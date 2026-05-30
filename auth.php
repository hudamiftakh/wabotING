<?php
/**
 * =============================================
 * OAUTH CALLBACK - INSTAGRAM LOGIN FLOW
 * =============================================
 * 
 * PERBEDAAN PENTING:
 * - Facebook Login → pakai Facebook App ID → URL: facebook.com/dialog/oauth
 * - Instagram Login → pakai Instagram App ID → URL: instagram.com/oauth/authorize
 * 
 * Kita pakai INSTAGRAM LOGIN karena App ID kamu adalah Instagram App ID.
 * 
 * ALUR:
 * 1. User klik "Hubungkan Instagram" → redirect ke instagram.com/oauth/authorize
 * 2. User login & izinkan → Instagram redirect balik ke sini dengan ?code=XXX
 * 3. Kita tukar code → short-lived access token (berlaku 1 jam)
 * 4. Tukar short-lived → long-lived token (berlaku 60 hari)
 * 5. Ambil profil user & simpan ke database
 * 
 * SCOPE yang tersedia untuk Instagram Login:
 * - instagram_business_basic          → Akses profil & media
 * - instagram_business_manage_messages → Baca & kirim DM
 * - instagram_business_manage_comments → Baca & balas komentar
 * - instagram_business_content_publish → Publish konten
 */

require_once __DIR__ . '/config.php';

function normalizeRedirectUri($uri)
{
    return rtrim(trim($uri), '/');
}

function encodeOAuthState($redirectUri)
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

function decodeOAuthState($state)
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

$configuredRedirectUri = normalizeRedirectUri(IG_REDIRECT_URI);
$oauthDebugVersion = '2026-05-30-state-v3';

