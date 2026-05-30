<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 */
class Dashboard extends CI_Controller
{
    private $oauthDebugVersion = '2026-05-30-redirect-fallback-v4';

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        check_and_create_db_tables();
        
        // Cek login session untuk semua method kecuali instagram_callback
        $method = $this->router->fetch_method();
        if ($method !== 'instagram_callback') {
            $this->checkSession();
        }
    }

    private function checkSession()
    {
        if (empty($this->session->userdata['username'])) {
            redirect('login');
            exit;
        }
    }

    private function get_my_ig_ids()
    {
        $userData = useraAuthData();
        $user_email = $userData['email'] ?? '';
        $level = $userData['level'] ?? '';

        if ($user_email === 'blowebdev17@gmail.com' || $level === 'admin') {
            return null; // Admin sees all
        }

        $tokens = $this->db->select('ig_user_id')
                           ->where('user_email', $user_email)
                           ->get('access_tokens')
                           ->result_array();
        
        $ids = array_map(function($row) {
            return $row['ig_user_id'];
        }, $tokens);

        return !empty($ids) ? $ids : ['none_connected'];
    }

    public function index()
    {
        $userData = useraAuthData();
        $user_email = $userData['email'] ?? '';
        $level = $userData['level'] ?? '';

        // Ambil akun terhubung sesuai email login (admin melihat semua)
        $this->db->order_by('updated_at', 'DESC');
        if ($user_email !== 'blowebdev17@gmail.com' && $level !== 'admin') {
            $this->db->where('user_email', $user_email);
        }
        $data['accounts'] = $this->db->get('access_tokens')->result_array();
        
        $data['halaman'] = 'dashboard/index';
        
        // Pass empty variables for backward compatibility with modul.php structure
        $data['result'] = null;
        $data['start'] = 0;

        $this->load->view('modul', $data);
    }

    // =========================================================================
    // INSTAGRAM OAUTH METHODS
    // =========================================================================

    private function normalizeRedirectUri($uri)
    {
        return rtrim(trim($uri), '/');
    }

    private function encodeOAuthState($redirectUri)
    {
        $userData = useraAuthData();
        $payload = [
            'redirect_uri' => $redirectUri,
            'user_email'   => $userData['email'] ?? null,
            'nonce'        => bin2hex(random_bytes(12)),
            'created_at'   => time(),
        ];
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $sig = hash_hmac('sha256', $json, IG_APP_SECRET);

        return rtrim(strtr(base64_encode($json . '.' . $sig), '+/', '-_'), '=');
    }

    private function decodeOAuthState($state)
    {
        if (!$state) {
            return null;
        }

        $decoded = base64_decode(strtr($state, '-_', '+/'), true);
        $separatorPos = strrpos($decoded ?: '', '.');
        if (!$decoded || $separatorPos === false) {
            return null;
        }

        $json = substr($decoded, 0, $separatorPos);
        $sig = substr($decoded, $separatorPos + 1);
        $expectedSig = hash_hmac('sha256', $json, IG_APP_SECRET);
        if (!hash_equals($expectedSig, $sig)) {
            return null;
        }

        $payload = json_decode($json, true);
        if (!is_array($payload) || empty($payload['redirect_uri'])) {
            return null;
        }

        return $payload;
    }

    private function restoreSessionFromEmail($email)
    {
        if (!$email || !empty($this->session->userdata['username'])) {
            return;
        }

        $user = $this->db->get_where('users', ['email' => $email])->row_array();
        if (!$user) {
            writeLog('OAuth callback could not restore session: user not found', ['email' => $email]);
            return;
        }

        $this->session->set_userdata([
            'username' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'] ?? $user['nama'] ?? $user['email'],
                'nama' => $user['nama'] ?? $user['name'] ?? null,
                'given_name' => $user['given_name'] ?? null,
                'family_name' => $user['family_name'] ?? null,
                'picture' => $user['picture'] ?? null,
                'locale' => $user['locale'] ?? null,
                'level' => $user['level'] ?? 'user',
                'logged_in' => true,
            ]
        ]);

        writeLog('OAuth callback restored dashboard session from state', [
            'email' => $email,
            'user_id' => $user['id'],
        ]);
    }

    private function getApiErrorMessage($response, $fallback)
    {
        if (!is_array($response)) {
            return $fallback;
        }

        if (!empty($response['error_message'])) {
            return (string)$response['error_message'];
        }

        if (isset($response['error'])) {
            if (is_array($response['error'])) {
                return (string)($response['error']['message'] ?? json_encode($response['error']));
            }
            return (string)$response['error'];
        }

        return $fallback;
    }

    private function redirectAfterOAuth($username)
    {
        $target = base_url('dashboard?status=success&username=' . rawurlencode((string)$username));

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        if (!headers_sent()) {
            header('Location: ' . $target, true, 303);
            echo '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '"></head><body>Redirecting...</body></html>';
            exit;
        }

        echo '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '"></head><body><script>window.location.replace(' . json_encode($target) . ');</script><p>Login berhasil. <a href="' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '">Lanjut ke dashboard</a></p></body></html>';
        exit;
    }

    private function igGraphUrl($path)
    {
        return rtrim(IG_GRAPH_API_BASE, '/') . '/' . IG_GRAPH_API_VERSION . '/' . ltrim($path, '/');
    }

    private function fetchInstagramProfile($accessToken, $igUserId)
    {
        $fieldSets = [
            'id,username',
            'user_id,username,name,account_type,profile_picture_url,followers_count,media_count',
            'id,username,name,account_type,profile_picture_url,followers_count,media_count',
            'id,username,account_type,media_count',
        ];

        $endpoints = [
            $this->igGraphUrl('/me'),
            IG_GRAPH_API_BASE . '/me',
            $this->igGraphUrl('/' . rawurlencode((string)$igUserId)),
            IG_GRAPH_API_BASE . '/' . rawurlencode((string)$igUserId),
        ];

        $lastResponse = null;
        foreach ($endpoints as $endpoint) {
            foreach ($fieldSets as $fields) {
                $profile = callGraphAPI($endpoint, 'GET', [
                    'fields'       => $fields,
                    'access_token' => $accessToken,
                ]);

                writeLog('Profile Response Attempt', [
                    'endpoint' => $endpoint,
                    'fields' => $fields,
                    'response' => $profile,
                ]);

                $lastResponse = $profile;
                if (is_array($profile) && empty($profile['error']) && (!empty($profile['user_id']) || !empty($profile['id']))) {
                    $profile['user_id'] = $profile['user_id'] ?? $profile['id'];
                    return $profile;
                }
            }
        }

        return $lastResponse ?: ['error' => ['message' => 'Profil Instagram tidak mengembalikan response.']];
    }

    private function buildFallbackInstagramProfile($igUserId)
    {
        return [
            'user_id' => $igUserId,
            'username' => 'ig_' . $igUserId,
            'name' => null,
            'profile_picture_url' => null,
            'followers_count' => 0,
            'media_count' => 0,
        ];
    }

    private function getRequiredInstagramScopes()
    {
        return [
            'instagram_business_basic',
            'instagram_business_manage_messages',
            'instagram_business_manage_comments',
            'instagram_business_content_publish',
            'instagram_business_manage_insights',
        ];
    }

    private function getGrantedScopesFromTokenResponse($tokenResponse)
    {
        if (!is_array($tokenResponse)) {
            return [];
        }

        $scopeValue = $tokenResponse['permissions'] ?? $tokenResponse['scope'] ?? $tokenResponse['granted_scopes'] ?? null;
        if (is_string($scopeValue)) {
            return array_values(array_filter(array_map('trim', preg_split('/[,\s]+/', $scopeValue))));
        }

        if (is_array($scopeValue)) {
            return array_values(array_filter(array_map('strval', $scopeValue)));
        }

        return [];
    }

    private function logGrantedInstagramScopes($tokenResponse)
    {
        $grantedScopes = $this->getGrantedScopesFromTokenResponse($tokenResponse);
        $missingScopes = $grantedScopes ? array_values(array_diff($this->getRequiredInstagramScopes(), $grantedScopes)) : [];

        writeLog('Instagram OAuth granted permissions', [
            'required_scopes' => $this->getRequiredInstagramScopes(),
            'granted_scopes' => $grantedScopes,
            'missing_scopes' => $missingScopes,
        ]);

        return [
            'granted' => $grantedScopes,
            'missing' => $missingScopes,
        ];
    }

    private function subscribeInstagramWebhooks($accessToken, $igUserId)
    {
        $response = callGraphAPI($this->igGraphUrl('/' . rawurlencode((string)$igUserId) . '/subscribed_apps'), 'POST', [
            'subscribed_fields' => 'comments,messages,live_comments',
            'access_token' => $accessToken,
        ]);

        writeLog('Instagram webhook subscription response', [
            'ig_user_id' => $igUserId,
            'response' => $response,
        ]);

        return $response;
    }

    private function fetchInstagramMediaList($accessToken)
    {
        $fieldSets = [
            'id,caption,media_type,media_url,permalink,timestamp,like_count,comments_count',
            'id,caption,media_type,media_url,permalink,timestamp',
            'id,media_type,media_url,permalink,timestamp',
            'id,media_type,timestamp',
        ];

        $lastResponse = null;
        foreach ($fieldSets as $fields) {
            $response = callGraphAPI($this->igGraphUrl('/me/media'), 'GET', [
                'access_token' => $accessToken,
                'fields' => $fields,
                'limit' => 20,
            ]);

            writeLog('Media Sync Attempt', [
                'fields' => $fields,
                'response' => $response,
            ]);

            $lastResponse = $response;
            if (is_array($response) && !isset($response['error'])) {
                return $response;
            }
        }

        return $lastResponse ?: ['error' => ['message' => 'Media Instagram tidak mengembalikan response.']];
    }

    private function fetchInstagramComments($accessToken, $mediaId, $includeReplies = false)
    {
        $fieldSets = $includeReplies
            ? [
                'id,text,timestamp,like_count,replies{id,text,timestamp}',
                'id,text,timestamp,replies{id,text,timestamp}',
                'id,text,timestamp',
                'id,text',
            ]
            : [
                'id,text,timestamp,like_count',
                'id,text,timestamp',
                'id,text',
            ];

        $lastResponse = null;
        foreach ($fieldSets as $fields) {
            $response = callGraphAPI($this->igGraphUrl('/' . rawurlencode((string)$mediaId) . '/comments'), 'GET', [
                'access_token' => $accessToken,
                'fields' => $fields,
                'limit' => 50,
            ]);

            writeLog('Comments Sync Attempt', [
                'media_id' => $mediaId,
                'fields' => $fields,
                'response' => $response,
            ]);

            $lastResponse = $response;
            if (is_array($response) && !isset($response['error'])) {
                return $response;
            }
        }

        return $lastResponse ?: ['error' => ['message' => 'Komentar Instagram tidak mengembalikan response.']];
    }

    public function instagram_login()
    {
        $configuredRedirectUri = $this->normalizeRedirectUri(IG_REDIRECT_URI);
        $state = $this->encodeOAuthState($configuredRedirectUri);
        
        $scopes = implode(',', $this->getRequiredInstagramScopes());

        $authUrl = IG_AUTH_URL . '?' . http_build_query([
            'force_reauth'  => 'true',
            'client_id'     => IG_APP_ID,
            'redirect_uri'  => $configuredRedirectUri,
            'response_type' => 'code',
            'scope'         => $scopes,
            'state'         => $state,
        ]);

        writeLog('Redirecting to Instagram Login via Controller', [
            'url'          => $authUrl,
            'redirect_uri' => $configuredRedirectUri,
            'state'        => $state,
            'debug_version'=> $this->oauthDebugVersion,
        ]);

        header('Location: ' . $authUrl);
        exit;
    }

    public function instagram_callback()
    {
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                writeLog('Instagram Callback Fatal Shutdown', $error);
                if (!headers_sent()) {
                    header('Content-Type: text/html; charset=UTF-8');
                }
                echo '<!doctype html><html><head><meta charset="utf-8"><title>Instagram OAuth Error</title></head><body style="font-family:Arial,sans-serif;background:#f4f7f6;padding:40px;"><div style="max-width:720px;margin:auto;background:white;border:1px solid #ddd;border-radius:12px;padding:24px;"><h1 style="color:#b91c1c;">Instagram OAuth Fatal Error</h1><p>Terjadi error fatal saat callback Instagram. Detail sudah dicatat ke log aplikasi.</p><pre style="white-space:pre-wrap;background:#111827;color:#e5e7eb;padding:14px;border-radius:8px;">' . htmlspecialchars(json_encode($error, JSON_PRETTY_PRINT)) . '</pre><p><a href="' . base_url('dashboard') . '">Kembali ke Dashboard</a></p></div></body></html>';
            }
        });

        try {
        $configuredRedirectUri = $this->normalizeRedirectUri(IG_REDIRECT_URI);
        writeLog('Instagram Callback Started', [
            'query_keys' => array_keys($_GET),
            'has_code' => $this->input->get('code') ? true : false,
            'has_state' => $this->input->get('state') ? true : false,
            'redirect_uri' => $configuredRedirectUri,
            'session_has_username' => !empty($this->session->userdata['username']),
        ]);
        
        // 1. Cek error dari Instagram
        $error = $this->input->get('error');
        if ($error) {
            writeLog('Instagram Callback Error: User denied access', $_GET);
            $this->show_oauth_error('Akses Ditolak', 'Kamu menolak izin akses Instagram.', $error);
            return;
        }

        $code = $this->input->get('code');
        if (!$code) {
            writeLog('Instagram Callback Error: Code is missing');
            $this->show_oauth_error('Code Missing', 'Instagram tidak mengembalikan kode otorisasi.', 'No code parameter found.');
            return;
        }

        // Hapus #_ atau spasi
        $code = str_replace('#_', '', $code);
        $code = trim($code);
        $state = $this->input->get('state');

        $tokenRedirectUri = $configuredRedirectUri;
        $statePayload = $this->decodeOAuthState($state);

        if ($statePayload) {
            $tokenRedirectUri = $this->normalizeRedirectUri($statePayload['redirect_uri']);
            $this->restoreSessionFromEmail($statePayload['user_email'] ?? null);
        } elseif (!$state) {
            writeLog('Instagram Callback Warning: Missing state, continuing with active session fallback', [
                'redirect_uri' => $tokenRedirectUri,
                'session_has_username' => !empty($this->session->userdata['username']),
            ]);
        } else {
            writeLog('OAuth state invalid or mismatch', [
                'state' => $state,
                'redirect_uri' => $tokenRedirectUri
            ]);
        }

        writeLog('Received Instagram OAuth code in Controller', [
            'code'         => substr($code, 0, 20) . '...',
            'state'        => $state,
            'redirect_uri' => $tokenRedirectUri,
        ]);

        // 2. Tukar Code -> Short-Lived Access Token
        $postData = [
            'client_id'     => IG_APP_ID,
            'client_secret' => IG_APP_SECRET,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $tokenRedirectUri,
            'code'          => $code,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, IG_TOKEN_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        $tokenResponse = $response ? json_decode($response, true) : ['error' => 'cURL fail: ' . $curlErr];
        writeLog('Short-lived Token Response', [
            'http_code' => $httpCode,
            'curl_error' => $curlErr,
            'response' => $tokenResponse,
        ]);

        if (isset($tokenResponse['error_type']) || isset($tokenResponse['error'])) {
            $errMsg = $this->getApiErrorMessage($tokenResponse, 'Gagal menukarkan kode.');
            $this->show_oauth_error('Gagal Mendapatkan Token', $errMsg, json_encode($tokenResponse));
            return;
        }

        if (empty($tokenResponse['access_token']) || empty($tokenResponse['user_id'])) {
            $this->show_oauth_error('Token Tidak Lengkap', 'Instagram tidak mengembalikan access_token atau user_id.', json_encode($tokenResponse));
            return;
        }

        // User sudah klik Allow; token ini adalah hasil izin OAuth yang diberikan.
        $permissionInfo = $this->logGrantedInstagramScopes($tokenResponse);

        $shortLivedToken = $tokenResponse['access_token'];
        $igUserId = $tokenResponse['user_id'];

        // 3. Tukar Short-Lived -> Long-Lived Token
        $longLivedResponse = callGraphAPI(IG_GRAPH_API_BASE . '/access_token', 'GET', [
            'grant_type'    => 'ig_exchange_token',
            'client_secret' => IG_APP_SECRET,
            'access_token'  => $shortLivedToken,
        ]);
        writeLog('Long-lived Token Response', $longLivedResponse);

        $accessToken = $longLivedResponse['access_token'] ?? $shortLivedToken;
        $expiresIn = $longLivedResponse['expires_in'] ?? 3600;
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
        $tokenType = $longLivedResponse['token_type'] ?? 'bearer';

        // 4. Ambil Profil User
        $profile = $this->fetchInstagramProfile($accessToken, $igUserId);
        writeLog('Profile Response', $profile);

        if (isset($profile['error']) || !isset($profile['user_id'])) {
            writeLog('Profile fetch failed after token success; continuing with fallback profile', [
                'ig_user_id' => $igUserId,
                'profile_error' => $profile,
            ]);
            $profile = $this->buildFallbackInstagramProfile($igUserId);
        }

        // 5. Simpan ke database
        $ig_user_id = $profile['user_id'];
        $username = $profile['username'] ?? null;
        $name = $profile['name'] ?? null;

        $userData = useraAuthData();
        $user_email = $userData['email'] ?? ($statePayload['user_email'] ?? null);
        if (!$user_email) {
            writeLog('Instagram Callback Warning: owner user_email is empty', [
                'session_has_username' => !empty($this->session->userdata['username']),
                'state_payload' => $statePayload,
            ]);
        }

        $tokenData = [
            'user_email' => $user_email,
            'ig_user_id' => $ig_user_id,
            'username' => $username,
            'name' => $name,
            'profile_picture_url' => $profile['profile_picture_url'] ?? null,
            'followers_count' => $profile['followers_count'] ?? 0,
            'media_count' => $profile['media_count'] ?? 0,
            'access_token' => $accessToken,
            'token_type' => $tokenType,
            'expires_at' => $expiresAt,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $check = $this->db->get_where('access_tokens', ['ig_user_id' => $ig_user_id])->row_array();
        if ($check) {
            $this->db->where('ig_user_id', $ig_user_id)->update('access_tokens', $tokenData);
        } else {
            $tokenData['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('access_tokens', $tokenData);
        }

        // Token permission sudah disimpan; setelah itu baru pasang subscription webhook.
        $subscriptionResponse = $this->subscribeInstagramWebhooks($accessToken, $ig_user_id);

        writeLog('Token saved to database in Controller', [
            'ig_user_id' => $ig_user_id,
            'user_email' => $user_email,
            'permissions' => $permissionInfo,
            'webhook_subscription' => $subscriptionResponse,
        ]);

        $this->redirectAfterOAuth($username);
        } catch (Exception $e) {
            writeLog('Instagram Callback Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->show_oauth_error('Callback Error', $e->getMessage(), $e->getTraceAsString());
        } catch (Throwable $e) {
            writeLog('Instagram Callback Throwable', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->show_oauth_error('Callback Error', $e->getMessage(), $e->getTraceAsString());
        }
    }

    private function show_oauth_error($title, $msg, $debug)
    {
        $data['title'] = $title;
        $data['msg'] = $msg;
        $data['debug'] = $debug;
        $data['debug_version'] = $this->oauthDebugVersion;
        $data['redirect_uri'] = IG_REDIRECT_URI;
        
        $this->load->view('oauth_error', $data);
    }

    // =========================================================================
    // AJAX ENDPOINTS
    // =========================================================================

    private function json_res($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function assertAccountAccess($igUserId)
    {
        $my_ids = $this->get_my_ig_ids();
        if (!$igUserId) {
            $this->json_res(['success' => false, 'error' => 'ig_user_id required']);
        }
        if ($my_ids !== null && !in_array($igUserId, $my_ids)) {
            $this->json_res(['success' => false, 'error' => 'Unauthorized']);
        }
    }

    private function getCurrentUserEmail()
    {
        $userData = useraAuthData();
        return $userData['email'] ?? null;
    }

    public function get_stats()
    {
        try {
            $my_ids = $this->get_my_ig_ids();
            $igUserId = $this->input->get('ig_user_id');
            $stats = [];

            if ($igUserId) {
                if ($my_ids !== null && !in_array($igUserId, $my_ids)) {
                    $this->json_res(['success' => false, 'error' => 'Unauthorized']);
                }
                $filter_ids = [$igUserId];
            } else {
                $filter_ids = $my_ids;
            }

            // Total accounts
            $this->db->from('access_tokens');
            if ($filter_ids !== null) {
                $this->db->where_in('ig_user_id', $filter_ids);
            }
            $stats['total_accounts'] = $this->db->count_all_results();

            // Total webhook logs
            $this->db->from('webhook_logs');
            if ($filter_ids !== null) {
                $this->db->where_in('entry_id', $filter_ids);
            }
            $stats['total_webhook_events'] = $this->db->count_all_results();

            // Total comments
            $this->db->from('comments');
            if ($filter_ids !== null) {
                $this->db->join('media', 'media.media_id = comments.media_id', 'left');
                $this->db->group_start();
                $this->db->where_in('comments.ig_user_id', $filter_ids);
                $this->db->or_where_in('media.ig_user_id', $filter_ids);
                $this->db->group_end();
            }
            $stats['total_comments'] = $this->db->count_all_results();

            // Total messages
            $this->db->from('messages');
            if ($filter_ids !== null) {
                $this->db->group_start();
                $this->db->where_in('ig_user_id', $filter_ids);
                $this->db->or_where_in('sender_id', $filter_ids);
                $this->db->or_where_in('recipient_id', $filter_ids);
                $this->db->group_end();
            }
            $stats['total_messages'] = $this->db->count_all_results();

            // Total media
            $this->db->from('media');
            if ($filter_ids !== null) {
                $this->db->where_in('ig_user_id', $filter_ids);
            }
            $stats['total_media'] = $this->db->count_all_results();

            // Latest event
            $this->db->select('created_at')->from('webhook_logs');
            if ($filter_ids !== null) {
                $this->db->where_in('entry_id', $filter_ids);
            }
            $latest = $this->db->order_by('created_at', 'DESC')->limit(1)->get()->row_array();
            $stats['latest_event'] = $latest ? $latest['created_at'] : '-';

            $this->json_res(['success' => true, 'data' => $stats]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_accounts()
    {
        try {
            $userData = useraAuthData();
            $user_email = $userData['email'] ?? '';
            $level = $userData['level'] ?? '';

            $this->db->order_by('updated_at', 'DESC');
            if ($user_email !== 'blowebdev17@gmail.com' && $level !== 'admin') {
                $this->db->where('user_email', $user_email);
            }
            $accounts = $this->db->get('access_tokens')->result_array();
            $this->json_res(['success' => true, 'data' => $accounts]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_webhook_logs()
    {
        try {
            $limit = (int)($this->input->get('limit') ?? 50);
            $my_ids = $this->get_my_ig_ids();
            $igUserId = $this->input->get('ig_user_id');
            
            $this->db->order_by('created_at', 'DESC')->limit($limit);
            if ($igUserId) {
                if ($my_ids !== null && !in_array($igUserId, $my_ids)) {
                    $this->json_res(['success' => false, 'error' => 'Unauthorized']);
                }
                $this->db->where('entry_id', $igUserId);
            } elseif ($my_ids !== null) {
                $this->db->where_in('entry_id', $my_ids);
            }
            $logs = $this->db->get('webhook_logs')->result_array();
            $this->json_res(['success' => true, 'data' => $logs]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_comments()
    {
        try {
            $limit = (int)($this->input->get('limit') ?? 50);
            $my_ids = $this->get_my_ig_ids();
            $igUserId = $this->input->get('ig_user_id');

            $this->db->select('comments.*, COALESCE(comments.ig_user_id, media.ig_user_id) as target_ig_user_id, access_tokens.username as target_ig_username');
            $this->db->from('comments');
            $this->db->join('media', 'media.media_id = comments.media_id', 'left');
            $this->db->join('access_tokens', 'access_tokens.ig_user_id = COALESCE(comments.ig_user_id, media.ig_user_id)', 'left');

            if ($igUserId) {
                if ($my_ids !== null && !in_array($igUserId, $my_ids)) {
                    $this->json_res(['success' => false, 'error' => 'Unauthorized']);
                }
                $this->db->group_start();
                $this->db->where('comments.ig_user_id', $igUserId);
                $this->db->or_where('media.ig_user_id', $igUserId);
                $this->db->group_end();
            } elseif ($my_ids !== null) {
                $this->db->group_start();
                $this->db->where_in('comments.ig_user_id', $my_ids);
                $this->db->or_where_in('media.ig_user_id', $my_ids);
                $this->db->group_end();
            }

            $this->db->order_by('comments.created_at', 'DESC')->limit($limit);
            $comments = $this->db->get()->result_array();
            $this->json_res(['success' => true, 'data' => $comments]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function analyzeSentimentText($text)
    {
        $text = strtolower((string)$text);
        $positiveWords = [
            'bagus', 'baik', 'mantap', 'keren', 'suka', 'senang', 'puas', 'top',
            'terbaik', 'recommended', 'rekomendasi', 'cepat', 'ramah', 'mudah',
            'hebat', 'wow', 'love', 'thanks', 'terima kasih', 'makasih', 'membantu',
            'berhasil', 'aman', 'nyaman', 'worth', 'perfect', 'good', 'great'
        ];
        $negativeWords = [
            'buruk', 'jelek', 'kecewa', 'komplain', 'lambat', 'lama', 'mahal',
            'susah', 'sulit', 'error', 'gagal', 'rusak', 'parah', 'benci',
            'tidak puas', 'ga puas', 'gak puas', 'nggak puas', 'kurang', 'masalah',
            'bohong', 'tipu', 'spam', 'cancel', 'bad', 'worst'
        ];

        $positiveScore = 0;
        $negativeScore = 0;

        foreach ($positiveWords as $word) {
            if (strpos($text, $word) !== false) {
                $positiveScore++;
            }
        }
        foreach ($negativeWords as $word) {
            if (strpos($text, $word) !== false) {
                $negativeScore++;
            }
        }

        if ($positiveScore > $negativeScore) {
            return 'positive';
        }
        if ($negativeScore > $positiveScore) {
            return 'negative';
        }
        return 'neutral';
    }

    public function get_sentiment_analysis()
    {
        try {
            $igUserId = $this->input->get('ig_user_id');
            $sourceFilter = $this->input->get('source') ?: 'all';
            $dateFrom = $this->input->get('date_from');
            $dateTo = $this->input->get('date_to');
            $mediaId = $this->input->get('media_id');
            $analyzer = $this->input->get('analyzer') ?: 'local';

            $this->assertAccountAccess($igUserId);

            $comments = [];
            if ($sourceFilter === 'all' || $sourceFilter === 'comment') {
                $this->db->select("'comment' as source, comments.text as text, comments.created_at", false);
                $this->db->from('comments');
                $this->db->join('media', 'media.media_id = comments.media_id', 'left');
                $this->db->group_start();
                $this->db->where('comments.ig_user_id', $igUserId);
                $this->db->or_where('media.ig_user_id', $igUserId);
                $this->db->group_end();
                if ($mediaId) {
                    $this->db->where('comments.media_id', $mediaId);
                }
                if ($dateFrom) {
                    $this->db->where('DATE(comments.created_at) >= ' . $this->db->escape($dateFrom), null, false);
                }
                if ($dateTo) {
                    $this->db->where('DATE(comments.created_at) <= ' . $this->db->escape($dateTo), null, false);
                }
                $this->db->where('comments.text IS NOT NULL', null, false);
                $comments = $this->db->get()->result_array();
            }

            $messages = [];
            if (!$mediaId && ($sourceFilter === 'all' || $sourceFilter === 'message')) {
                $this->db->select("'message' as source, messages.message_text as text, messages.created_at", false);
                $this->db->from('messages');
                $this->db->group_start();
                $this->db->where('messages.ig_user_id', $igUserId);
                $this->db->or_where('messages.sender_id', $igUserId);
                $this->db->or_where('messages.recipient_id', $igUserId);
                $this->db->group_end();
                if ($dateFrom) {
                    $this->db->where('DATE(messages.created_at) >= ' . $this->db->escape($dateFrom), null, false);
                }
                if ($dateTo) {
                    $this->db->where('DATE(messages.created_at) <= ' . $this->db->escape($dateTo), null, false);
                }
                $this->db->where('messages.message_text IS NOT NULL', null, false);
                $messages = $this->db->get()->result_array();
            }

            $items = array_merge($comments, $messages);
            $summary = [
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
                'total' => 0,
            ];
            $sourceSummary = [
                'comment' => ['positive' => 0, 'neutral' => 0, 'negative' => 0, 'total' => 0],
                'message' => ['positive' => 0, 'neutral' => 0, 'negative' => 0, 'total' => 0],
            ];
            $trend = [];
            $recent = [];

            foreach ($items as $item) {
                $text = trim((string)($item['text'] ?? ''));
                if ($text === '') {
                    continue;
                }

                $sentiment = $this->analyzeSentimentText($text);
                $source = $item['source'] === 'message' ? 'message' : 'comment';
                $dateKey = date('Y-m-d', strtotime($item['created_at'] ?? date('Y-m-d H:i:s')));

                $summary[$sentiment]++;
                $summary['total']++;
                $sourceSummary[$source][$sentiment]++;
                $sourceSummary[$source]['total']++;

                if (!isset($trend[$dateKey])) {
                    $trend[$dateKey] = ['date' => $dateKey, 'positive' => 0, 'neutral' => 0, 'negative' => 0];
                }
                $trend[$dateKey][$sentiment]++;

                $recent[] = [
                    'source' => $source,
                    'sentiment' => $sentiment,
                    'text' => substr($text, 0, 160),
                    'created_at' => $item['created_at'],
                ];
            }

            ksort($trend);
            usort($recent, function ($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            $this->json_res([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'source_summary' => $sourceSummary,
                    'trend' => array_values($trend),
                    'recent' => array_slice($recent, 0, 8),
                    'analyzer' => $analyzer === 'ai' ? 'ai-ready-fallback-local' : 'local-keyword',
                ]
            ]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_webhook_health()
    {
        try {
            $igUserId = $this->input->get('ig_user_id');
            $this->assertAccountAccess($igUserId);

            $account = $this->db->get_where('access_tokens', ['ig_user_id' => $igUserId])->row_array();
            $latestWebhook = $this->db->where('entry_id', $igUserId)->order_by('created_at', 'DESC')->limit(1)->get('webhook_logs')->row_array();
            $eventCounts = $this->db->select('event_type, COUNT(*) as total')
                ->where('entry_id', $igUserId)
                ->group_by('event_type')
                ->order_by('total', 'DESC')
                ->get('webhook_logs')
                ->result_array();

            $expiresAt = $account['expires_at'] ?? null;
            $daysLeft = null;
            $tokenStatus = 'unknown';
            if ($expiresAt) {
                $daysLeft = floor((strtotime($expiresAt) - time()) / 86400);
                if ($daysLeft < 0) {
                    $tokenStatus = 'expired';
                } elseif ($daysLeft <= 7) {
                    $tokenStatus = 'warning';
                } else {
                    $tokenStatus = 'ok';
                }
            }

            $lastEventAt = $latestWebhook['created_at'] ?? null;
            $webhookStatus = $lastEventAt ? 'receiving' : 'no_events';
            $minutesSinceLastEvent = $lastEventAt ? floor((time() - strtotime($lastEventAt)) / 60) : null;

            $this->json_res([
                'success' => true,
                'data' => [
                    'webhook_status' => $webhookStatus,
                    'last_event_at' => $lastEventAt ?: '-',
                    'minutes_since_last_event' => $minutesSinceLastEvent,
                    'event_counts' => $eventCounts,
                    'token_status' => $tokenStatus,
                    'expires_at' => $expiresAt,
                    'days_left' => $daysLeft,
                ]
            ]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function refresh_access_token()
    {
        try {
            $igUserId = $this->input->get('ig_user_id');
            $this->assertAccountAccess($igUserId);

            $account = $this->db->select('access_token')->get_where('access_tokens', ['ig_user_id' => $igUserId])->row_array();
            if (!$account) {
                $this->json_res(['success' => false, 'error' => 'Token not found']);
            }

            $response = callGraphAPI(IG_GRAPH_API_BASE . '/refresh_access_token', 'GET', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $account['access_token'],
            ]);

            if (isset($response['error'])) {
                $this->json_res(['success' => false, 'error' => $response['error']['message'] ?? 'Gagal refresh token']);
            }

            $accessToken = $response['access_token'] ?? $account['access_token'];
            $expiresIn = $response['expires_in'] ?? (60 * 24 * 60 * 60);
            $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
            $subscriptionResponse = $this->subscribeInstagramWebhooks($accessToken, $igUserId);

            $this->db->where('ig_user_id', $igUserId)->update('access_tokens', [
                'access_token' => $accessToken,
                'expires_at' => $expiresAt,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->json_res(['success' => true, 'expires_at' => $expiresAt, 'webhook_subscription' => $subscriptionResponse]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_reply_templates()
    {
        try {
            $igUserId = $this->input->get('ig_user_id');
            $this->assertAccountAccess($igUserId);

            $templates = $this->db->where('ig_user_id', $igUserId)
                ->order_by('updated_at', 'DESC')
                ->get('reply_templates')
                ->result_array();

            $this->json_res(['success' => true, 'data' => $templates]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function save_reply_template()
    {
        try {
            $igUserId = $this->input->post('ig_user_id');
            $this->assertAccountAccess($igUserId);

            $id = (int)($this->input->post('id') ?? 0);
            $data = [
                'user_email' => $this->getCurrentUserEmail(),
                'ig_user_id' => $igUserId,
                'name' => trim((string)$this->input->post('name')),
                'channel' => $this->input->post('channel') ?: 'all',
                'keyword' => trim((string)$this->input->post('keyword')),
                'response_text' => trim((string)$this->input->post('response_text')),
                'is_active' => (int)($this->input->post('is_active') ?? 1),
                'auto_reply' => (int)($this->input->post('auto_reply') ?? 0),
            ];

            if ($data['name'] === '' || $data['response_text'] === '') {
                $this->json_res(['success' => false, 'error' => 'Nama template dan isi balasan wajib diisi.']);
            }

            if ($id > 0) {
                $this->db->where('id', $id)->where('ig_user_id', $igUserId)->update('reply_templates', $data);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('reply_templates', $data);
                $id = $this->db->insert_id();
            }

            $this->json_res(['success' => true, 'id' => $id]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function delete_reply_template()
    {
        try {
            $igUserId = $this->input->get('ig_user_id');
            $id = (int)$this->input->get('id');
            $this->assertAccountAccess($igUserId);
            $this->db->where('id', $id)->where('ig_user_id', $igUserId)->delete('reply_templates');
            $this->json_res(['success' => true]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function export_data()
    {
        $igUserId = $this->input->get('ig_user_id');
        $type = $this->input->get('type') ?: 'comments';
        $this->assertAccountAccess($igUserId);

        $filename = $type . '_' . $igUserId . '_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');

        if ($type === 'messages') {
            fputcsv($out, ['id', 'message_id', 'ig_user_id', 'sender_id', 'recipient_id', 'message_text', 'created_at']);
            $rows = $this->db->group_start()->where('ig_user_id', $igUserId)->or_where('sender_id', $igUserId)->or_where('recipient_id', $igUserId)->group_end()
                ->order_by('created_at', 'DESC')->get('messages')->result_array();
            foreach ($rows as $row) {
                fputcsv($out, [$row['id'], $row['message_id'], $row['ig_user_id'], $row['sender_id'], $row['recipient_id'], $row['message_text'], $row['created_at']]);
            }
        } elseif ($type === 'webhooks') {
            fputcsv($out, ['id', 'object', 'event_type', 'field', 'value', 'created_at']);
            $rows = $this->db->where('entry_id', $igUserId)->order_by('created_at', 'DESC')->get('webhook_logs')->result_array();
            foreach ($rows as $row) {
                fputcsv($out, [$row['id'], $row['object'], $row['event_type'], $row['field'], $row['value'], $row['created_at']]);
            }
        } else {
            fputcsv($out, ['id', 'comment_id', 'media_id', 'from_username', 'text', 'sentiment', 'created_at']);
            $this->db->select('comments.*')->from('comments')->join('media', 'media.media_id = comments.media_id', 'left');
            $this->db->group_start()->where('comments.ig_user_id', $igUserId)->or_where('media.ig_user_id', $igUserId)->group_end();
            $rows = $this->db->order_by('comments.created_at', 'DESC')->get()->result_array();
            foreach ($rows as $row) {
                fputcsv($out, [$row['id'], $row['comment_id'], $row['media_id'], $row['from_username'], $row['text'], $this->analyzeSentimentText($row['text']), $row['created_at']]);
            }
        }

        fclose($out);
        exit;
    }

    public function get_messages()
    {
        try {
            $limit = (int)($this->input->get('limit') ?? 50);
            $my_ids = $this->get_my_ig_ids();
            $igUserId = $this->input->get('ig_user_id');

            $this->db->select('messages.*, 
                sender_acc.username as sender_username, 
                recipient_acc.username as recipient_username');
            $this->db->from('messages');
            $this->db->join('access_tokens as sender_acc', 'sender_acc.ig_user_id = messages.sender_id', 'left');
            $this->db->join('access_tokens as recipient_acc', 'recipient_acc.ig_user_id = messages.recipient_id', 'left');

            if ($igUserId) {
                if ($my_ids !== null && !in_array($igUserId, $my_ids)) {
                    $this->json_res(['success' => false, 'error' => 'Unauthorized']);
                }
                $this->db->group_start();
                $this->db->where('messages.ig_user_id', $igUserId);
                $this->db->or_where('messages.sender_id', $igUserId);
                $this->db->or_where('messages.recipient_id', $igUserId);
                $this->db->group_end();
            } elseif ($my_ids !== null) {
                $this->db->group_start();
                $this->db->where_in('messages.ig_user_id', $my_ids);
                $this->db->or_where_in('messages.sender_id', $my_ids);
                $this->db->or_where_in('messages.recipient_id', $my_ids);
                $this->db->group_end();
            }

            $this->db->order_by('messages.created_at', 'DESC')->limit($limit);
            $messages = $this->db->get()->result_array();
            $this->json_res(['success' => true, 'data' => $messages]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_media()
    {
        try {
            $limit = (int)($this->input->get('limit') ?? 20);
            $my_ids = $this->get_my_ig_ids();
            $igUserId = $this->input->get('ig_user_id');

            $this->db->order_by('timestamp', 'DESC')->limit($limit);
            if ($igUserId) {
                if ($my_ids !== null && !in_array($igUserId, $my_ids)) {
                    $this->json_res(['success' => false, 'error' => 'Unauthorized']);
                }
                $this->db->where('ig_user_id', $igUserId);
            } elseif ($my_ids !== null) {
                $this->db->where_in('ig_user_id', $my_ids);
            }
            $media = $this->db->get('media')->result_array();
            $this->json_res(['success' => true, 'data' => $media]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function fetch_media()
    {
        $igUserId = $this->input->get('ig_user_id');
        if (!$igUserId) {
            $this->json_res(['success' => false, 'error' => 'ig_user_id required']);
        }

        try {
            // Ambil token dari DB
            $tokenRow = $this->db->select('access_token')->get_where('access_tokens', ['ig_user_id' => $igUserId])->row_array();
            if (!$tokenRow) {
                $this->json_res(['success' => false, 'error' => 'Token not found']);
            }
            $token = $tokenRow['access_token'];
            $subscriptionResponse = $this->subscribeInstagramWebhooks($token, $igUserId);

            // Sinkronisasi profil terupdate
            $profile = callGraphAPI($this->igGraphUrl('/me'), 'GET', [
                'fields'       => 'id,username',
                'access_token' => $token,
            ]);
            if ($profile && !isset($profile['error'])) {
                $this->db->where('ig_user_id', $igUserId)->update('access_tokens', [
                    'username' => $profile['username'] ?? ('ig_' . $igUserId),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Panggil API
            $response = $this->fetchInstagramMediaList($token);

            if (isset($response['error'])) {
                $this->json_res(['success' => false, 'error' => $response['error']['message']]);
            }

            // Simpan/update media
            $mediaList = $response['data'] ?? [];
            $syncedComments = 0;
            foreach ($mediaList as $media) {
                $media_id = $media['id'];
                $exist = $this->db->get_where('media', ['media_id' => $media_id])->row_array();
                
                $mediaData = [
                    'media_id' => $media_id,
                    'ig_user_id' => $igUserId,
                    'media_type' => $media['media_type'] ?? null,
                    'media_url' => $media['media_url'] ?? null,
                    'permalink' => $media['permalink'] ?? null,
                    'caption' => $media['caption'] ?? null,
                    'timestamp' => isset($media['timestamp']) ? date('Y-m-d H:i:s', strtotime($media['timestamp'])) : null,
                    'like_count' => $media['like_count'] ?? 0,
                    'comments_count' => $media['comments_count'] ?? 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($exist) {
                    $this->db->where('media_id', $media_id)->update('media', $mediaData);
                } else {
                    $mediaData['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('media', $mediaData);
                }

                $commentsResponse = $this->fetchInstagramComments($token, $media_id);

                if (!isset($commentsResponse['error'])) {
                    foreach (($commentsResponse['data'] ?? []) as $comment) {
                        $comment_id = $comment['id'] ?? null;
                        if (!$comment_id) {
                            continue;
                        }

                        $commentData = [
                            'comment_id' => $comment_id,
                            'ig_user_id' => $igUserId,
                    'media_id' => $media_id,
                    'from_username' => $comment['username'] ?? null,
                    'text' => $comment['text'] ?? '',
                    'like_count' => $comment['like_count'] ?? 0,
                    'timestamp' => isset($comment['timestamp']) ? date('Y-m-d H:i:s', strtotime($comment['timestamp'])) : null,
                    'is_from_webhook' => 0
                ];

                        $commentExist = $this->db->get_where('comments', ['comment_id' => $comment_id])->row_array();
                        if ($commentExist) {
                            $this->db->where('comment_id', $comment_id)->update('comments', $commentData);
                        } else {
                            $commentData['created_at'] = date('Y-m-d H:i:s');
                            $this->db->insert('comments', $commentData);
                        }
                        $syncedComments++;
                    }
                }
            }

            $this->json_res(['success' => true, 'data' => $mediaList, 'count' => count($mediaList), 'comments_count' => $syncedComments, 'webhook_subscription' => $subscriptionResponse]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function fetch_comments()
    {
        $mediaId = $this->input->get('media_id');
        $igUserId = $this->input->get('ig_user_id');
        if (!$mediaId || !$igUserId) {
            $this->json_res(['success' => false, 'error' => 'media_id and ig_user_id required']);
        }

        try {
            $tokenRow = $this->db->select('access_token')->get_where('access_tokens', ['ig_user_id' => $igUserId])->row_array();
            if (!$tokenRow) {
                $this->json_res(['success' => false, 'error' => 'Token not found']);
            }
            $token = $tokenRow['access_token'];

            $response = $this->fetchInstagramComments($token, $mediaId, true);

            if (isset($response['error'])) {
                $this->json_res(['success' => false, 'error' => $response['error']['message']]);
            }

            $commentsList = $response['data'] ?? [];
            foreach ($commentsList as $comment) {
                $comment_id = $comment['id'];
                $exist = $this->db->get_where('comments', ['comment_id' => $comment_id])->row_array();
                
                $commentData = [
                    'comment_id' => $comment_id,
                    'ig_user_id' => $igUserId,
                    'media_id' => $mediaId,
                    'from_username' => $comment['username'] ?? null,
                    'text' => $comment['text'] ?? '',
                    'like_count' => $comment['like_count'] ?? 0,
                    'timestamp' => isset($comment['timestamp']) ? date('Y-m-d H:i:s', strtotime($comment['timestamp'])) : null,
                    'is_from_webhook' => 0
                ];

                if ($exist) {
                    $this->db->where('comment_id', $comment_id)->update('comments', $commentData);
                } else {
                    $commentData['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('comments', $commentData);
                }

                foreach (($comment['replies']['data'] ?? []) as $reply) {
                    $replyId = $reply['id'] ?? null;
                    if (!$replyId) {
                        continue;
                    }

                    $replyData = [
                        'comment_id' => $replyId,
                        'ig_user_id' => $igUserId,
                        'media_id' => $mediaId,
                        'parent_id' => $comment_id,
                        'from_username' => $reply['username'] ?? null,
                        'text' => $reply['text'] ?? '',
                        'like_count' => $reply['like_count'] ?? 0,
                        'timestamp' => isset($reply['timestamp']) ? date('Y-m-d H:i:s', strtotime($reply['timestamp'])) : null,
                        'is_from_webhook' => 0
                    ];

                    $replyExist = $this->db->get_where('comments', ['comment_id' => $replyId])->row_array();
                    if ($replyExist) {
                        $this->db->where('comment_id', $replyId)->update('comments', $replyData);
                    } else {
                        $replyData['created_at'] = date('Y-m-d H:i:s');
                        $this->db->insert('comments', $replyData);
                    }
                }
            }

            $this->json_res(['success' => true, 'data' => $commentsList, 'count' => count($commentsList)]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_media_comments()
    {
        try {
            $igUserId = $this->input->get('ig_user_id');
            $mediaId = $this->input->get('media_id');
            $this->assertAccountAccess($igUserId);

            if (!$mediaId) {
                $this->json_res(['success' => false, 'error' => 'media_id required']);
            }

            $media = $this->db->get_where('media', [
                'media_id' => $mediaId,
                'ig_user_id' => $igUserId
            ])->row_array();
            if (!$media) {
                $this->json_res(['success' => false, 'error' => 'Media tidak ditemukan atau bukan milik akun ini.']);
            }

            $comments = $this->db->where('ig_user_id', $igUserId)
                ->where('media_id', $mediaId)
                ->order_by('COALESCE(parent_id, comment_id)', 'ASC', false)
                ->order_by('created_at', 'ASC')
                ->get('comments')
                ->result_array();

            $this->json_res(['success' => true, 'data' => $comments]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function reply_comment()
    {
        try {
            $igUserId = $this->input->post('ig_user_id');
            $commentId = $this->input->post('comment_id');
            $mediaId = $this->input->post('media_id');
            $message = trim((string)$this->input->post('message'));

            $this->assertAccountAccess($igUserId);
            if (!$commentId || $message === '') {
                $this->json_res(['success' => false, 'error' => 'comment_id dan message wajib diisi.']);
            }

            $tokenRow = $this->db->select('access_token,username')->get_where('access_tokens', ['ig_user_id' => $igUserId])->row_array();
            if (!$tokenRow) {
                $this->json_res(['success' => false, 'error' => 'Token not found']);
            }

            $response = callGraphAPI($this->igGraphUrl('/' . rawurlencode((string)$commentId) . '/replies'), 'POST', [
                'message' => $message,
                'access_token' => $tokenRow['access_token'],
            ]);

            if (isset($response['error'])) {
                $this->json_res(['success' => false, 'error' => $response['error']['message'] ?? 'Gagal membalas komentar.']);
            }

            $replyId = $response['id'] ?? ('local_reply_' . time() . '_' . mt_rand(1000, 9999));
            $this->db->insert('comments', [
                'comment_id' => $replyId,
                'ig_user_id' => $igUserId,
                'media_id' => $mediaId,
                'parent_id' => $commentId,
                'from_username' => $tokenRow['username'] ?? 'me',
                'text' => $message,
                'like_count' => 0,
                'timestamp' => date('Y-m-d H:i:s'),
                'is_from_webhook' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->json_res(['success' => true, 'id' => $replyId]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function delete_account()
    {
        $igUserId = $this->input->get('ig_user_id');
        if (!$igUserId) {
            $this->json_res(['success' => false, 'error' => 'ig_user_id required']);
        }

        try {
            $userData = useraAuthData();
            $user_email = $userData['email'] ?? '';
            $level = $userData['level'] ?? '';

            // If not admin, verify ownership before deleting
            if ($user_email !== 'blowebdev17@gmail.com' && $level !== 'admin') {
                $check = $this->db->get_where('access_tokens', [
                    'ig_user_id' => $igUserId,
                    'user_email' => $user_email
                ])->row_array();
                if (!$check) {
                    $this->json_res(['success' => false, 'error' => 'Anda tidak memiliki hak untuk menghapus akun ini.']);
                }
            }

            $this->db->delete('access_tokens', ['ig_user_id' => $igUserId]);
            $this->json_res(['success' => true]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
