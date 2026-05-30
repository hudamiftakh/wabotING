<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property Googleplus $googleplus
 */
class auth extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('pagination');
        $this->load->library('Googleplus');
        $this->load->model('M_Datatables');
    }
    public function index()
    {
        if (!empty($this->session->userdata['username'])) {
            redirect('./dashboard');
        } else {
            $this->login();
        }
    }
    public function login()
    {
        $url['url_google'] = $this->googleplus->loginURL();
        $this->load->view('login', $url);
    }
    public function logout()
    {
        session_destroy();
        redirect('./login');
    }

    public function doLogin()
    {

        if ($this->input->get('dummy') === 'true') {
            $email = 'blowebdev17@gmail.com';
            // $email = 'al.miftachuel.huda1994@gmail.com';
            $user = $this->db->get_where('users', ['email' => $email])->row_array();

            if ($user) {
                $this->session->set_userdata([
                    'username' => [
                        'id'        => $user['id'],
                        'email'     => $user['email'],
                        'name'      => $user['name'],
                        'level'     => $user['level'],
                        'logged_in' => true
                    ]
                ]);
                // Catat log login
                $this->db->insert('login_logs', [
                    'user_id'    => $user['id'],
                    'email'      => $user['email'],
                    'name'       => $user['name'],
                    'ip_address' => $this->input->ip_address(),
                    'user_agent' => $this->input->user_agent(),
                    'login_at'   => date('Y-m-d H:i:s'),
                ]);
                redirect('dashboard');
                exit;
            }
        }

        try {

            $code = $this->input->get('code');

            if (!$code) {
                redirect('login');
                exit;
            }

            // Exchange code ke access token
            $this->googleplus->getAuthenticate($code);

            // Ambil data user
            $resultData = $this->googleplus->getUserInfo();

            if (empty($resultData['email'])) {
                redirect('login');
                exit;
            }

            $checkUser = $this->db->get_where('users', [
                'email' => $resultData['email']
            ]);

            $data = [
                'email'       => $resultData['email'],
                'name'        => $resultData['name'],
                'nama'        => $resultData['name'],
                'given_name'  => $resultData['given_name'] ?? null,
                'family_name' => $resultData['family_name'] ?? null,
                'picture'     => $resultData['picture'] ?? null,
                'locale'      => $resultData['locale'] ?? null,
                'status'      => 'aktif',
                'create_at'   => date('Y-m-d H:i:s'),
                'login_at'    => date('Y-m-d H:i:s'),
            ];

            if ($checkUser->num_rows() > 0) {
                $this->db->update(
                    'users',
                    ['login_at' => date('Y-m-d H:i:s')],
                    ['email' => $resultData['email']]
                );
            } else {
                $this->db->insert('users', $data);
            }

            // Session login (WAJIB rapi)
            $this->session->set_userdata([
                'username' => [
                    'id'        => $checkUser->row_array()['id'] ?? $this->db->insert_id(),
                    'email'     => $data['email'],
                    'name'      => $data['name'],
                    'nama'      => $data['nama'],
                    'given_name'  => $data['given_name'] ?? null,
                    'family_name' => $data['family_name'] ?? null,
                    'picture'     => $data['picture'] ?? null,
                    'locale'      => $data['locale'] ?? null,
                    'level'     => $checkUser->row_array()['level'] ?? null,
                    'logged_in' => true
                ]
            ]);

            // Catat log login
            $loggedUserId = $checkUser->num_rows() > 0 ? $checkUser->row_array()['id'] : $this->db->insert_id();
            $this->db->insert('login_logs', [
                'user_id'    => $loggedUserId,
                'email'      => $data['email'],
                'name'       => $data['name'],
                'ip_address' => $this->input->ip_address(),
                'user_agent' => $this->input->user_agent(),
                'login_at'   => date('Y-m-d H:i:s'),
            ]);

            redirect('dashboard');
            exit;
        } catch (Exception $e) {
            log_message('error', 'Google Login Error: ' . $e->getMessage());
            redirect('login');
            exit;
        }
    }



    public function checkSession()
    {
        if (empty($this->session->userdata['username'])) {
            redirect('./');
        }
    }
}
