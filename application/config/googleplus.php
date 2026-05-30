<?php
$secrets_file = APPPATH . 'config/secrets.php';
$secrets = [];
if (file_exists($secrets_file)) {
	include($secrets_file);
}

$config['googleplus']['application_name'] = 'wabot';
$config['googleplus']['client_id']        = $secrets['google_client_id'] ?? '';
$config['googleplus']['client_secret']    = $secrets['google_client_secret'] ?? '';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$script_dir = dirname($script_name);
$script_dir = str_replace('\\', '/', $script_dir);
$base_path = ($script_dir === '/' || $script_dir === '.') ? '/' : rtrim($script_dir, '/') . '/';
$config['googleplus']['redirect_uri']     = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $base_path . "doLogin";
$config['googleplus']['api_key']          = '';
$config['googleplus']['scopes']           = array();

