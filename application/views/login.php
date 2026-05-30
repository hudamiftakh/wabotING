<!DOCTYPE html>
<html lang="id">

<head>
  <title>Login | WabotING Meta API - Instagram Gateway Solution</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="WabotING Meta API - Professional Instagram API and Webhook Integration" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <link rel="shortcut icon" type="image/png" href="<?php echo base_url() ?>assets/wabot.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Core Css -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>dist/css/style.min.css" />

  <style>
    :root {
      --primary-color: #6b46c1;
      --secondary-color: #d53f8c;
      --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    body {
      font-family: 'Outfit', sans-serif;
      background: var(--bg-gradient);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      color: #334155;
    }

    .login-container {
      width: 100%;
      max-width: 1000px;
      margin: 20px;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 30px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .brand-side {
      background: linear-gradient(rgba(107, 70, 193, 0.85), rgba(213, 63, 140, 0.85)), url('<?php echo base_url(); ?>dist/images/backgrounds/login-security.svg');
      background-size: cover;
      background-position: center;
      color: white;
      padding: 60px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-side {
      padding: 60px;
    }

    .logo-box img {
      width: 80px;
      filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.1));
    }

    .step-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 25px;
    }

    .step-number {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      margin-right: 20px;
      flex-shrink: 0;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .btn-google {
      background: white;
      color: #374151;
      border: 2px solid #e5e7eb;
      border-radius: 16px;
      padding: 16px 24px;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      text-decoration: none;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .btn-google:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      border-color: var(--primary-color);
      color: var(--primary-color);
    }

    .btn-google img {
      width: 24px;
      margin-right: 12px;
    }

    .welcome-text h1 {
      font-size: 2.3rem;
      font-weight: 800;
      margin-bottom: 1rem;
      background: linear-gradient(90deg, #1e293b, var(--primary-color));
      background-clip: text;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .animate-up {
      animation: slideUp 0.8s ease-out forwards;
      opacity: 0;
    }

    @keyframes slideUp {
      from {
        transform: translateY(30px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    @media (max-width: 991.98px) {
      .brand-side {
        display: none;
      }

      .login-container {
        max-width: 500px;
      }

      .login-side {
        padding: 40px;
      }
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="row g-0">
      <!-- Left Branding Side -->
      <div class="col-lg-6 brand-side">
        <div class="animate-up" style="animation-delay: 0.1s">
          <h2 class="fw-bolder fs-9 mb-4 text-white">Integrasi Instagram & Webhook Premium</h2>
          <p class="fs-5 text-white-50 mb-9">Kelola komentar, direct message, dan monitoring log webhook API Meta dengan mudah dan real-time.</p>
        </div>

        <div class="features mt-4">
          <div class="step-item animate-up" style="animation-delay: 0.2s">
            <div class="step-number">1</div>
            <div>
              <h5 class="text-white fw-bold mb-1">Google OAuth</h5>
              <p class="text-white-50 small mb-0">Masuk aman menggunakan akun Google Anda dalam sekali klik.</p>
            </div>
          </div>
          <div class="step-item animate-up" style="animation-delay: 0.3s">
            <div class="step-number">2</div>
            <div>
              <h5 class="text-white fw-bold mb-1">Hubungkan Instagram</h5>
              <p class="text-white-50 small mb-0">Integrasikan akun Instagram Creator/Business melalui Facebook Login.</p>
            </div>
          </div>
          <div class="step-item animate-up" style="animation-delay: 0.4s">
            <div class="step-number">3</div>
            <div>
              <h5 class="text-white fw-bold mb-1">Monitor Real-time</h5>
              <p class="text-white-50 small mb-0">Pantau aktivitas webhook, salin token akses, dan manage data interaksi.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Login Side -->
      <div class="col-lg-6 login-side">
        <div class="text-center mb-9 animate-up" style="animation-delay: 0.1s">
          <div class="logo-box mb-4">
            <img src="<?php echo base_url() ?>assets/wabot.png" alt="WabotING Logo">
          </div>
          <div class="welcome-text">
            <h1>WabotING Meta API</h1>
          </div>
          <p class="text-muted fs-4">Masuk menggunakan akun Google Anda untuk melanjutkan ke dashboard.</p>
        </div>

        <div class="auth-box animate-up" style="animation-delay: 0.3s">
          <a class="btn-google" href="<?php echo $url_google; ?>">
            <img src="<?php echo base_url(); ?>assets/google-icon.svg" alt="Google">
            Sign in with Google
          </a>

          <div class="mt-9 p-4 bg-light rounded-4 border border-dashed text-center">
            <h6 class="fw-bold text-dark mb-2"><i class="ti ti-info-circle me-1 text-primary"></i> Login Otomatis</h6>
            <p class="small text-muted mb-0">Bagi pengguna baru, akun Anda akan <b>langsung terdaftar</b> secara otomatis setelah login Google pertama kali.</p>
          </div>
        </div>

        <div class="mt-9 text-center animate-up" style="animation-delay: 0.5s">
          <p class="small text-muted mb-2">© <?php echo date('Y'); ?> <b>WabotING</b>. All Rights Reserved.</p>
          <a href="<?php echo base_url(); ?>privacy" class="small text-decoration-none text-primary fw-bold">Kebijakan Privasi</a>
        </div>
      </div>
    </div>
  </div>

  <script src="<?php echo base_url(); ?>dist/libs/jquery/dist/jquery.min.js"></script>
  <script src="<?php echo base_url(); ?>dist/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>