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

                $this->handleNewMessage($msg);
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
            'media_id' => $value['media']['id'] ?? null,
            'from_id' => $value['from']['id'] ?? null,
            'from_username' => $value['from']['username'] ?? null,
            'text' => $value['text'] ?? '',
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

        writeLog('Comment saved to DB via Controller', ['comment_id' => $commentId]);
    }

    /**
     * Handle pesan baru (DM) dari webhook
     */
    private function handleNewMessage($msgData)
    {
        $message = $msgData['message'] ?? null;
        if (!$message) return;

        $messageId = $message['mid'] ?? null;
        if (!$messageId) return;

        $messageData = [
            'message_id' => $messageId,
            'sender_id' => $msgData['sender']['id'] ?? null,
            'recipient_id' => $msgData['recipient']['id'] ?? null,
            'message_text' => $message['text'] ?? '',
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

        writeLog('Message saved to DB via Controller', ['message_id' => $messageId]);
    }

    /**
     * Handle pesan baru (DM) dari webhook dalam format changes
     */
    private function handleNewMessageChange($value, $igUserId)
    {
        $messageId = $value['id'] ?? $value['message_id'] ?? null;
        if (!$messageId) return;

        $messageData = [
            'message_id' => $messageId,
            'sender_id' => $value['sender']['id'] ?? $value['from']['id'] ?? null,
            'recipient_id' => $igUserId,
            'message_text' => $value['text'] ?? $value['message'] ?? '',
            'attachments' => isset($value['attachments']) ? json_encode($value['attachments']) : null,
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

        writeLog('Message from changes saved to DB via Controller', ['message_id' => $messageId]);
    }
}
