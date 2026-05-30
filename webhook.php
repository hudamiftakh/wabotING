<?php
/**
 * =============================================
 * WEBHOOK ENDPOINT
 * =============================================
 * 
 * File ini menerima notifikasi real-time dari Meta/Instagram.
 * 
 * CARA KERJA WEBHOOK:
 * 1. Kamu daftarkan URL ini di Meta Developer Dashboard
 *    (Contoh: https://domain-kamu.com/webhook.php)
 * 
 * 2. Meta akan kirim request GET untuk VERIFIKASI
 *    - Meta kirim: hub.mode, hub.challenge, hub.verify_token
 *    - Kita cek verify_token cocok → kirim balik hub.challenge
 * 
 * 3. Setelah terverifikasi, Meta kirim POST setiap ada event baru
 *    - Komentar baru di post
 *    - Pesan masuk (DM)
 *    - Mention di story
 *    - dll.
 * 
 * URL webhook ini HARUS:
 *    ✅ HTTPS (SSL)
 *    ✅ Bisa diakses publik (bukan localhost)
 *    ✅ Merespon dalam 20 detik
 * 
 * TIPS: Gunakan ngrok untuk testing di localhost:
 *    ngrok http 80
 *    Lalu pakai URL ngrok sebagai webhook URL
 */

require_once __DIR__ . '/config.php';

// ========================================
// STEP 1: VERIFIKASI WEBHOOK (GET REQUEST)
// ========================================
// Meta akan kirim GET request pertama kali untuk memastikan 
// webhook URL kamu valid dan milik kamu.

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Ambil parameter yang dikirim Meta
    $mode = $_GET['hub_mode'] ?? '';           // Harus "subscribe"
    $token = $_GET['hub_verify_token'] ?? '';   // Token yang kamu set di dashboard
    $challenge = $_GET['hub_challenge'] ?? '';  // Challenge yang harus dikembalikan

    writeLog('Webhook Verification Request', [
        'mode' => $mode,
        'token' => $token,
        'challenge' => $challenge,
    ]);

    // Cek apakah mode = subscribe DAN token cocok
    if ($mode === 'subscribe' && $token === WEBHOOK_VERIFY_TOKEN) {
        // ✅ Verifikasi berhasil! Kirim balik challenge
        writeLog('Webhook Verification SUCCESS');
        http_response_code(200);
        echo $challenge;  // PENTING: harus echo challenge tanpa tambahan apapun
        exit;
    } else {
        // ❌ Token tidak cocok
        writeLog('Webhook Verification FAILED - Token mismatch');
        http_response_code(403);
        echo 'Verification failed';
        exit;
    }
}

