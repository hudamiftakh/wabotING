<?php
defined('BASEPATH') or exit('No direct script access allowed');
$base_url = config_item('base_url');
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>404 - Halaman Tidak Ditemukan</title>
	<!-- Modern Typography -->
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
	<!-- Tabler Icons -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
	<style>
		:root {
			--primary: #5d87ff;
			--primary-light: #ecf1ff;
			--secondary: #49beff;
			--dark: #2a3547;
			--text-muted: #7c8fac;
			--bg-gradient: linear-gradient(135deg, #f5f7ff 0%, #ffffff 100%);
		}

		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: 'Plus Jakarta Sans', sans-serif;
			background: var(--bg-gradient);
			color: var(--dark);
			height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			overflow: hidden;
		}

		.container {
			text-align: center;
			padding: 2rem;
			max-width: 600px;
			width: 90%;
			animation: fadeIn 0.8s ease-out;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(20px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.error-mascot {
			width: 100%;
			max-width: 380px;
			height: auto;
			margin-bottom: -20px;
			filter: drop-shadow(0 20px 30px rgba(93, 135, 255, 0.2));
			animation: float 4s ease-in-out infinite;
		}

		@keyframes float {

			0%,
			100% {
				transform: translateY(0);
			}

			50% {
				transform: translateY(-15px);
			}
		}

		.error-code {
			font-size: 8rem;
			font-weight: 800;
			line-height: 1;
			margin-bottom: 0.5rem;
			background: linear-gradient(to right, #5d87ff, #49beff);
			-webkit-background-clip: text;
			-webkit-text-fill-color: transparent;
			letter-spacing: -4px;
		}

		h1 {
			font-size: 2rem;
			font-weight: 700;
			margin-bottom: 1rem;
			color: var(--dark);
		}

		p {
			color: var(--text-muted);
			font-size: 1.1rem;
			margin-bottom: 2.5rem;
			line-height: 1.6;
		}

		.btn-back {
			display: inline-flex;
			align-items: center;
			padding: 1rem 2.5rem;
			background: var(--primary);
			color: white;
			text-decoration: none;
			border-radius: 50px;
			font-weight: 600;
			font-size: 1rem;
			box-shadow: 0 10px 20px rgba(93, 135, 255, 0.3);
			transition: all 0.3s ease;
		}

		.btn-back i {
			margin-right: 8px;
			font-size: 1.2rem;
		}

		.btn-back:hover {
			background: #4570e6;
			transform: translateY(-3px);
			box-shadow: 0 15px 25px rgba(93, 135, 255, 0.4);
		}

		.bg-shapes {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: -1;
			overflow: hidden;
		}

		.shape {
			position: absolute;
			background: rgba(93, 135, 255, 0.05);
			border-radius: 50%;
		}

		.shape-1 {
			width: 400px;
			height: 400px;
			top: -100px;
			right: -100px;
		}

		.shape-2 {
			width: 300px;
			height: 300px;
			bottom: -50px;
			left: -50px;
			background: rgba(73, 190, 255, 0.05);
		}

		@media (max-width: 576px) {
			.error-code {
				font-size: 5rem;
			}

			h1 {
				font-size: 1.5rem;
			}

			p {
				font-size: 1rem;
			}
		}
	</style>
</head>

<body>
	<div class="bg-shapes">
		<div class="shape shape-1"></div>
		<div class="shape shape-2"></div>
	</div>

	<div class="container">
		<img src="<?= $base_url ?>dist/images/backgrounds/404_mascot.png" alt="404 Mascot" class="error-mascot">
		<div class="error-code">404</div>
		<h1>Ups! Halaman Hilang</h1>
		<p>Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin telah dipindahkan atau tautan yang Anda gunakan salah.</p>
		<a href="<?= $base_url ?>dashboard" class="btn-back">
			<i class="ti ti-home"></i> Kembali ke Dashboard
		</a>
	</div>
</body>

</html>