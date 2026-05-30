<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Secrets & API Credentials (Template)
|--------------------------------------------------------------------------
|
| Copy this file to 'secrets.php' and fill in your actual credentials.
| Do NOT commit 'secrets.php' to Git.
|
*/

// Local Database Configuration
$secrets['db_hostname'] = 'localhost';
$secrets['db_username'] = 'root';
$secrets['db_password'] = '';
$secrets['db_database'] = 'instagram_api';

// Google OAuth Credentials
$secrets['google_client_id']     = 'YOUR_GOOGLE_CLIENT_ID';
$secrets['google_client_secret'] = 'YOUR_GOOGLE_CLIENT_SECRET';

// Meta / Instagram App Credentials
$secrets['ig_app_id']            = 'YOUR_META_APP_ID';
$secrets['ig_app_secret']        = 'YOUR_META_APP_SECRET';