// ========================================
// STEP 2: TERIMA EVENT WEBHOOK (POST REQUEST)
// ========================================
// Setelah verifikasi berhasil, Meta akan kirim POST request
// setiap ada event baru (komentar, pesan, dll)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Baca body request (JSON)
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    writeLog('Webhook POST Received', $data);

    // Validasi signature (opsional tapi SANGAT DIREKOMENDASIKAN)
    // Meta mengirim header X-Hub-Signature-256 untuk memastikan 
    // request benar-benar dari Meta
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    if ($signature) {
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $rawInput, IG_APP_SECRET);
        if (!hash_equals($expectedSignature, $signature)) {
            writeLog('Webhook Signature INVALID', [
                'expected' => $expectedSignature,
                'received' => $signature,
            ]);
            http_response_code(403);
            echo 'Invalid signature';
            exit;
        }
        writeLog('Webhook Signature Valid ✅');
    }

    // Pastikan data valid
    if (!$data || !isset($data['object'])) {
        http_response_code(400);
        echo 'Invalid payload';
        exit;
    }

    $db = getDB();

    // ----------------------------------------
    // PROSES SETIAP ENTRY DALAM WEBHOOK
    // ----------------------------------------
    // Struktur data dari Meta:
    // {
    //   "object": "instagram",         ← tipe object
    //   "entry": [                      ← array of entries
    //     {
    //       "id": "123456",             ← ID akun Instagram
    //       "time": 1234567890,         ← timestamp
    //       "changes": [                ← perubahan yang terjadi
    //         {
    //           "field": "comments",    ← field yang berubah
    //           "value": { ... }        ← data perubahannya
    //         }
    //       ]
    //     }
    //   ]
    // }

    $entries = $data['entry'] ?? [];

    foreach ($entries as $entry) {
        $entryId = $entry['id'] ?? null;
        $changes = $entry['changes'] ?? [];
        $messaging = $entry['messaging'] ?? [];

        // ---- PROSES CHANGES (komentar, mention, dll) ----
        foreach ($changes as $change) {
            $field = $change['field'] ?? 'unknown';
            $value = $change['value'] ?? [];

            // Simpan ke webhook_logs (log semua event)
            $stmt = $db->prepare("
                INSERT INTO webhook_logs (object, entry_id, event_type, field, value, raw_payload)
                VALUES (:object, :entry_id, :event_type, :field, :value, :raw_payload)
            ");
            $stmt->execute([
                ':object' => $data['object'],
                ':entry_id' => $entryId,
                ':event_type' => $field,
                ':field' => $field,
                ':value' => json_encode($value),
                ':raw_payload' => $rawInput,
            ]);

            // ---- HANDLE KOMENTAR BARU ----
            if ($field === 'comments') {
                handleNewComment($db, $value, $entryId);
            }

            writeLog("Processed change: field=$field", $value);
        }

        // ---- PROSES MESSAGING (DM) ----
        foreach ($messaging as $msg) {
            // Simpan ke webhook_logs
            $stmt = $db->prepare("
                INSERT INTO webhook_logs (object, entry_id, event_type, field, value, raw_payload)
                VALUES (:object, :entry_id, :event_type, :field, :value, :raw_payload)
            ");
            $stmt->execute([
                ':object' => $data['object'],
                ':entry_id' => $entryId,
                ':event_type' => 'messaging',
                ':field' => 'messaging',
                ':value' => json_encode($msg),
                ':raw_payload' => $rawInput,
            ]);

            handleNewMessage($db, $msg);
        }
    }

    // PENTING: Selalu respon 200 OK dalam 20 detik!
    // Jika tidak, Meta akan retry dan bisa disable webhook
    http_response_code(200);
    echo 'EVENT_RECEIVED';
    exit;
}

// Jika bukan GET atau POST, tolak
http_response_code(405);
echo 'Method not allowed';
exit;

// ========================================
// HANDLER FUNCTIONS
// ========================================

/**
 * Handle komentar baru dari webhook
 * 
 * Value dari webhook comment biasanya berisi:
 * {
 *   "id": "17858893269123456",    ← ID komentar
 *   "text": "Keren banget!",      ← Isi komentar
 *   "from": {
 *     "id": "123456",
 *     "username": "user123"
 *   },
 *   "media": {
 *     "id": "17890012345678",     ← ID post
 *     "media_product_type": "FEED"
 *   }
 * }
 */
function handleNewComment($db, $value, $igUserId) {
    $commentId = $value['id'] ?? null;
    if (!$commentId) return;

    $stmt = $db->prepare("
        INSERT INTO comments (comment_id, media_id, from_id, from_username, text, parent_id, is_from_webhook, timestamp)
        VALUES (:comment_id, :media_id, :from_id, :from_username, :text, :parent_id, 1, NOW())
        ON DUPLICATE KEY UPDATE text = VALUES(text)
    ");

    $stmt->execute([
        ':comment_id' => $commentId,
        ':media_id' => $value['media']['id'] ?? null,
        ':from_id' => $value['from']['id'] ?? null,
        ':from_username' => $value['from']['username'] ?? null,
        ':text' => $value['text'] ?? '',
        ':parent_id' => $value['parent_id'] ?? null,
    ]);

    writeLog('Comment saved to DB', ['comment_id' => $commentId]);
}

/**
 * Handle pesan baru (DM) dari webhook
 */
function handleNewMessage($db, $msgData) {
    $message = $msgData['message'] ?? null;
    if (!$message) return;

    $messageId = $message['mid'] ?? null;
    if (!$messageId) return;

    $stmt = $db->prepare("
        INSERT INTO messages (message_id, sender_id, recipient_id, message_text, attachments, is_from_webhook, timestamp)
        VALUES (:message_id, :sender_id, :recipient_id, :message_text, :attachments, 1, NOW())
        ON DUPLICATE KEY UPDATE message_text = VALUES(message_text)
    ");

    $stmt->execute([
        ':message_id' => $messageId,
        ':sender_id' => $msgData['sender']['id'] ?? null,
        ':recipient_id' => $msgData['recipient']['id'] ?? null,
        ':message_text' => $message['text'] ?? '',
        ':attachments' => isset($message['attachments']) ? json_encode($message['attachments']) : null,
    ]);

    writeLog('Message saved to DB', ['message_id' => $messageId]);
}
