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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function normalizeRedirectUri($uri)
{
    return rtrim(trim($uri), '/');
}

$configuredRedirectUri = normalizeRedirectUri(IG_REDIRECT_URI);

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

if (!$code) {
    $state = bin2hex(random_bytes(16));
    $_SESSION['ig_oauth_states'][$state] = [
        'redirect_uri' => $configuredRedirectUri,
        'created_at'   => time(),
    ];

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
    ]);
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
$tokenRedirectUri = $configuredRedirectUri;

if ($state && isset($_SESSION['ig_oauth_states'][$state])) {
    $tokenRedirectUri = normalizeRedirectUri($_SESSION['ig_oauth_states'][$state]['redirect_uri']);
    unset($_SESSION['ig_oauth_states'][$state]);
} else {
    writeLog('OAuth state missing or expired; using configured redirect URI', [
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
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData)); // Gunakan url-encoded standar
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
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
                    <strong>Sent Redirect URI:</strong> " . htmlspecialchars($tokenRedirectUri) . "<br>
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
// STEP 6: TAMPILKAN HASIL
// ========================================
$username = $profile['username'] ?? 'N/A';
$name = $profile['name'] ?? 'N/A';
$accountType = $profile['account_type'] ?? 'N/A';
$followers = $profile['followers_count'] ?? 'N/A';
$mediaCount = $profile['media_count'] ?? 'N/A';
$profilePic = $profile['profile_picture_url'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koneksi Berhasil - Instagram API</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container" style="margin-top: 40px; max-width: 700px;">
    <div class="card success-card">
        <h1 style="margin-bottom: 20px;">✅ Instagram Berhasil Terhubung!</h1>
        
        <div class="account-info" style="display: flex; gap: 20px; align-items: center;">
            <?php if ($profilePic): ?>
                <img src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" 
                     style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid var(--accent);">
            <?php endif; ?>
            <div>
                <p style="font-size: 1.3rem; font-weight: 700;">@<?= htmlspecialchars($username) ?></p>
                <p style="color: var(--text-muted);"><?= htmlspecialchars($name) ?></p>
                <p><span class="badge badge-purple"><?= htmlspecialchars($accountType) ?></span></p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin: 20px 0;">
            <div class="account-info" style="text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 700;"><?= is_numeric($followers) ? number_format($followers) : $followers ?></div>
                <div style="color: var(--text-muted); font-size: 0.85rem;">Followers</div>
            </div>
            <div class="account-info" style="text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 700;"><?= is_numeric($mediaCount) ? number_format($mediaCount) : $mediaCount ?></div>
                <div style="color: var(--text-muted); font-size: 0.85rem;">Posts</div>
            </div>
            <div class="account-info" style="text-align: center;">
                <div style="font-size: 1.5rem; font-weight: 700;"><?= htmlspecialchars($igUserId) ?></div>
                <div style="color: var(--text-muted); font-size: 0.85rem;">User ID</div>
            </div>
        </div>

        <div class="token-info">
            <h3>🔑 Info Token</h3>
            <p><strong>Tipe:</strong> <?= $expiresIn > 3600 ? 'Long-lived (60 hari)' : 'Short-lived (1 jam)' ?></p>
            <p><strong>Berlaku sampai:</strong> <?= htmlspecialchars($expiresAt) ?></p>
            <?php if ($expiresIn > 3600): ?>
                <div class="alert alert-success" style="margin-top: 12px;">
                    <strong>✅ Long-lived token berhasil!</strong>
                    <p>Token berlaku selama ~60 hari. Refresh sebelum expired.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" style="margin-top: 12px;">
                    <strong>⚠️ Short-lived token</strong>
                    <p>Token hanya berlaku ~1 jam. Gagal mendapatkan long-lived token.</p>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 24px; display: flex; gap: 12px;">
            <a href="index.php" class="btn btn-primary">← Ke Dashboard</a>
            <a href="auth.php" class="btn btn-outline">🔄 Hubungkan Lagi</a>
        </div>
    </div>
</div>
</body>
</html>
