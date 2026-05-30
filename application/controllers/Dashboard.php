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

        $userData = useraAuthData();
        $user_email = $userData['email'] ?? null;

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

            $this->db->where('ig_user_id', $igUserId)->update('access_tokens', [
                'access_token' => $accessToken,
                'expires_at' => $expiresAt,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->json_res(['success' => true, 'expires_at' => $expiresAt]);
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

            // Sinkronisasi profil terupdate
            $profile = callGraphAPI(IG_GRAPH_API_BASE . '/me', 'GET', [
                'fields'       => 'user_id,username,name,profile_picture_url,followers_count,media_count',
                'access_token' => $token,
            ]);
            if ($profile && !isset($profile['error'])) {
                $this->db->where('ig_user_id', $igUserId)->update('access_tokens', [
                    'name' => $profile['name'] ?? null,
                    'profile_picture_url' => $profile['profile_picture_url'] ?? null,
                    'followers_count' => $profile['followers_count'] ?? 0,
                    'media_count' => $profile['media_count'] ?? 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

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

                $commentsResponse = callGraphAPI(IG_GRAPH_API_BASE . '/' . $media_id . '/comments', 'GET', [
                    'access_token' => $token,
                    'fields' => 'id,text,username,timestamp,like_count',
                    'limit' => 50,
                ]);

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

            $this->json_res(['success' => true, 'data' => $mediaList, 'count' => count($mediaList), 'comments_count' => $syncedComments]);
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
                    'ig_user_id' => $igUserId,
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
