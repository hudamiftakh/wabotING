<!DOCTYPE html>
<html lang="id">

<head>
  <title>WabotING Meta API | Dashboard</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="handheldfriendly" content="true" />
  <meta name="MobileOptimized" content="width" />
  <meta name="description" content="WabotING Meta API - Instagram Integration Dashboard" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="shortcut icon" type="image/png" href="<?php echo base_url() ?>assets/wabot.png" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Libraries CSS -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>dist/css/style.min.css" />
  <link rel="stylesheet" href="<?php echo base_url('dist/material-design-iconic-font/css/material-design-iconic-font.css'); ?>" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>dist/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>dist/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
  <link href="<?= base_url('dist/font/css/font-awesome.css') ?>" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css" />

  <!-- Core Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="<?php echo base_url(); ?>dist/libs/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

  <style type="text/css">
    :root {
      --primary: #6b46c1;
      --primary-dark: #553c9a;
      --accent: #d53f8c;
      --bg-light: #f4f7f6;
      --white: #ffffff;
      --header-height: 70px;
      --sidebar-width: 270px;
    }

    body {
      font-family: 'Outfit', sans-serif;
      background-color: var(--bg-light);
      color: #2a3547;
      overflow-x: hidden;
    }

    /* --- MODERN PRELOADER --- */
    .preloader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 999999;
      background: #ffffff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .preloader.fade-out {
      opacity: 0;
      visibility: hidden;
      transform: scale(1.1);
    }

    .loader-box {
      text-align: center;
    }

    .loader-ring {
      width: 60px;
      height: 60px;
      border: 4px solid #f3f3f3;
      border-top: 4px solid var(--primary);
      border-radius: 50%;
      animation: spin 0.8s cubic-bezier(0.5, 0, 0.5, 1) infinite;
      margin: 0 auto 20px;
    }

    .loader-text {
      font-weight: 800;
      font-size: 26px;
      color: var(--primary);
      letter-spacing: -0.5px;
    }

    .loader-text span {
      color: var(--accent);
    }

    .loader-sub {
      font-size: 11px;
      color: #a1a1a1;
      text-transform: uppercase;
      letter-spacing: 3px;
      margin-top: 8px;
      font-weight: 600;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    /* --- HEADER STYLING --- */
    header.app-header,
    .app-header,
    .app-header.fixed-header,
    #main-wrapper .app-header,
    #main-wrapper[data-layout=vertical] .app-header {
      background: var(--primary) !important;
      height: var(--header-height) !important;
      display: flex;
      align-items: center;
      padding: 0 25px !important;
      box-shadow: none !important;
      transition: none !important;
      z-index: 1000;
      border: none !important;
    }

    .app-header .navbar,
    .app-header.fixed-header .navbar,
    #main-wrapper .app-header .navbar {
      padding: 0 !important;
      width: 100% !important;
      height: 100% !important;
      display: flex !important;
      align-items: center !important;
      background: transparent !important;
      margin-top: 0 !important;
      border-radius: 0 !important;
      box-shadow: none !important;
    }

    .brand-logo {
      background: var(--primary) !important;
      height: var(--header-height) !important;
      display: flex;
      align-items: center;
      padding: 0 25px !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .logo-text {
      font-weight: 800;
      color: var(--white) !important;
      font-size: 22px;
      margin-bottom: 0;
      letter-spacing: -0.5px;
      text-decoration: none !important;
    }

    .logo-text span {
      color: var(--accent);
    }

    .nav-link {
      padding: 10px !important;
      color: var(--white) !important;
      display: flex;
      align-items: center;
      border-radius: 10px;
      transition: all 0.2s ease;
    }

    .nav-link:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    /* --- SIDEBAR CUSTOMIZATION --- */
    .left-sidebar {
      box-shadow: 4px 0 25px rgba(0, 0, 0, 0.02) !important;
      border-right: 1px solid #eef2f6 !important;
    }

    /* --- USER PROFILE DROPDOWN --- */
    .user-profile-img img {
      border: 2px solid rgba(255, 255, 255, 0.3);
      padding: 2px;
      background: var(--white);
      transition: all 0.3s ease;
    }

    .nav-item.dropdown:hover .user-profile-img img {
      border-color: var(--accent);
      transform: scale(1.05);
    }

    .dropdown-menu {
      border: none;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      border-radius: 15px;
      padding: 10px;
      transform-origin: top right;
      animation: dropdownFade 0.3s ease;
    }

    @keyframes dropdownFade {
      from {
        opacity: 0;
        transform: translateY(10px) scale(0.95);
      }

      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    /* --- RESPONSIVE FIXES --- */
    @media (max-width: 991.98px) {
      .app-header {
        padding: 0 15px !important;
      }

      .user-info-text {
        display: none !important;
      }

      .brand-logo {
        width: 100% !important;
        justify-content: space-between;
      }
    }

    /* --- CONTENT WRAPPER --- */
    .container-fluid {
      padding-top: 30px !important;
      max-width: 1400px;
    }

    /* Alertify Customization */
    .alertify-notifier .ajs-message {
      background: rgba(255, 255, 255, 0.95) !important;
      color: #2a3547 !important;
      border-radius: 12px !important;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
      border: 1px solid #eef2f6 !important;
      backdrop-filter: blur(10px);
      padding: 15px 20px !important;
    }
  </style>
</head>

<body>
  <!-- Preloader Start -->
  <div class="preloader">
    <div class="loader-box">
      <div class="loader-ring"></div>
      <div class="loader-text">WabotING <span>META API</span></div>
      <div class="loader-sub">Instagram Integration</div>
    </div>
  </div>
  <!-- Preloader End -->

  <div class="page-wrapper mini-sidebar show-sidebar" id="main-wrapper" data-layout="vertical" data-navbarbg="skin1"
    data-card="border" data-boxed-layout="full" data-sidebartype="full" data-sidebar-position="fixed"
    data-header-position="fixed">

    <!-- Sidebar Start -->
    <aside class="left-sidebar">
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="<?= base_url(); ?>" class="text-nowrap logo-img">
            <h1 class="logo-text">WabotING <span>Meta</span></h1>
          </a>
          <div class="close-btn d-lg-none d-block sidebartoggler cursor-pointer text-white" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>
        <?php include 'master/menu.php'; ?>
      </div>
    </aside>
    <!-- Sidebar End -->

    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <header class="app-header">
        <nav class="navbar navbar-expand-lg">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link sidebartoggler ms-n3" id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2 fs-7"></i>
              </a>
            </li>
          </ul>

          <div class="ms-auto d-flex align-items-center">
            <?php
            $userData = $this->session->userdata('username');
            if (!$userData) $userData = ['picture' => '', 'name' => 'User', 'email' => '', 'given_name' => 'Guest'];
            ?>

            <div class="text-end me-3 d-none d-md-block user-info-text">
              <h6 class="mb-0 fw-bold text-white fs-3"><?= $userData['name']; ?></h6>
              <small class="text-white-50 fs-2" style="opacity: 0.8;"><?= $userData['email']; ?></small>
            </div>

            <ul class="navbar-nav flex-row align-items-center justify-content-center">
              <li class="nav-item dropdown">
                <a class="nav-link pe-0" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown" aria-expanded="false">
                  <div class="user-profile-img">
                    <img src="<?= $userData['picture']; ?>" class="rounded-circle" width="40" height="40"
                      onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($userData['name']); ?>&background=6b46c1&color=fff'">
                  </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                  <div class="message-body">
                    <div class="py-3 px-7 d-flex align-items-center border-bottom bg-light-subtle">
                      <img src="<?= $userData['picture']; ?>" class="rounded-circle border" width="45" height="45"
                        onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($userData['name']); ?>'">
                      <div class="ms-3">
                        <h5 class="mb-0 fs-3 fw-bold"><?= $userData['name']; ?></h5>
                        <span class="fs-2 text-muted"><?= $userData['email']; ?></span>
                      </div>
                    </div>
                    <div class="p-2">
                      <a href="<?= base_url() ?>privacy" class="dropdown-item py-2 px-3 rounded-2 d-flex align-items-center gap-3">
                        <i class="ti ti-shield-lock fs-6 text-primary"></i>
                        <div>
                          <h6 class="mb-0 fs-3 fw-semibold">Kebijakan Privasi</h6>
                          <span class="fs-2 text-muted">Halaman Privasi Data</span>
                        </div>
                      </a>
                      <div class="dropdown-divider my-2"></div>
                      <a href="<?= base_url(); ?>logout" class="btn btn-primary w-100 py-2 rounded-3" style="background: var(--primary); border: none;">Keluar</a>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->

      <div class="container-fluid" style="padding-top: calc(var(--header-height) + 20px) !important;">
        <?php $this->load->view($halaman, array('result' => $result, 'start' => $start)); ?>
      </div>
    </div>
  </div>

  <!-- Core Libraries JS -->
  <script src="<?php echo base_url(); ?>dist/libs/simplebar/dist/simplebar.min.js"></script>
  <script src="<?php echo base_url(); ?>dist/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?php echo base_url(); ?>dist/js/app.min.js"></script>
  <script src="<?php echo base_url(); ?>dist/js/app.minisidebar.init.js"></script>
  <script src="<?php echo base_url(); ?>dist/js/sidebarmenu.js"></script>
  <script src="<?php echo base_url(); ?>dist/js/custom.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // Handle Preloader Fade Out
    window.addEventListener('load', function() {
      const preloader = document.querySelector('.preloader');
      setTimeout(() => {
        preloader.classList.add('fade-out');
        setTimeout(() => {
          preloader.style.display = 'none';
        }, 500);
      });
    });
  </script>
</body>

</html>
