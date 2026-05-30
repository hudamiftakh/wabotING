<?php

/**
 * =============================================
 * KONFIGURASI INSTAGRAM API
 * =============================================
 * File ini berisi semua konfigurasi yang dibutuhkan
 */

// ---- KONFIGURASI DATABASE ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'instagram_api');
define('DB_USER', 'wabotweb_chatwabot');
define('DB_PASS', 'jombang2017');

// ---- KONFIGURASI INSTAGRAM/META APP ----
define('IG_APP_ID', '2496439197475089');                    // ID Aplikasi Meta (Bukan Instagram App ID)
define('IG_APP_SECRET', '44e7b972c8edfcaebc916ae5c565a4b6'); // Rahasia Aplikasi Meta
define('IG_REDIRECT_URI', 'https://ing.wabot.web.id/auth.php');   // ⚠️ GANTI! Harus HTTPS & terdaftar di Meta Dashboard

// Token verifikasi untuk webhook (buat sendiri, bebas apa saja)
define('WEBHOOK_VERIFY_TOKEN', 'token_verifikasi_instagram_2024');

// ---- INSTAGRAM API ENDPOINTS ----
// Karena kita pakai Instagram App ID (bukan Facebook App ID),
// maka kita harus pakai Instagram Login flow
define('IG_GRAPH_API_VERSION', 'v21.0');
define('IG_GRAPH_API_BASE', 'https://graph.instagram.com');
define('IG_AUTH_URL', 'https://www.instagram.com/oauth/authorize');      // OAuth authorize
define('IG_TOKEN_URL', 'https://api.instagram.com/oauth/access_token');  // Tukar code→token
define('FB_GRAPH_API_BASE', 'https://graph.facebook.com/' . IG_GRAPH_API_VERSION);

// ---- KONEKSI DATABASE ----
function getDB()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            // Terkoneksi langsung ke database (lebih aman untuk hosting/cPanel)
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Cek apakah tabel utama sudah ada, jika belum jalankan db.sql
            $tableExists = $pdo->query("SHOW TABLES LIKE 'access_tokens'")->rowCount() > 0;
            if (!$tableExists && file_exists(__DIR__ . '/db.sql')) {
                $sql = file_get_contents(__DIR__ . '/db.sql');
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
                $pdo->exec($sql);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            }
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    return $pdo;
}

/**
 * Helper: Panggil Instagram/Facebook Graph API
 * 
 * @param string $url URL endpoint
 * @param string $method GET atau POST
 * @param array $data Data yang dikirim (untuk POST)
 * @return array Response dari API
 */
function callGraphAPI($url, $method = 'GET', $data = [])
{
    $ch = curl_init();

    if ($method === 'GET' && !empty($data)) {
        $url .= '?' . http_build_query($data);
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false, // Development only! Aktifkan di production
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => ['message' => 'cURL Error: ' . $error]];
    }

    $decoded = json_decode($response, true);
    return $decoded ?: ['error' => ['message' => 'Invalid JSON response', 'raw' => $response]];
}

/**
 * Helper: Log ke file untuk debugging
 */
function writeLog($message, $data = null)
{
    $logFile = __DIR__ . '/logs/app_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $log = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($data !== null) {
        $log .= "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    $log .= "\n---\n";

    file_put_contents($logFile, $log, FILE_APPEND);
}
