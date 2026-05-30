<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Privacy extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function index()
    {
        $data['halaman'] = 'privacy';
        $data['result'] = null;
        $data['start'] = 0;

        if (!empty($this->session->userdata['username'])) {
            $this->load->view('modul', $data);
        } else {
            $this->load->view('privacy_public', $data);
        }
    }
}
