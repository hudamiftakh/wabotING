<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 */
class Dashboard extends CI_Controller
{
    private $oauthDebugVersion = '2026-05-30-state-v3';

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        
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

    public function index()
    {
        // Ambil akun terhubung
        $data['accounts'] = $this->db->order_by('updated_at', 'DESC')->get('access_tokens')->result_array();
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
        $payload = [
            'redirect_uri' => $redirectUri,
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

    public function instagram_login()
    {
        $configuredRedirectUri = $this->normalizeRedirectUri(IG_REDIRECT_URI);
        $state = $this->encodeOAuthState($configuredRedirectUri);
        
        $scopes = implode(',', [
            'instagram_business_basic',
            'instagram_business_manage_comments',
            'instagram_business_manage_messages',
        ]);

        $authUrl = IG_AUTH_URL . '?' . http_build_query([
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
        $configuredRedirectUri = $this->normalizeRedirectUri(IG_REDIRECT_URI);
        
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

        if (!$state) {
            writeLog('Instagram Callback Error: Missing state');
            $this->show_oauth_error('State Missing', 'Link callback tidak berisi state token.', 'Link callback kemungkinan sudah kadaluarsa.');
            return;
        }

        $tokenRedirectUri = $configuredRedirectUri;
        $statePayload = $this->decodeOAuthState($state);

        if ($statePayload) {
            $tokenRedirectUri = $this->normalizeRedirectUri($statePayload['redirect_uri']);
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);

        $tokenResponse = $response ? json_decode($response, true) : ['error' => 'cURL fail: ' . $curlErr];
        writeLog('Short-lived Token Response', $tokenResponse);

        if (isset($tokenResponse['error_type']) || isset($tokenResponse['error'])) {
            $errMsg = $tokenResponse['error_message'] ?? $tokenResponse['error']['message'] ?? 'Gagal menukarkan kode.';
            $this->show_oauth_error('Gagal Mendapatkan Token', $errMsg, json_encode($tokenResponse));
            return;
        }

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
        $profile = callGraphAPI(IG_GRAPH_API_BASE . '/me', 'GET', [
            'fields'       => 'user_id,username,name,account_type,profile_picture_url,followers_count,media_count',
            'access_token' => $accessToken,
        ]);
        writeLog('Profile Response', $profile);

        if (isset($profile['error']) || !isset($profile['user_id'])) {
            $errMsg = $profile['error']['message'] ?? 'Gagal mengambil data profil dari Instagram.';
            $this->show_oauth_error('Gagal Mengambil Profil', $errMsg, json_encode($profile));
            return;
        }

        // 5. Simpan ke database
        $ig_user_id = $profile['user_id'];
        $username = $profile['username'] ?? null;
        $name = $profile['name'] ?? null;

        $check = $this->db->get_where('access_tokens', ['ig_user_id' => $ig_user_id])->row_array();

        $tokenData = [
            'ig_user_id' => $ig_user_id,
            'username' => $username,
            'name' => $name,
            'access_token' => $accessToken,
            'token_type' => $tokenType,
            'expires_at' => $expiresAt,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($check) {
            $this->db->where('ig_user_id', $ig_user_id)->update('access_tokens', $tokenData);
        } else {
            $tokenData['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('access_tokens', $tokenData);
        }

        writeLog('Token saved to database in Controller', ['ig_user_id' => $ig_user_id]);

        redirect('dashboard?status=success&username=' . urlencode($username));
        exit;
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

    public function get_stats()
    {
        try {
            $stats = [];
            $stats['total_accounts'] = $this->db->count_all('access_tokens');
            $stats['total_webhook_events'] = $this->db->count_all('webhook_logs');
            $stats['total_comments'] = $this->db->count_all('comments');
            $stats['total_messages'] = $this->db->count_all('messages');
            $stats['total_media'] = $this->db->count_all('media');
            $latest = $this->db->select('created_at')->order_by('created_at', 'DESC')->limit(1)->get('webhook_logs')->row_array();
            $stats['latest_event'] = $latest ? $latest['created_at'] : '-';

            $this->json_res(['success' => true, 'data' => $stats]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_accounts()
    {
        try {
            $accounts = $this->db->order_by('updated_at', 'DESC')->get('access_tokens')->result_array();
            $this->json_res(['success' => true, 'data' => $accounts]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_webhook_logs()
    {
        try {
            $limit = (int)($this->input->get('limit') ?? 50);
            $logs = $this->db->order_by('created_at', 'DESC')->limit($limit)->get('webhook_logs')->result_array();
            $this->json_res(['success' => true, 'data' => $logs]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_comments()
    {
        try {
            $limit = (int)($this->input->get('limit') ?? 50);
            $comments = $this->db->order_by('created_at', 'DESC')->limit($limit)->get('comments')->result_array();
            $this->json_res(['success' => true, 'data' => $comments]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_messages()
    {
        try {
            $limit = (int)($this->input->get('limit') ?? 50);
            $messages = $this->db->order_by('created_at', 'DESC')->limit($limit)->get('messages')->result_array();
            $this->json_res(['success' => true, 'data' => $messages]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get_media()
    {
        try {
            $limit = (int)($this->input->get('limit') ?? 20);
            $media = $this->db->order_by('timestamp', 'DESC')->limit($limit)->get('media')->result_array();
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

            // Panggil API
            $response = callGraphAPI(IG_GRAPH_API_BASE . '/me/media', 'GET', [
                'access_token' => $token,
                'fields' => 'id,caption,media_type,media_url,permalink,timestamp,like_count,comments_count',
                'limit' => 20,
            ]);

            if (isset($response['error'])) {
                $this->json_res(['success' => false, 'error' => $response['error']['message']]);
            }

            // Simpan/update media
            $mediaList = $response['data'] ?? [];
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
            }

            $this->json_res(['success' => true, 'data' => $mediaList, 'count' => count($mediaList)]);
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

            $response = callGraphAPI(IG_GRAPH_API_BASE . '/' . $mediaId . '/comments', 'GET', [
                'access_token' => $token,
                'fields' => 'id,text,username,timestamp,like_count,replies{id,text,username,timestamp}',
                'limit' => 50,
            ]);

            if (isset($response['error'])) {
                $this->json_res(['success' => false, 'error' => $response['error']['message']]);
            }

            $commentsList = $response['data'] ?? [];
            foreach ($commentsList as $comment) {
                $comment_id = $comment['id'];
                $exist = $this->db->get_where('comments', ['comment_id' => $comment_id])->row_array();
                
                $commentData = [
                    'comment_id' => $comment_id,
                    'media_id' => $mediaId,
                    'from_username' => $comment['username'] ?? null,
                    'text' => $comment['text'] ?? '',
                    'timestamp' => isset($comment['timestamp']) ? date('Y-m-d H:i:s', strtotime($comment['timestamp'])) : null,
                    'is_from_webhook' => 0
                ];

                if ($exist) {
                    $this->db->where('comment_id', $comment_id)->update('comments', $commentData);
                } else {
                    $commentData['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('comments', $commentData);
                }
            }

            $this->json_res(['success' => true, 'data' => $commentsList, 'count' => count($commentsList)]);
        } catch (Exception $e) {
            $this->json_res(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
