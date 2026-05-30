<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['default_controller'] = 'auth';
$route['login'] = 'auth/index';
$route['logout'] = 'auth/logout';
$route['doLogin'] = 'auth/doLogin';
$route['dashboard'] = 'dashboard/index';
$route['privacy'] = 'privacy/index';
$route['webhook'] = 'webhook/index';
$route['webhook.php'] = 'webhook/index';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
