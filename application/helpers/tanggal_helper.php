<?php
function useraAuthData()
{
	/** @var CI_Controller $ci */
	$ci = &get_instance();
	if (!empty($ci->session->userdata['username'])) {
		$sess = $ci->session->userdata['username'];
		// If id or level is missing (for older sessions), fetch from DB
		if (empty($sess['id']) || !isset($sess['level'])) {
			$user = $ci->db->get_where('users', ['email' => $sess['email']])->row_array();
			if ($user) {
				$sess['id'] = $user['id'];
				$sess['level'] = $user['level'];
				// Update session for subsequent calls
				$ci->session->set_userdata(['username' => $sess]);
			}
		}
		return $sess;
	}
}
function farmat_tanggal($waktu)
{
	if (empty($waktu)) {
		return '-';
	}
	$hari_array = array(
		'Minggu',
		'Senin',
		'Selasa',
		'Rabu',
		'Kamis',
		'Jumat',
		'Sabtu'
	);
	$hr = date('w', strtotime($waktu));
	$hari = $hari_array[$hr];
	$tanggal = date('j', strtotime($waktu));
	$bulan_array = array(
		1 => 'Januari',
		2 => 'Februari',
		3 => 'Maret',
		4 => 'April',
		5 => 'Mei',
		6 => 'Juni',
		7 => 'Juli',
		8 => 'Agustus',
		9 => 'September',
		10 => 'Oktober',
		11 => 'November',
		12 => 'Desember',
	);
	$bl = date('n', strtotime($waktu));
	$bulan = $bulan_array[$bl];
	$tahun = date('Y', strtotime($waktu));
	$jam = date('H:i:s', strtotime($waktu));

	//untuk menampilkan hari, tanggal bulan tahun jam
	//return "$hari, $tanggal $bulan $tahun $jam";

	//untuk menampilkan hari, tanggal bulan tahun
	return "$hari, $tanggal $bulan $tahun";
}

function hp($nohp)
{
	$nohp = str_replace([" ", "(", ")", ".", "-", "+"], "", (string)$nohp);
	$hp = trim($nohp);
	if (!empty($hp)) {
		if (substr($hp, 0, 2) == '62') {
			// already 62
		} elseif (substr($hp, 0, 1) == '0') {
			$hp = '62' . substr($hp, 1);
		} else {
			$hp = '62' . $hp;
		}
	}
	return preg_replace('/[^0-9]/', '', $hp);
}


function generateUniqueTransactionCode($prefix = 'PJM')
{
	$uniqueId = uniqid(); // Mendapatkan ID unik berdasarkan waktu saat ini
	$transactionCode = $prefix . strtoupper(substr(md5($uniqueId), 0, 8)); // Menggabungkan prefix dengan substring unik dari ID

	return $transactionCode;
}

function sendWa1($hp, $text)
{
	// $data = [
	// 	'api_key' => 'FMJ5rNIdm8tn3smAHsjgND3YDFD8T8',
	// 	'sender' => '6281330743343',
	// 	'number' => $hp,
	// 	'message' => $text
	// ];
	// $curl = curl_init();
	// curl_setopt_array($curl, array(
	// 	CURLOPT_URL => 'https://wa.digitalminsajo.sch.id/send-message',
	// 	CURLOPT_RETURNTRANSFER => true,
	// 	CURLOPT_ENCODING => '',
	// 	CURLOPT_MAXREDIRS => 10,
	// 	CURLOPT_TIMEOUT => 0,
	// 	CURLOPT_FOLLOWLOCATION => true,
	// 	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	// 	CURLOPT_CUSTOMREQUEST => 'POST',
	// 	CURLOPT_POSTFIELDS => json_encode($data),
	// 	CURLOPT_HTTPHEADER => array(
	// 		'Content-Type: application/json'
	// 	),
	// ));
	// $response = curl_exec($curl);
	// curl_close($curl);
	// $djson = json_decode($response,true);
	// if($djson["status"]){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://wa2.digitalminsajo.sch.id/send-message");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "session=wa1&to=" . $hp . "&text=" . $text . "");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	$djson = json_decode($response, true);
	if ($djson['data']['status']) {
		// echo $response;
	} else {
		$data = [
			'api_key' => 'efacb2a793deade57af9fb2fd3f79b91911c5324',
			'sender' => '6281330743343',
			'number' => $hp,
			'message' => $text
		];
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL => 'https://wa.srv2.wapanels.com/send-message',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => json_encode($data),
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json'
				),
			)
		);
		$response = curl_exec($curl);
		curl_close($curl);
		$djson2 = json_decode($response, true);
		if ($djson2["status"]) {
			// echo $response;
		} else {
			$data = [
				'api_key' => 'nWlmCfhaK9SoajlsuEzT02riifcAfMeg',
				'sender' => '6281330743343',
				'number' => $hp,
				'message' => $text
			];
			$curl = curl_init();
			curl_setopt_array(
				$curl,
				array(
					CURLOPT_URL => 'https://wa.minsajo.waserv.my.id/send-message',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS => json_encode($data),
					CURLOPT_HTTPHEADER => array(
						'Content-Type: application/json'
					),
				)
			);
			$response = curl_exec($curl);
			curl_close($curl);
			$djson3 = json_decode($response, true);
			if ($djson3["status"]) {
				// echo $response;
			} else {
			}
		}
	}
}


