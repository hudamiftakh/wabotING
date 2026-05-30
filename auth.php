<?php
/**
 * =============================================
 * OAUTH CALLBACK - FACEBOOK LOGIN FLOW
 * =============================================
 * 
 * ALUR (Facebook Login untuk Instagram Graph API):
 * 1. User klik "Hubungkan Instagram" → redirect ke facebook.com/dialog/oauth
 * 2. User login & izinkan → Facebook redirect balik ke sini dengan ?code=XXX
 * 3. Tukar code → short-lived User Access Token
 * 4. Tukar short-lived → long-lived User Access Token
 * 5. Ambil daftar Facebook Page yang dikelola user
 * 6. Cari Page yang terhubung ke akun Instagram Professional
 * 7. Ambil profil Instagram & simpan token Page ke database
 */

require_once __DIR__ . '/config.php';

// ========================================
// CEK ERROR DARI FACEBOOK
// ========================================
$error = $_GET['error'] ?? null;
if ($error) {
    writeLog('OAuth Error: User denied access', $_GET);
    die("
        <link rel='stylesheet' href='assets/style.css'>
        <div class='container' style='margin-top:50px'>
            <div class='card'>
                <h2>❌ Akses Ditolak</h2>
                <p style='margin:12px 0'>Kamu menolak izin akses Facebook/Instagram.</p>
                <p style='color:var(--text-muted)'>Error: " . htmlspecialchars($_GET['error_description'] ?? $_GET['error_reason'] ?? $error) . "</p>
                <a href='index.php' class='btn btn-primary' style='margin-top:16px'>← Kembali ke Dashboard</a>
            </div>
        </div>
    ");
}

// ========================================
// STEP 1: JIKA BELUM ADA CODE → REDIRECT KE FACEBOOK
// ========================================
$code = $_GET['code'] ?? null;

if (!$code) {
    // Scope yang dibutuhkan untuk Instagram Graph API via Facebook Login
    $scopes = implode(',', [
        'pages_show_list',
        'pages_read_engagement',
        'pages_manage_metadata', // Dibutuhkan oleh beberapa webhook
        'instagram_basic',
        'instagram_manage_comments',
        'instagram_manage_messages',
    ]);

    $authUrl = 'https://www.facebook.com/' . IG_GRAPH_API_VERSION . '/dialog/oauth?' . http_build_query([
        'client_id'     => IG_APP_ID,
        'redirect_uri'  => IG_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => $scopes,
    ]);
    
    writeLog('Redirecting to Facebook Login', ['url' => $authUrl]);
    header('Location: ' . $authUrl);
    exit;
}

// ========================================
// STEP 2: TUKAR CODE → SHORT-LIVED USER TOKEN
// ========================================
writeLog('Received OAuth code', ['code' => substr($code, 0, 20) . '...']);

$tokenUrl = FB_GRAPH_API_BASE . '/oauth/access_token';
$tokenResponse = callGraphAPI($tokenUrl, 'GET', [
    'client_id'     => IG_APP_ID,
    'client_secret' => IG_APP_SECRET,
    'redirect_uri'  => IG_REDIRECT_URI,
    'code'          => $code,
]);

writeLog('Short-lived Token Response', $tokenResponse);

if (isset($tokenResponse['error'])) {
    die("Gagal mendapatkan token: " . htmlspecialchars($tokenResponse['error']['message']));
}

$shortLivedUserToken = $tokenResponse['access_token'];

// ========================================
// STEP 3: TUKAR SHORT-LIVED → LONG-LIVED USER TOKEN
// ========================================
$longLivedResponse = callGraphAPI(FB_GRAPH_API_BASE . '/oauth/access_token', 'GET', [
    'grant_type'        => 'fb_exchange_token',
    'client_id'         => IG_APP_ID,
    'client_secret'     => IG_APP_SECRET,
    'fb_exchange_token' => $shortLivedUserToken,
]);

writeLog('Long-lived Token Response', $longLivedResponse);

$userAccessToken = $longLivedResponse['access_token'] ?? $shortLivedUserToken;

// ========================================
// STEP 4: AMBIL DAFTAR HALAMAN FACEBOOK (PAGES)
// ========================================
$pagesResponse = callGraphAPI(FB_GRAPH_API_BASE . '/me/accounts', 'GET', [
    'access_token' => $userAccessToken,
]);

writeLog('Facebook Pages Response', $pagesResponse);

if (empty($pagesResponse['data'])) {
    die("❌ Tidak ada Halaman Facebook yang ditemukan di akun ini. Pastikan Instagram kamu terhubung ke Halaman Facebook.");
}

// ========================================
// STEP 5: CARI PAGE YANG TERHUBUNG KE INSTAGRAM
// ========================================
$igUserId = null;
$pageId = null;
$pageAccessToken = null;

foreach ($pagesResponse['data'] as $page) {
    // Cek apakah page ini terhubung ke Instagram Business
    $igCheck = callGraphAPI(FB_GRAPH_API_BASE . '/' . $page['id'], 'GET', [
        'fields'       => 'instagram_business_account',
        'access_token' => $page['access_token'],
    ]);

    if (isset($igCheck['instagram_business_account']['id'])) {
        $igUserId = $igCheck['instagram_business_account']['id'];
        $pageId = $page['id'];
        $pageAccessToken = $page['access_token']; // Ini long-lived karena user tokennya long-lived
        break; // Dapatkan akun IG pertama yang ditemukan
    }
}

if (!$igUserId) {
    die("❌ Tidak ditemukan Akun Instagram Bisnis/Kreator yang terhubung ke Halaman Facebook kamu. Silakan hubungkan IG ke FP terlebih dahulu.");
}

// ========================================
// STEP 6: AMBIL PROFIL INSTAGRAM
// ========================================
$profile = callGraphAPI(FB_GRAPH_API_BASE . '/' . $igUserId, 'GET', [
    'fields'       => 'username,name,profile_picture_url,followers_count,media_count',
    'access_token' => $pageAccessToken, // Gunakan Page Token!
]);

writeLog('Instagram Profile Response', $profile);

// ========================================
// STEP 7: SIMPAN KE DATABASE
// ========================================
$db = getDB();

$stmt = $db->prepare("
    INSERT INTO access_tokens (ig_user_id, username, name, access_token, page_id, page_access_token, token_type, expires_at)
    VALUES (:ig_user_id, :username, :name, :access_token, :page_id, :page_access_token, 'bearer', DATE_ADD(NOW(), INTERVAL 60 DAY))
    ON DUPLICATE KEY UPDATE 
        username = VALUES(username),
        name = VALUES(name),
        access_token = VALUES(access_token),
        page_id = VALUES(page_id),
        page_access_token = VALUES(page_access_token),
        expires_at = VALUES(expires_at),
        updated_at = NOW()
");

// Kita menyimpan Page Access Token sebagai token utama yang dipakai untuk call API Instagram
$stmt->execute([
    ':ig_user_id'        => $igUserId,
    ':username'          => $profile['username'] ?? null,
    ':name'              => $profile['name'] ?? null,
    ':access_token'      => $pageAccessToken, 
    ':page_id'           => $pageId,
    ':page_access_token' => $pageAccessToken,
]);

writeLog('Tokens saved to database', ['ig_user_id' => $igUserId, 'page_id' => $pageId]);

// ========================================
// STEP 8: TAMPILKAN HASIL
// ========================================
$username = $profile['username'] ?? 'N/A';
$name = $profile['name'] ?? 'N/A';
$followers = $profile['followers_count'] ?? 'N/A';
$mediaCount = $profile['media_count'] ?? 'N/A';
$profilePic = $profile['profile_picture_url'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koneksi Berhasil - Instagram API via Facebook</title>
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
                <div style="color: var(--text-muted); font-size: 0.85rem;">IG User ID</div>
            </div>
        </div>

        <div class="token-info">
            <h3>🔑 Info Integrasi</h3>
            <p><strong>Page ID:</strong> <?= htmlspecialchars($pageId) ?></p>
            <div class="alert alert-success" style="margin-top: 12px;">
                <strong>✅ Long-lived Page Token berhasil didapatkan!</strong>
                <p>Token ini tidak akan expired secara default dan bisa terus dipakai.</p>
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; gap: 12px;">
            <a href="index.php" class="btn btn-primary">← Ke Dashboard</a>
            <a href="auth.php" class="btn btn-outline">🔄 Hubungkan Lagi</a>
        </div>
    </div>
</div>
</body>
</html>