// ========================================
// CEK ERROR DARI INSTAGRAM
// ========================================
$error = $_GET['error'] ?? null;
if ($error) {
    writeLog('OAuth Error: User denied access', $_GET);
    die("
        <link rel='stylesheet' href='assets/style.css'>
        <div class='container' style='margin-top:50px'>
            <div class='card'>
                <h2>❌ Akses Ditolak</h2>
                <p style='margin:12px 0'>Kamu menolak izin akses Instagram.</p>
                <p style='color:var(--text-muted)'>Error: " . htmlspecialchars($_GET['error_description'] ?? $_GET['error_reason'] ?? $error) . "</p>
                <a href='index.php' class='btn btn-primary' style='margin-top:16px'>← Kembali ke Dashboard</a>
            </div>
        </div>
    ");
}

// ========================================
// STEP 1: JIKA BELUM ADA CODE → REDIRECT KE INSTAGRAM
// ========================================
$code = $_GET['code'] ?? null;
$isDebug = isset($_GET['debug_oauth']);

if (!$code || $isDebug) {
    $state = encodeOAuthState($configuredRedirectUri);

    // Buat URL authorization menggunakan Instagram Login
    // PENTING: URL-nya instagram.com, BUKAN facebook.com
    $scopes = implode(',', [
        'instagram_business_basic',           // Profil & media dasar
        'instagram_business_manage_comments', // Kelola komentar
        'instagram_business_manage_messages', // Kelola DM (opsional)
    ]);

    $authUrl = IG_AUTH_URL . '?' . http_build_query([
        'client_id'     => IG_APP_ID,
        'redirect_uri'  => $configuredRedirectUri,
        'response_type' => 'code',
        'scope'         => $scopes,
        'state'         => $state,
    ]);
    
    writeLog('Redirecting to Instagram Login', [
        'url'          => $authUrl,
        'redirect_uri' => $configuredRedirectUri,
        'state'        => $state,
        'debug_version' => $oauthDebugVersion,
    ]);

    if ($isDebug) {
        die("
            <link rel='stylesheet' href='assets/style.css'>
            <div class='container' style='margin-top:50px'>
                <div class='card'>
                    <h2>OAuth Debug</h2>
                    <p><strong>Version:</strong> " . htmlspecialchars($oauthDebugVersion) . "</p>
                    <p><strong>Redirect URI:</strong> <code>" . htmlspecialchars($configuredRedirectUri) . "</code></p>
                    <p><strong>Authorize URL:</strong></p>
                    <div class='code-block' style='font-size:12px; background:#f4f4f4; padding:10px; border-radius:5px; word-break:break-all;'>" . htmlspecialchars($authUrl) . "</div>
                    <a href='" . htmlspecialchars($authUrl) . "' class='btn btn-primary' style='margin-top:16px'>Mulai OAuth dari URL ini</a>
                </div>
            </div>
        ");
    }

    header('Location: ' . $authUrl);
    exit;
}

// ========================================
// STEP 2: TUKAR CODE → SHORT-LIVED TOKEN
// ========================================
// Instagram mengirim code setelah user login & izinkan
// Code ini harus ditukar dengan access token dalam waktu singkat

// Hapus #_ atau spasi jika tidak sengaja terbawa di URL
$code = str_replace('#_', '', $code);
$code = trim($code);
$state = $_GET['state'] ?? null;

if (!$state) {
    writeLog('OAuth callback rejected: missing state; likely stale callback URL', [
        'code'         => substr($code, 0, 20) . '...',
        'redirect_uri' => $configuredRedirectUri,
    ]);

    die("
        <link rel='stylesheet' href='assets/style.css'>
        <div class='container' style='margin-top:50px'>
            <div class='card'>
                <h2>OAuth Perlu Diulang</h2>
                <p style='margin:12px 0; color:var(--danger)'>Link callback ini tidak berisi state OAuth. Biasanya ini terjadi karena link lama di-refresh atau code lama dibuka ulang.</p>
                <p style='font-size:13px; margin-top:15px'>Klik tombol di bawah untuk membuat request OAuth baru dengan Redirect URI <code>" . htmlspecialchars($configuredRedirectUri) . "</code>.</p>
                <a href='auth.php' class='btn btn-primary' style='margin-top:16px'>Hubungkan Instagram Ulang</a>
            </div>
        </div>
    ");
}

$tokenRedirectUri = $configuredRedirectUri;
$statePayload = decodeOAuthState($state);

if ($statePayload) {
    $tokenRedirectUri = normalizeRedirectUri($statePayload['redirect_uri']);
} else {
    writeLog('OAuth state missing or invalid; using configured redirect URI', [
        'received_state' => $state,
        'redirect_uri'   => $tokenRedirectUri,
    ]);
}

writeLog('Received OAuth code', [
    'code'         => substr($code, 0, 20) . '...',
    'state'        => $state,
    'redirect_uri' => $tokenRedirectUri,
]);

// POST ke https://api.instagram.com/oauth/access_token
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
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData)); // Menggunakan application/x-www-form-urlencoded
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

$tokenResponse = $response ? json_decode($response, true) : ['error' => 'cURL fail: ' . $error];

writeLog('Short-lived Token Response', $tokenResponse);

// Cek error
if (isset($tokenResponse['error_type']) || isset($tokenResponse['error'])) {
    $errMsg = $tokenResponse['error_message'] ?? $tokenResponse['error']['message'] ?? 'Unknown error';
    die("
        <link rel='stylesheet' href='assets/style.css'>
        <div class='container' style='margin-top:50px'>
            <div class='card'>
                <h2>❌ Gagal Mendapatkan Token</h2>
                <p style='margin:12px 0; color:var(--danger)'>$errMsg</p>
                <div class='code-block' style='font-size:12px; background:#f4f4f4; padding:10px; border-radius:5px;'>
                    <strong>DEBUG INFO:</strong><br>
                    <strong>App Debug Version:</strong> " . htmlspecialchars($oauthDebugVersion) . "<br>
                    <strong>Sent Redirect URI:</strong> " . htmlspecialchars($tokenRedirectUri) . "<br>
                    <strong>Received State:</strong> " . htmlspecialchars($state) . "<br>
                    <strong>State Valid:</strong> " . ($statePayload ? 'yes' : 'no') . "<br>
                    <strong>Sent Client ID:</strong> " . htmlspecialchars(IG_APP_ID) . "<br>
                    <strong>Response:</strong><br>" . htmlspecialchars(json_encode($tokenResponse, JSON_PRETTY_PRINT)) . "
                </div>
                <p style='font-size:13px; margin-top:15px'><strong>PENTING:</strong> Jangan refresh link callback lama. Klik tombol Hubungkan Instagram dari dashboard supaya kode OAuth baru dibuat dengan Redirect URI <code>" . htmlspecialchars($tokenRedirectUri) . "</code>.</p>
                <a href='index.php' class='btn btn-primary' style='margin-top:16px'>← Kembali</a>
            </div>
        </div>
    ");
}

$shortLivedToken = $tokenResponse['access_token'];
$igUserId = $tokenResponse['user_id']; // Instagram User ID

writeLog('Got short-lived token', ['user_id' => $igUserId]);

// ========================================
// STEP 3: TUKAR SHORT-LIVED → LONG-LIVED TOKEN
// ========================================
// Short-lived: berlaku ~1 jam
// Long-lived: berlaku ~60 hari
// 
// GET https://graph.instagram.com/access_token
//   ?grant_type=ig_exchange_token
//   &client_secret=XXX
//   &access_token=SHORT_LIVED_TOKEN

$longLivedResponse = callGraphAPI(IG_GRAPH_API_BASE . '/access_token', 'GET', [
    'grant_type'    => 'ig_exchange_token',
    'client_secret' => IG_APP_SECRET,
    'access_token'  => $shortLivedToken,
]);

writeLog('Long-lived Token Response', $longLivedResponse);

// Gunakan long-lived token jika berhasil, fallback ke short-lived
$accessToken = $longLivedResponse['access_token'] ?? $shortLivedToken;
$expiresIn = $longLivedResponse['expires_in'] ?? 3600; // default 1 jam
$expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
$tokenType = $longLivedResponse['token_type'] ?? 'bearer';

// ========================================
// STEP 4: AMBIL PROFIL USER
// ========================================
// GET https://graph.instagram.com/me
//   ?fields=user_id,username,name,account_type,profile_picture_url,...
//   &access_token=XXX

$profile = callGraphAPI(IG_GRAPH_API_BASE . '/me', 'GET', [
    'fields'       => 'user_id,username,name,account_type,profile_picture_url,followers_count,media_count',
    'access_token' => $accessToken,
]);

writeLog('Profile Response', $profile);

// Cek jika ada error dari pemanggilan profile
if (isset($profile['error']) || !isset($profile['user_id'])) {
    $errMsg = $profile['error']['message'] ?? 'Gagal mengambil data profil dari Instagram.';
    writeLog('Profile Fetch Error', $profile);
    die("
        <link rel='stylesheet' href='assets/style.css'>
        <div class='container' style='margin-top:50px'>
            <div class='card'>
                <h2>❌ Gagal Mengambil Profil</h2>
                <p style='margin:12px 0; color:var(--danger)'>$errMsg</p>
                <div class='code-block' style='font-size:12px; background:#f4f4f4; padding:10px; border-radius:5px;'>
                    <strong>DEBUG INFO:</strong><br>
                    <strong>App Debug Version:</strong> " . htmlspecialchars($oauthDebugVersion) . "<br>
                    <strong>Instagram User ID:</strong> " . htmlspecialchars($igUserId) . "<br>
                    <strong>Response:</strong><br>" . htmlspecialchars(json_encode($profile, JSON_PRETTY_PRINT)) . "
                </div>
                <a href='index.php' class='btn btn-primary' style='margin-top:16px'>← Kembali ke Dashboard</a>
            </div>
        </div>
    ");
}

// ========================================
// STEP 5: SIMPAN KE DATABASE
// ========================================
$db = getDB();

$stmt = $db->prepare("
    INSERT INTO access_tokens (ig_user_id, username, name, access_token, token_type, expires_at)
    VALUES (:ig_user_id, :username, :name, :access_token, :token_type, :expires_at)
    ON DUPLICATE KEY UPDATE 
        username = VALUES(username),
        name = VALUES(name),
        access_token = VALUES(access_token),
        token_type = VALUES(token_type),
        expires_at = VALUES(expires_at),
        updated_at = NOW()
");

$stmt->execute([
    ':ig_user_id'    => $profile['user_id'] ?? $igUserId,
    ':username'      => $profile['username'] ?? null,
    ':name'          => $profile['name'] ?? null,
    ':access_token'  => $accessToken,
    ':token_type'    => $tokenType,
    ':expires_at'    => $expiresAt,
]);

writeLog('Token saved to database', ['ig_user_id' => $igUserId]);

// ========================================
// REDIRECT LANGSUNG KE DASHBOARD
// ========================================
$username = $profile['username'] ?? 'N/A';
header('Location: index.php?status=success&username=' . urlencode($username));
exit;