function sendWa2($hp, $text)
{
	$data = array(
		'chatId' => $hp . '@c.us',
		'message' => $text,
	);
	$options = array(
		'http' => array(
			'method' => 'POST',
			'content' => json_encode($data),
			'header' => "Content-Type: application/json\r\n" .
				"Accept: application/json\r\n"
		)
	);
	$url = "https://dhsend.com/waInstance1101001027/sendMessage/17f5c57d96922c2e6cdd7190e4aa7918682919ab5024a7c6 ";
	$context = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
}

function kode_transaksi_tabungan()
{
	$prefix = 'TRX'; // Awalan kode transaksi
	$suffix = 'TB'; // Akhiran kode transaksi
	$randomNumber = mt_rand(10000, 99999); // Angka acak antara 10000 dan 99999
	$transactionCode = $prefix . $randomNumber; // Gabungkan awalan, angka acak, dan akhiran
	return $transactionCode;
}

function url_wa()
{
	// return 'https://wa-api-v2.wabot.web.id/';
	return 'https://wa-api-v1.wabot.web.id/';
}


function generateApiKey($length = 32)
{
	// Karakter yang diizinkan dalam API key
	$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charsLength = strlen($chars);
	$apiKey = '';
	// Generate API key secara acak
	for ($i = 0; $i < $length; $i++) {
		$apiKey .= $chars[rand(0, $charsLength - 1)];
	}
	return $apiKey;
}

function generateSecretKey($length = 32)
{
	// Karakter yang diizinkan dalam secret key
	$chars = '0123456789abcdefghijklmnopqrstuvwxyz';
	$charsLength = strlen($chars);
	$secretKey = '';
	// Generate secret key secara acak
	for ($i = 0; $i < $length; $i++) {
		$secretKey .= $chars[rand(0, $charsLength - 1)];
	}
	return $secretKey;
}

/**
 * Helper: Panggil Instagram/Facebook Graph API
 */
function callGraphAPI($url, $method = 'GET', $data = [])
{
    $ch = curl_init();

    if ($method === 'GET' && !empty($data)) {
        $url .= '?' . http_build_query($data);
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => ['message' => 'cURL Error: ' . $error]];
    }

    $decoded = json_decode($response, true);
    return $decoded ?: ['error' => ['message' => 'Invalid JSON response', 'raw' => $response]];
}

/**
 * Helper: Log ke file untuk debugging
 */
function writeLog($message, $data = null)
{
    $logFile = APPPATH . 'logs/app_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $log = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($data !== null) {
        $log .= "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    $log .= "\n---\n";

    file_put_contents($logFile, $log, FILE_APPEND);
}

/**
 * Helper: Auto-create required database tables if they do not exist
 */
