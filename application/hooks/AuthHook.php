<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthHook
{
    public function check_auth()
    {
        $CI = &get_instance();

        // Load necessary helpers/libraries if not already loaded
        if (!isset($CI->session)) {
            $CI->load->library('session');
        }
        $CI->load->helper('url');

        $directory = $CI->router->directory;
        $class = strtolower($CI->router->class);
        $method = strtolower($CI->router->method);

        // 1. Whitelist completely public controllers
        $public_controllers = ['auth', 'webhook', 'migration', 'migrate'];
        if (in_array($class, $public_controllers)) {
            return;
        }

        // 2. Handle API Controller specifically
        // API endpoints use API Key auth, while UI pages in API controller need Session.
        if ($class === 'api') {
            $public_api_methods = ['send_message', 'send_gambar', 'send_document', 'documentation', 'send_image', 'send_document'];
            if (in_array($method, $public_api_methods)) {
                return; // Let the controller handle API Key validation
            }
            // For other methods (index, apikey, generate_key, etc.), fall through to session check
        }

        if ($class === 'bot') {
            $public_bot_methods = ['receiver', 'register_api', 'logic'];
            if (in_array($method, $public_bot_methods)) {
                return;
            }
        }

        // 3. Fallback: Check for Admin/User Session
        // Note: 'username' is the session key used in Auth.php ($this->session->set_userdata(['username' => ...]))
        if (!$CI->session->userdata('username')) {
            // Handle AJAX requests gracefully
            if ($CI->input->is_ajax_request()) {
                // Returns 401 Unauthorized status with JSON
                $CI->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => 'error',
                        'message' => 'Session Expired',
                        'redirect' => base_url('login')
                    ]));
                $CI->output->_display();
                exit;
            }

            // Redirect standard requests to login
            redirect('login');
        }
    }
}
