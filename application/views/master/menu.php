<nav class="sidebar-nav scroll-sidebar" data-simplebar>
  <ul id="sidebarnav">
    <li class="nav-small-cap">
      <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
      <span class="hide-menu">Menu Utama</span>
    </li>
    
    <li class="sidebar-item">
      <a class="sidebar-link sidebar-link" href="<?php echo base_url('dashboard') ?>" aria-expanded="false">
        <span class="rounded-3">
          <i class="ti ti-layout-grid"></i>
        </span>
        <span class="hide-menu"> Dashboard</span>
      </a>
    </li>

    <li class="sidebar-item">
      <a class="sidebar-link sidebar-link" href="<?php echo base_url('privacy') ?>" aria-expanded="false">
        <span class="rounded-3">
          <i class="ti ti-shield-lock"></i>
        </span>
        <span class="hide-menu"> Kebijakan Privasi</span>
      </a>
    </li>

    <li class="sidebar-item">
      <a class="sidebar-link sidebar-link" href="<?php echo base_url('logout') ?>" aria-expanded="false">
        <span class="rounded-3">
          <i class="ti ti-logout"></i>
        </span>
        <span class="hide-menu"> Keluar</span>
      </a>
    </li>
  </ul>
  
  <div class="unlimited-access hide-menu bg-light-primary position-relative my-7 rounded">
    <div class="d-flex">
      <div class="unlimited-access-title">
        <h6 class="fw-semibold fs-3 mb-2 text-dark">WabotING Meta</h6>
        <p class="small text-muted mb-0">v1.0.0</p>
      </div>
      <div class="unlimited-access-img" style="max-width: 60px; margin-left: auto;">
        <img src="<?php echo base_url(); ?>assets/wabot.png" style="width: 100%" alt="Logo">
      </div>
    </div>
  </div>
</nav>
