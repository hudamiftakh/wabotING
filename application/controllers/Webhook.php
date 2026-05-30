<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_DB_query_builder $db
 */
class Webhook extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        check_and_create_db_tables();
    }

    public function index()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method === 'GET') {
            $this->handleVerification();
        } elseif ($method === 'POST') {
            $this->handleEvent();
        } else {
            $this->output->set_status_header(405)->set_output('Method not allowed');
        }
    }

    /**
     * Meta Webhook Verification (GET)
     */
    private function handleVerification()
    {
        $mode = $this->input->get('hub_mode') ?? '';
        $token = $this->input->get('hub_verify_token') ?? '';
        $challenge = $this->input->get('hub_challenge') ?? '';

        writeLog('Webhook Verification Request via Controller', [
            'mode' => $mode,
            'token' => $token,
            'challenge' => $challenge,
        ]);

        if ($mode === 'subscribe' && $token === WEBHOOK_VERIFY_TOKEN) {
            writeLog('Webhook Verification SUCCESS');
            $this->output
                ->set_status_header(200)
                ->set_content_type('text/plain')
                ->set_output($challenge);
        } else {
            writeLog('Webhook Verification FAILED - Token mismatch');
            $this->output
                ->set_status_header(403)
                ->set_content_type('text/plain')
                ->set_output('Verification failed');
        }
    }

    /**
     * Meta Webhook Event (POST)
     */
    private function handleEvent()
    {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        writeLog('Webhook POST Received via Controller', $data);

        // Validasi Signature
        $signature = $this->input->server('HTTP_X_HUB_SIGNATURE_256') ?? '';
        if ($signature) {
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $rawInput, IG_APP_SECRET);
            if (!hash_equals($expectedSignature, $signature)) {
                writeLog('Webhook Signature INVALID', [
                    'expected' => $expectedSignature,
                    'received' => $signature,
                ]);
                $this->output->set_status_header(403)->set_output('Invalid signature');
                return;
            }
            writeLog('Webhook Signature Valid ✅');
        }

        if (!$data || !isset($data['object'])) {
            $this->output->set_status_header(400)->set_output('Invalid payload');
            return;
        }

        $entries = $data['entry'] ?? [];

        foreach ($entries as $entry) {
            $entryId = $entry['id'] ?? null;
            $changes = $entry['changes'] ?? [];
            $messaging = $entry['messaging'] ?? [];

            // 1. Process Changes (comments, mentions, live comments, etc.)
            foreach ($changes as $change) {
                $field = $change['field'] ?? 'unknown';
                $value = $change['value'] ?? [];

                // Simpan ke webhook_logs
                $logData = [
                    'object' => $data['object'],
                    'entry_id' => $entryId,
                    'event_type' => $field,
                    'field' => $field,
                    'value' => json_encode($value),
                    'raw_payload' => $rawInput,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->db->insert('webhook_logs', $logData);

                // Handle comments and messages
                if ($field === 'comments') {
                    $this->handleNewComment($value, $entryId);
                } elseif ($field === 'messages') {
                    $this->handleNewMessageChange($value, $entryId);
                }

                writeLog("Processed change via Controller: field=$field", $value);
            }

            // 2. Process Messaging (Direct Messages / DMs)
            foreach ($messaging as $msg) {
                // Simpan ke webhook_logs
                $logData = [
                    'object' => $data['object'],
                    'entry_id' => $entryId,
                    'event_type' => 'messaging',
                    'field' => 'messaging',
                    'value' => json_encode($msg),
                    'raw_payload' => $rawInput,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->db->insert('webhook_logs', $logData);

                $this->handleNewMessage($msg, $entryId);
            }
        }

        $this->output->set_status_header(200)->set_output('EVENT_RECEIVED');
    }

    /**
     * Handle komentar baru dari webhook
     */
    private function handleNewComment($value, $igUserId)
    {
        $commentId = $value['id'] ?? null;
        if (!$commentId) return;

        $commentData = [
            'comment_id' => $commentId,
            'ig_user_id' => $igUserId,
            'media_id' => $value['media']['id'] ?? null,
            'from_id' => $value['from']['id'] ?? null,
            'from_username' => $value['from']['username'] ?? null,
            'text' => $value['text'] ?? '',
            'like_count' => $value['like_count'] ?? 0,
            'parent_id' => $value['parent_id'] ?? null,
            'is_from_webhook' => 1,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $exist = $this->db->get_where('comments', ['comment_id' => $commentId])->row_array();

        if ($exist) {
            $this->db->where('comment_id', $commentId)->update('comments', $commentData);
        } else {
            $commentData['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('comments', $commentData);
        }

        $this->attemptAutoReply($igUserId, 'comment', $commentId, $commentData['text'], $commentData['from_id']);

        writeLog('Comment saved to DB via Controller', ['comment_id' => $commentId]);
    }

    /**
     * Handle pesan baru (DM) dari webhook
     */
    private function handleNewMessage($msgData, $igUserId)
    {
        $message = $msgData['message'] ?? null;
        if (!$message) {
            writeLog('DM skipped: message node missing', $msgData);
            return;
        }

        $messageId = $message['mid'] ?? $message['id'] ?? $msgData['message_id'] ?? null;
        if (!$messageId) {
            writeLog('DM skipped: message id missing', $msgData);
            return;
        }

        $messageText = $this->normalizeMessageText($message);
        $senderId = $msgData['sender']['id'] ?? $msgData['from']['id'] ?? $msgData['from'] ?? null;
        $recipientId = $msgData['recipient']['id'] ?? $msgData['to']['id'] ?? $msgData['to'] ?? null;

        $messageData = [
            'message_id' => $messageId,
            'ig_user_id' => $igUserId,
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'message_text' => $messageText,
            'attachments' => isset($message['attachments']) ? json_encode($message['attachments']) : null,
            'is_from_webhook' => 1,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $exist = $this->db->get_where('messages', ['message_id' => $messageId])->row_array();

        if ($exist) {
            $this->db->where('message_id', $messageId)->update('messages', $messageData);
        } else {
            $messageData['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('messages', $messageData);
        }

        $this->attemptAutoReply($igUserId, 'message', $messageData['sender_id'], $messageData['message_text'], $messageData['sender_id']);

        writeLog('Message saved to DB via Controller', ['message_id' => $messageId]);
    }

    /**
     * Handle pesan baru (DM) dari webhook dalam format changes
     */
    private function handleNewMessageChange($value, $igUserId)
    {
        if (!empty($value['messages']) && is_array($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->handleNewMessage([
                    'sender' => ['id' => $message['from'] ?? ($value['from']['id'] ?? null)],
                    'recipient' => ['id' => $igUserId],
                    'message' => $message,
                ], $igUserId);
            }
            return;
        }

        $messageNode = is_array($value['message'] ?? null) ? $value['message'] : $value;
        $messageId = $value['id'] ?? $value['message_id'] ?? $messageNode['mid'] ?? $messageNode['id'] ?? null;
        if (!$messageId) {
            writeLog('DM change skipped: message id missing', $value);
            return;
        }

        $senderId = $value['sender']['id'] ?? $value['from']['id'] ?? $value['from'] ?? $messageNode['from'] ?? null;
        $recipientId = $value['recipient']['id'] ?? $value['to']['id'] ?? $value['to'] ?? $igUserId;
        $messageText = $this->normalizeMessageText($messageNode);

        $messageData = [
            'message_id' => $messageId,
            'ig_user_id' => $igUserId,
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'message_text' => $messageText,
            'attachments' => isset($messageNode['attachments']) ? json_encode($messageNode['attachments']) : null,
            'is_from_webhook' => 1,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $exist = $this->db->get_where('messages', ['message_id' => $messageId])->row_array();

        if ($exist) {
            $this->db->where('message_id', $messageId)->update('messages', $messageData);
        } else {
            $messageData['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('messages', $messageData);
        }

        $this->attemptAutoReply($igUserId, 'message', $messageData['sender_id'], $messageData['message_text'], $messageData['sender_id']);

        writeLog('Message from changes saved to DB via Controller', ['message_id' => $messageId]);
    }

    private function normalizeMessageText($message)
    {
        if (!is_array($message)) {
            return (string)$message;
        }

        if (isset($message['text'])) {
            if (is_array($message['text'])) {
                return (string)($message['text']['body'] ?? json_encode($message['text']));
            }
            return (string)$message['text'];
        }

        if (isset($message['message'])) {
            return is_array($message['message']) ? json_encode($message['message']) : (string)$message['message'];
        }

        if (isset($message['postback']['title'])) {
            return (string)$message['postback']['title'];
        }

        return isset($message['attachments']) ? '[attachment]' : '';
    }

    private function attemptAutoReply($igUserId, $channel, $targetId, $incomingText, $senderId = null)
    {
        if (!$igUserId || !$targetId || trim((string)$incomingText) === '') {
            return;
        }

        $tokenRow = $this->db->select('access_token')->get_where('access_tokens', ['ig_user_id' => $igUserId])->row_array();
        if (!$tokenRow) {
            return;
        }

        $templates = $this->db->where('ig_user_id', $igUserId)
            ->where('is_active', 1)
            ->where('auto_reply', 1)
            ->group_start()
            ->where('channel', $channel)
            ->or_where('channel', 'all')
            ->group_end()
            ->order_by('id', 'ASC')
            ->get('reply_templates')
            ->result_array();

        $incomingTextLower = strtolower((string)$incomingText);
        foreach ($templates as $template) {
            $keyword = strtolower(trim((string)$template['keyword']));
            if ($keyword !== '' && strpos($incomingTextLower, $keyword) === false) {
                continue;
            }

            $responseText = $template['response_text'];
            $payload = [];
            if ($channel === 'comment') {
                $url = IG_GRAPH_API_BASE . '/' . $targetId . '/replies';
                $payload = [
                    'message' => $responseText,
                    'access_token' => $tokenRow['access_token'],
                ];
            } else {
                if (!$senderId || $senderId === $igUserId) {
                    return;
                }
                $url = IG_GRAPH_API_BASE . '/me/messages';
                $payload = [
                    'recipient' => json_encode(['id' => $senderId]),
                    'message' => json_encode(['text' => $responseText]),
                    'access_token' => $tokenRow['access_token'],
                ];
            }

            $response = callGraphAPI($url, 'POST', $payload);
            $this->db->insert('auto_reply_logs', [
                'template_id' => $template['id'],
                'ig_user_id' => $igUserId,
                'channel' => $channel,
                'target_id' => $targetId,
                'request_payload' => json_encode($payload),
                'response_payload' => json_encode($response),
                'status' => isset($response['error']) ? 'failed' : 'sent',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            writeLog('Auto reply attempted', [
                'ig_user_id' => $igUserId,
                'channel' => $channel,
                'target_id' => $targetId,
                'status' => isset($response['error']) ? 'failed' : 'sent',
                'response' => $response,
            ]);
            return;
        }
    }
}
