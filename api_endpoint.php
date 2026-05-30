<?php
/**
 * =============================================
 * API ENDPOINT (AJAX)
 * =============================================
 * Endpoint ini dipanggil oleh dashboard (index.php) via AJAX
 * untuk mengambil data dari database dan Instagram API
 * 
 * Usage: api_endpoint.php?action=NAMA_ACTION
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$db = getDB();

try {
    switch ($action) {

        // ---- Ambil data akun yang tersimpan ----
        case 'get_accounts':
            $stmt = $db->query("SELECT * FROM access_tokens ORDER BY updated_at DESC");
            $accounts = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $accounts]);
            break;

        // ---- Ambil webhook logs terbaru ----
        case 'get_webhook_logs':
            $limit = (int)($_GET['limit'] ?? 50);
            $stmt = $db->prepare("SELECT * FROM webhook_logs ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $logs]);
            break;

        // ---- Ambil komentar terbaru ----
        case 'get_comments':
            $limit = (int)($_GET['limit'] ?? 50);
            $stmt = $db->prepare("SELECT * FROM comments ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $comments = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $comments]);
            break;

        // ---- Ambil pesan/DM terbaru ----
        case 'get_messages':
            $limit = (int)($_GET['limit'] ?? 50);
            $stmt = $db->prepare("SELECT * FROM messages ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $messages = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $messages]);
            break;

        // ---- Ambil media/posts dari Instagram API ----
        case 'fetch_media':
            $igUserId = $_GET['ig_user_id'] ?? '';
            if (!$igUserId) {
                echo json_encode(['success' => false, 'error' => 'ig_user_id required']);
                break;
            }

            // Ambil token dari DB (Instagram Login simpan di kolom access_token)
            $stmt = $db->prepare("SELECT access_token FROM access_tokens WHERE ig_user_id = :id");
            $stmt->execute([':id' => $igUserId]);
            $token = $stmt->fetchColumn();

            if (!$token) {
                echo json_encode(['success' => false, 'error' => 'Token not found']);
                break;
            }

            // Panggil Instagram Graph API (pakai graph.instagram.com untuk Instagram Login)
            $response = callGraphAPI(IG_GRAPH_API_BASE . '/me/media', 'GET', [
                'access_token' => $token,
                'fields' => 'id,caption,media_type,media_url,permalink,timestamp,like_count,comments_count',
                'limit' => 20,
            ]);

            if (isset($response['error'])) {
                echo json_encode(['success' => false, 'error' => $response['error']['message']]);
                break;
            }

            // Simpan ke database
            $mediaList = $response['data'] ?? [];
            foreach ($mediaList as $media) {
                $stmt = $db->prepare("
                    INSERT INTO media (media_id, ig_user_id, media_type, media_url, permalink, caption, timestamp, like_count, comments_count)
                    VALUES (:media_id, :ig_user_id, :media_type, :media_url, :permalink, :caption, :timestamp, :like_count, :comments_count)
                    ON DUPLICATE KEY UPDATE 
                        like_count = VALUES(like_count),
                        comments_count = VALUES(comments_count),
                        caption = VALUES(caption),
                        updated_at = NOW()
                ");
                $stmt->execute([
                    ':media_id' => $media['id'],
                    ':ig_user_id' => $igUserId,
                    ':media_type' => $media['media_type'] ?? null,
                    ':media_url' => $media['media_url'] ?? null,
                    ':permalink' => $media['permalink'] ?? null,
                    ':caption' => $media['caption'] ?? null,
                    ':timestamp' => isset($media['timestamp']) ? date('Y-m-d H:i:s', strtotime($media['timestamp'])) : null,
                    ':like_count' => $media['like_count'] ?? 0,
                    ':comments_count' => $media['comments_count'] ?? 0,
                ]);
            }

            echo json_encode(['success' => true, 'data' => $mediaList, 'count' => count($mediaList)]);
            break;

        // ---- Ambil komentar dari post tertentu via API ----
        case 'fetch_comments':
            $mediaId = $_GET['media_id'] ?? '';
            $igUserId = $_GET['ig_user_id'] ?? '';
            if (!$mediaId || !$igUserId) {
                echo json_encode(['success' => false, 'error' => 'media_id and ig_user_id required']);
                break;
            }

            $stmt = $db->prepare("SELECT access_token FROM access_tokens WHERE ig_user_id = :id");
            $stmt->execute([':id' => $igUserId]);
            $token = $stmt->fetchColumn();

            if (!$token) {
                echo json_encode(['success' => false, 'error' => 'Token not found']);
                break;
            }

            $response = callGraphAPI(IG_GRAPH_API_BASE . '/' . $mediaId . '/comments', 'GET', [
                'access_token' => $token,
                'fields' => 'id,text,username,timestamp,like_count,replies{id,text,username,timestamp}',
                'limit' => 50,
            ]);

            if (isset($response['error'])) {
                echo json_encode(['success' => false, 'error' => $response['error']['message']]);
                break;
            }

            // Simpan ke database
            $commentsList = $response['data'] ?? [];
            foreach ($commentsList as $comment) {
                $stmt = $db->prepare("
                    INSERT INTO comments (comment_id, media_id, from_username, text, timestamp, is_from_webhook)
                    VALUES (:comment_id, :media_id, :from_username, :text, :timestamp, 0)
                    ON DUPLICATE KEY UPDATE text = VALUES(text)
                ");
                $stmt->execute([
                    ':comment_id' => $comment['id'],
                    ':media_id' => $mediaId,
                    ':from_username' => $comment['username'] ?? null,
                    ':text' => $comment['text'] ?? '',
                    ':timestamp' => isset($comment['timestamp']) ? date('Y-m-d H:i:s', strtotime($comment['timestamp'])) : null,
                ]);
            }

            echo json_encode(['success' => true, 'data' => $commentsList, 'count' => count($commentsList)]);
            break;

        // ---- Statistik dashboard ----
        case 'get_stats':
            $stats = [];
            $stats['total_accounts'] = $db->query("SELECT COUNT(*) FROM access_tokens")->fetchColumn();
            $stats['total_webhook_events'] = $db->query("SELECT COUNT(*) FROM webhook_logs")->fetchColumn();
            $stats['total_comments'] = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
            $stats['total_messages'] = $db->query("SELECT COUNT(*) FROM messages")->fetchColumn();
            $stats['total_media'] = $db->query("SELECT COUNT(*) FROM media")->fetchColumn();
            $stats['latest_event'] = $db->query("SELECT created_at FROM webhook_logs ORDER BY created_at DESC LIMIT 1")->fetchColumn() ?: '-';
            echo json_encode(['success' => true, 'data' => $stats]);
            break;

        // ---- Ambil media dari database ----
        case 'get_media':
            $limit = (int)($_GET['limit'] ?? 20);
            $stmt = $db->prepare("SELECT * FROM media ORDER BY timestamp DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $media = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $media]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
    }

} catch (Exception $e) {
    writeLog('API Error', ['action' => $action, 'error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