function check_and_create_db_tables()
{
    $ci = &get_instance();
    if (!isset($ci->db)) {
        return;
    }

    // 1. Table: users
    if (!$ci->db->table_exists('users')) {
        $ci->db->query("CREATE TABLE `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(255) NOT NULL,
            `name` VARCHAR(255) DEFAULT NULL,
            `nama` VARCHAR(255) DEFAULT NULL,
            `given_name` VARCHAR(255) DEFAULT NULL,
            `family_name` VARCHAR(255) DEFAULT NULL,
            `picture` VARCHAR(255) DEFAULT NULL,
            `locale` VARCHAR(50) DEFAULT NULL,
            `status` VARCHAR(50) DEFAULT 'aktif',
            `level` VARCHAR(50) DEFAULT 'user',
            `create_at` DATETIME DEFAULT NULL,
            `login_at` DATETIME DEFAULT NULL,
            UNIQUE KEY `uk_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    // Insert default user if not exists
    $email_default = 'blowebdev17@gmail.com';
    $check_default = $ci->db->get_where('users', ['email' => $email_default])->num_rows();
    if ($check_default == 0) {
        $ci->db->insert('users', [
            'email' => $email_default,
            'name' => 'Huda Miftakh',
            'nama' => 'Huda Miftakh',
            'status' => 'aktif',
            'level' => 'admin',
            'create_at' => date('Y-m-d H:i:s'),
            'login_at' => date('Y-m-d H:i:s')
        ]);
    }

    // 2. Table: login_logs
    if (!$ci->db->table_exists('login_logs')) {
        $ci->db->query("CREATE TABLE `login_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT DEFAULT NULL,
            `email` VARCHAR(255) DEFAULT NULL,
            `name` VARCHAR(255) DEFAULT NULL,
            `ip_address` VARCHAR(50) DEFAULT NULL,
            `user_agent` TEXT DEFAULT NULL,
            `login_at` DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    // 3. Table: access_tokens
    if (!$ci->db->table_exists('access_tokens')) {
        $ci->db->query("CREATE TABLE `access_tokens` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_email` VARCHAR(255) DEFAULT NULL,
            `ig_user_id` VARCHAR(100) NOT NULL,
            `username` VARCHAR(255) DEFAULT NULL,
            `name` VARCHAR(255) DEFAULT NULL,
            `profile_picture_url` TEXT DEFAULT NULL,
            `followers_count` INT DEFAULT 0,
            `media_count` INT DEFAULT 0,
            `access_token` TEXT NOT NULL,
            `token_type` VARCHAR(50) DEFAULT 'bearer',
            `expires_at` DATETIME DEFAULT NULL,
            `page_id` VARCHAR(100) DEFAULT NULL,
            `page_access_token` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_ig_user_id` (`ig_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } else {
        // Ensure user_email column exists if table is already created
        if (!$ci->db->field_exists('user_email', 'access_tokens')) {
            $ci->db->query("ALTER TABLE `access_tokens` ADD `user_email` VARCHAR(255) DEFAULT NULL AFTER `id`;");
        }
        if (!$ci->db->field_exists('profile_picture_url', 'access_tokens')) {
            $ci->db->query("ALTER TABLE `access_tokens` ADD `profile_picture_url` TEXT DEFAULT NULL AFTER `name`;");
        }
        if (!$ci->db->field_exists('followers_count', 'access_tokens')) {
            $ci->db->query("ALTER TABLE `access_tokens` ADD `followers_count` INT DEFAULT 0 AFTER `profile_picture_url`;");
        }
        if (!$ci->db->field_exists('media_count', 'access_tokens')) {
            $ci->db->query("ALTER TABLE `access_tokens` ADD `media_count` INT DEFAULT 0 AFTER `followers_count`;");
        }
    }

    // 4. Table: webhook_logs
    if (!$ci->db->table_exists('webhook_logs')) {
        $ci->db->query("CREATE TABLE `webhook_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `object` VARCHAR(100) DEFAULT NULL,
            `entry_id` VARCHAR(100) DEFAULT NULL,
            `event_type` VARCHAR(100) DEFAULT NULL,
            `field` VARCHAR(100) DEFAULT NULL,
            `value` JSON DEFAULT NULL,
            `raw_payload` JSON DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    // 5. Table: comments
    if (!$ci->db->table_exists('comments')) {
        $ci->db->query("CREATE TABLE `comments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `comment_id` VARCHAR(100) NOT NULL,
            `ig_user_id` VARCHAR(100) DEFAULT NULL,
            `media_id` VARCHAR(100) DEFAULT NULL,
            `parent_id` VARCHAR(100) DEFAULT NULL,
            `from_id` VARCHAR(100) DEFAULT NULL,
            `from_username` VARCHAR(255) DEFAULT NULL,
            `text` TEXT DEFAULT NULL,
            `like_count` INT DEFAULT 0,
            `timestamp` DATETIME DEFAULT NULL,
            `is_from_webhook` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_comment_id` (`comment_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } else {
        if (!$ci->db->field_exists('ig_user_id', 'comments')) {
            $ci->db->query("ALTER TABLE `comments` ADD `ig_user_id` VARCHAR(100) DEFAULT NULL AFTER `comment_id`;");
        }
        if (!$ci->db->field_exists('like_count', 'comments')) {
            $ci->db->query("ALTER TABLE `comments` ADD `like_count` INT DEFAULT 0 AFTER `text`;");
        }
    }

    // 6. Table: media
    if (!$ci->db->table_exists('media')) {
        $ci->db->query("CREATE TABLE `media` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `media_id` VARCHAR(100) NOT NULL,
            `ig_user_id` VARCHAR(100) DEFAULT NULL,
            `media_type` VARCHAR(50) DEFAULT NULL,
            `media_url` TEXT DEFAULT NULL,
            `permalink` TEXT DEFAULT NULL,
            `caption` TEXT DEFAULT NULL,
            `timestamp` DATETIME DEFAULT NULL,
            `like_count` INT DEFAULT 0,
            `comments_count` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_media_id` (`media_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    // 7. Table: messages
    if (!$ci->db->table_exists('messages')) {
        $ci->db->query("CREATE TABLE `messages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `message_id` VARCHAR(100) NOT NULL,
            `ig_user_id` VARCHAR(100) DEFAULT NULL,
            `sender_id` VARCHAR(100) DEFAULT NULL,
            `recipient_id` VARCHAR(100) DEFAULT NULL,
            `message_text` TEXT DEFAULT NULL,
            `attachments` JSON DEFAULT NULL,
            `timestamp` DATETIME DEFAULT NULL,
            `is_from_webhook` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_message_id` (`message_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } else {
        if (!$ci->db->field_exists('ig_user_id', 'messages')) {
            $ci->db->query("ALTER TABLE `messages` ADD `ig_user_id` VARCHAR(100) DEFAULT NULL AFTER `message_id`;");
        }
        $ci->db->query("UPDATE `messages` m
            JOIN `access_tokens` a ON a.ig_user_id = m.sender_id OR a.ig_user_id = m.recipient_id
            SET m.ig_user_id = a.ig_user_id
            WHERE m.ig_user_id IS NULL;");
    }

    // 8. Table: reply_templates
    if (!$ci->db->table_exists('reply_templates')) {
        $ci->db->query("CREATE TABLE `reply_templates` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_email` VARCHAR(255) DEFAULT NULL,
            `ig_user_id` VARCHAR(100) DEFAULT NULL,
            `name` VARCHAR(120) NOT NULL,
            `channel` VARCHAR(20) DEFAULT 'all',
            `keyword` VARCHAR(120) DEFAULT NULL,
            `response_text` TEXT NOT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `auto_reply` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } else {
        $replyTemplateColumns = [
            'user_email' => "ALTER TABLE `reply_templates` ADD `user_email` VARCHAR(255) DEFAULT NULL AFTER `id`;",
            'ig_user_id' => "ALTER TABLE `reply_templates` ADD `ig_user_id` VARCHAR(100) DEFAULT NULL AFTER `user_email`;",
            'channel' => "ALTER TABLE `reply_templates` ADD `channel` VARCHAR(20) DEFAULT 'all' AFTER `name`;",
            'keyword' => "ALTER TABLE `reply_templates` ADD `keyword` VARCHAR(120) DEFAULT NULL AFTER `channel`;",
            'is_active' => "ALTER TABLE `reply_templates` ADD `is_active` TINYINT(1) DEFAULT 1 AFTER `response_text`;",
            'auto_reply' => "ALTER TABLE `reply_templates` ADD `auto_reply` TINYINT(1) DEFAULT 0 AFTER `is_active`;",
        ];
        foreach ($replyTemplateColumns as $column => $query) {
            if (!$ci->db->field_exists($column, 'reply_templates')) {
                $ci->db->query($query);
            }
        }
    }

    // 9. Table: auto_reply_logs
    if (!$ci->db->table_exists('auto_reply_logs')) {
        $ci->db->query("CREATE TABLE `auto_reply_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `template_id` INT DEFAULT NULL,
            `ig_user_id` VARCHAR(100) DEFAULT NULL,
            `channel` VARCHAR(20) DEFAULT NULL,
            `target_id` VARCHAR(100) DEFAULT NULL,
            `request_payload` JSON DEFAULT NULL,
            `response_payload` JSON DEFAULT NULL,
            `status` VARCHAR(30) DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    } else {
        $autoReplyLogColumns = [
            'template_id' => "ALTER TABLE `auto_reply_logs` ADD `template_id` INT DEFAULT NULL AFTER `id`;",
            'ig_user_id' => "ALTER TABLE `auto_reply_logs` ADD `ig_user_id` VARCHAR(100) DEFAULT NULL AFTER `template_id`;",
            'channel' => "ALTER TABLE `auto_reply_logs` ADD `channel` VARCHAR(20) DEFAULT NULL AFTER `ig_user_id`;",
            'target_id' => "ALTER TABLE `auto_reply_logs` ADD `target_id` VARCHAR(100) DEFAULT NULL AFTER `channel`;",
            'request_payload' => "ALTER TABLE `auto_reply_logs` ADD `request_payload` JSON DEFAULT NULL AFTER `target_id`;",
            'response_payload' => "ALTER TABLE `auto_reply_logs` ADD `response_payload` JSON DEFAULT NULL AFTER `request_payload`;",
            'status' => "ALTER TABLE `auto_reply_logs` ADD `status` VARCHAR(30) DEFAULT 'pending' AFTER `response_payload`;",
        ];
        foreach ($autoReplyLogColumns as $column => $query) {
            if (!$ci->db->field_exists($column, 'auto_reply_logs')) {
                $ci->db->query($query);
            }
        }
    }
}

