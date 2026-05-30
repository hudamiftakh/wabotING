<?php
require_once __DIR__ . '/config.php';

$token = "IGAAayCfjZCv1NBZAFpXZAWJyWk1NcVZAXYlNkV3NQTW1yNXZAwZAFhjRUM2bTRwTzJsaHVXM1ExMjVOWWd6eGtQNTg4U19XZAjB6Y0ZAoLXUyUFZASb1lzWVowTlFLNzhEVmJIUWJnSU1WY2xtaTRWUkV4R2ZAjVFV4dUJsc3ItOFgtdDR0NAZDZD";

echo "<h2>Mengambil data dari token...</h2>";

$profile = callGraphAPI(IG_GRAPH_API_BASE . '/me', 'GET', [
    'fields'       => 'user_id,username,name,account_type',
    'access_token' => $token,
]);

if (isset($profile['error']) || !isset($profile['user_id'])) {
    die("Gagal memvalidasi token: " . json_encode($profile));
}

echo "Berhasil mendapatkan data profil Instagram: <b>@" . htmlspecialchars($profile['username']) . "</b><br>";

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        INSERT INTO access_tokens (ig_user_id, username, name, access_token, token_type, expires_at)
        VALUES (:ig_user_id, :username, :name, :access_token, 'bearer', DATE_ADD(NOW(), INTERVAL 60 DAY))
        ON DUPLICATE KEY UPDATE 
            username = VALUES(username),
            name = VALUES(name),
            access_token = VALUES(access_token),
            expires_at = VALUES(expires_at),
            updated_at = NOW()
    ");

    $stmt->execute([
        ':ig_user_id'    => $profile['user_id'],
        ':username'      => $profile['username'] ?? '',
        ':name'          => $profile['name'] ?? '',
        ':access_token'  => $token,
    ]);

    echo "<h3 style='color:green;'>✅ Token berhasil disimpan ke Database!</h3>";
    echo "<p><a href='index.php'>Kembali ke Dashboard</a></p>";

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
