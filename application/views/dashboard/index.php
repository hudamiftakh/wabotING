<!-- CSS Custom for Premium Dashboard UI/UX -->
<style>
    :root {
        --ig-gradient: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
        --ig-pink: #d62976;
        --ig-purple: #962fbf;
        --ig-blue: #4f5bd5;
        --ig-orange: #fa7e1e;
        --ig-yellow: #fcaf45;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.4);
        --glow-shadow: 0 10px 30px rgba(138, 58, 185, 0.12);
        --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.03);
    }

    /* ---- PANEL TRANSITION ANIMATION ---- */
    .panel-transition {
        transition: opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1), transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        opacity: 1;
        transform: translateY(0) scale(1);
    }

    .panel-hidden {
        opacity: 0;
        transform: translateY(20px) scale(0.98);
        pointer-events: none;
        height: 0;
        overflow: hidden;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* ---- ACCOUNTS GRID PREMIUM CARD ---- */
    .premium-account-card {
        border: none;
        border-radius: 24px;
        background: #ffffff;
        box-shadow: var(--card-shadow);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        overflow: hidden;
        position: relative;
        border: 1px solid #f1f5f9;
    }

    .premium-account-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(138, 58, 185, 0.1), 0 4px 12px rgba(0, 0, 0, 0.02);
        border-color: rgba(138, 58, 185, 0.2);
    }

    /* ---- INSTAGRAM STORY AVATAR RING ---- */
    .ig-avatar-ring {
        position: relative;
        padding: 3px;
        background: var(--ig-gradient);
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 4px 12px rgba(230, 104, 60, 0.15);
    }

    .ig-avatar-inner {
        background: #ffffff;
        padding: 3px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ig-avatar-img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
    }

    .ig-avatar-letter {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #ffffff;
        font-size: 1.4rem;
        background: linear-gradient(135deg, var(--ig-purple), var(--ig-pink));
    }

    /* ---- BUTTON GRADIENT ---- */
    .btn-gradient-instagram {
        background: var(--ig-gradient);
        color: #ffffff !important;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(220, 39, 67, 0.2);
    }

    .btn-gradient-instagram:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 39, 67, 0.35);
        opacity: 0.95;
    }

    /* ---- STATS CARD PREMIUM ---- */
    .stats-card-premium {
        border: none;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }

    .stats-card-premium:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08) !important;
    }

    .stats-card-premium .stat-bg-icon {
        position: absolute;
        right: -10px;
        bottom: -20px;
        font-size: 7.5rem;
        opacity: 0.04;
        pointer-events: none;
        transition: all 0.3s ease;
    }

    .stats-card-premium:hover .stat-bg-icon {
        transform: scale(1.1) rotate(-10deg);
        opacity: 0.08;
    }

    /* ---- PREMIUM TAB SYSTEM ---- */
    .tab-btn-premium {
        border: none;
        background: transparent;
        padding: 14px 28px;
        font-weight: 600;
        font-size: 0.95rem;
        color: #64748b;
        position: relative;
        transition: all 0.3s ease;
        border-radius: 12px 12px 0 0;
    }

    .tab-btn-premium:hover {
        color: var(--primary);
        background: rgba(107, 70, 193, 0.04);
    }

    .tab-btn-premium.active {
        color: var(--primary);
        font-weight: 700;
    }

    .tab-btn-premium::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 3px;
        background: var(--ig-gradient);
        transition: all 0.3s ease;
        transform: translateX(-50%);
        border-radius: 3px;
    }

    .tab-btn-premium.active::after {
        width: 70%;
    }

    /* ---- COMMENTS PREMIUM FEED ---- */
    .comment-feed-item {
        display: flex;
        align-items: flex-start;
        padding: 18px;
        border-radius: 20px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        margin-bottom: 15px;
        transition: all 0.2s ease;
    }

    .comment-feed-item:hover {
        background: #ffffff;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03);
        border-color: rgba(107, 70, 193, 0.15);
    }

    .comment-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #cbd5e1, #94a3b8);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.95rem;
        color: #ffffff;
        margin-right: 15px;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .comment-bubble {
        flex-grow: 1;
    }

    .comment-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    /* ---- DIRECT MESSAGES PREMIUM CHAT ---- */
    .chat-bubble-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 24px;
        max-height: 500px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
    }

    .chat-bubble {
        max-width: 75%;
        padding: 14px 18px;
        border-radius: 20px;
        font-size: 0.92rem;
        line-height: 1.45;
        position: relative;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .chat-bubble.inbound {
        align-self: flex-start;
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 4px;
    }

    .chat-bubble.outbound {
        align-self: flex-end;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: #ffffff;
        border-bottom-right-radius: 4px;
    }

    .chat-time {
        font-size: 0.7rem;
        margin-top: 6px;
        opacity: 0.7;
        text-align: right;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
    }

    .chat-bubble.inbound .chat-time {
        color: #64748b;
    }

    .chat-bubble.outbound .chat-time {
        color: #e9d5ff;
    }

    /* ---- WEBHOOK EVENTS SLATE DISPLAY ---- */
    .webhook-card-event {
        background: #0f172a;
        color: #38bdf8;
        border-radius: 12px;
        border: 1px solid #1e293b;
        font-family: 'Consolas', 'Courier New', Courier, monospace;
        font-size: 0.82rem;
        padding: 12px;
        max-height: 150px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-break: break-all;
    }

    /* ---- MEDIA FEED GALLERY GRID ---- */
    .media-grid-card {
        border-radius: 20px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: var(--card-shadow);
        border: 1px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .media-grid-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
    }

    .media-card-img-wrapper {
        position: relative;
        background: #f1f5f9;
        height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .media-card-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .media-card-img-wrapper video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        background: #000;
    }

    .media-grid-card:hover .media-card-img-wrapper img {
        transform: scale(1.05);
    }

    .media-card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.55);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        opacity: 0;
        transition: all 0.3s ease;
        color: #ffffff;
        font-weight: 700;
        font-size: 1.1rem;
        backdrop-filter: blur(2px);
    }

    .media-grid-card:hover .media-card-overlay {
        opacity: 1;
    }

    .live-dot {
        width: 10px;
        height: 10px;
        background-color: #22c55e;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
        }
    }

    .sentiment-metric {
        border: 1px solid #eef2f6;
        border-radius: 14px;
        padding: 16px;
        background: #ffffff;
    }

    .sentiment-list {
        max-height: 330px;
        overflow-y: auto;
    }

    .sentiment-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .instagram-actions {
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: 1.25rem;
        color: #111827;
    }

    .media-comments-panel {
        border-top: 1px solid #eef2f6;
        background: #ffffff;
        max-height: 340px;
        overflow-y: auto;
    }

    .media-comment-item {
        display: flex;
        gap: 10px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .media-comment-item.reply {
        margin-left: 34px;
        padding-top: 8px;
    }

    .media-comment-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--ig-purple), var(--ig-pink));
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 800;
        flex-shrink: 0;
    }
</style>

<div class="dashboard-container">
    <!-- ========================================================================= -->
    <!-- MAIN PANEL: ACCOUNTS LIST (LANDING PAGE) -->
    <!-- ========================================================================= -->
    <div id="mainAccountsPanel" class="panel-transition">
        <!-- HEADER WELCOME HERO BANNER -->
        <div class="card border-0 overflow-hidden shadow-sm rounded-4 mb-4 text-white" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 50%, var(--accent) 100%);">
            <div class="card-body p-4 p-md-5 position-relative">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge bg-white text-primary px-3 py-1.5 rounded-pill fw-bold mb-3 small" style="box-shadow: 0 4px 10px rgba(0,0,0,0.1);">📸 Instagram Meta API</span>
                        <h1 class="fw-extrabold text-white mb-2" style="font-size: 2.2rem; letter-spacing: -1px;">Instagram Integration Hub</h1>
                        <p class="text-white-50 mb-0 fs-4" style="max-width: 600px;">Hubungkan akun bisnis atau creator Instagram Anda, sinkronisasikan pesan DM secara instan, dan pantau log webhook real-time dengan dasbor interaktif.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                        <a href="<?php echo base_url('dashboard/instagram_login'); ?>" class="btn btn-light py-3 px-4 rounded-3 fw-bold text-primary shadow-sm hover-scale transition-all" style="border: none;">
                            <i class="ti ti-link me-1 text-primary"></i> Hubungkan Instagram Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACCOUNTS GRID -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0 text-dark"><i class="ti ti-users me-1 text-primary"></i> Daftar Akun Instagram Terhubung</h4>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold"><?= count($accounts) ?> Terkoneksi</span>
        </div>
        
        <div class="row g-4 mb-5">
            <?php if (empty($accounts)): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
                        <div class="card-body">
                            <div class="mb-3" style="font-size: 4rem;">🔗</div>
                            <h5 class="fw-bold text-dark">Belum Ada Akun Instagram Terhubung</h5>
                            <p class="text-muted mb-4" style="max-width: 450px; margin: 0 auto;">Silakan hubungkan akun bisnis atau creator Instagram Anda terlebih dahulu untuk memulai integrasi webhook.</p>
                            <a href="<?php echo base_url('dashboard/instagram_login'); ?>" class="btn btn-primary py-2.5 px-4 rounded-3 fw-bold btn-gradient-instagram">Hubungkan Sekarang</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($accounts as $acc): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card premium-account-card h-100">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Profil Header & Avatar Ring -->
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="ig-avatar-ring">
                                            <div class="ig-avatar-inner">
                                                <?php if (!empty($acc['profile_picture_url'])): ?>
                                                    <img src="<?= htmlspecialchars($acc['profile_picture_url']) ?>" class="ig-avatar-img" onerror="this.style.display='none'; $(this).next().show();">
                                                    <div class="ig-avatar-letter" style="display:none;"><?= strtoupper(substr($acc['username'] ?? 'I', 0, 1)) ?></div>
                                                <?php else: ?>
                                                    <div class="ig-avatar-letter"><?= strtoupper(substr($acc['username'] ?? 'I', 0, 1)) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="ms-3">
                                            <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-0.5 mb-1 small fw-bold" style="font-size: 0.7rem;"><i class="ti ti-circle-check-filled me-1"></i> Aktif</span>
                                            <h5 class="fw-bold mb-0 text-dark">@<?= htmlspecialchars($acc['username'] ?? 'N/A') ?></h5>
                                            <span class="small text-muted" style="font-size: 0.8rem;"><?= htmlspecialchars($acc['name'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Mini Stats Counters -->
                                    <div class="my-4">
                                        <div class="row g-2 text-center bg-light p-2.5 rounded-3 border">
                                            <div class="col-6 border-end">
                                                <span class="small text-muted d-block" style="font-size: 0.72rem; font-weight: 500;">Followers</span>
                                                <strong class="text-dark fs-5 fw-extrabold"><?= number_format($acc['followers_count'] ?? 0) ?></strong>
                                            </div>
                                            <div class="col-6">
                                                <span class="small text-muted d-block" style="font-size: 0.72rem; font-weight: 500;">Postingan</span>
                                                <strong class="text-dark fs-5 fw-extrabold"><?= number_format($acc['media_count'] ?? 0) ?></strong>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Account Meta ID -->
                                    <div class="mb-4">
                                        <span class="small text-muted d-block mb-1" style="font-size: 0.75rem;">Instagram Account ID:</span>
                                        <code class="small text-primary bg-light-primary px-2.5 py-1.5 rounded d-block text-truncate border border-primary-subtle" style="font-size: 0.82rem;"><?= htmlspecialchars($acc['ig_user_id']) ?></code>
                                    </div>
                                    <?php
                                        $daysLeft = !empty($acc['expires_at']) ? floor((strtotime($acc['expires_at']) - time()) / 86400) : null;
                                        $tokenBadge = $daysLeft === null ? ['bg-secondary-subtle text-secondary', 'Token: tidak diketahui'] : ($daysLeft < 0 ? ['bg-danger-subtle text-danger', 'Token expired'] : ($daysLeft <= 7 ? ['bg-warning-subtle text-warning', 'Token hampir expired (' . $daysLeft . ' hari)'] : ['bg-success-subtle text-success', 'Token aman (' . $daysLeft . ' hari)']));
                                    ?>
                                    <div class="mb-3">
                                        <span class="badge <?= $tokenBadge[0] ?> rounded-pill px-2.5 py-1 fw-bold"><?= htmlspecialchars($tokenBadge[1]) ?></span>
                                    </div>
                                </div>
                                
                                <!-- Card Actions -->
                                <div>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-gradient-instagram py-2.5 rounded-3 fw-bold" onclick='openAccountDashboard(<?= htmlspecialchars(json_encode($acc['ig_user_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($acc['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($acc['profile_picture_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)'>
                                            Buka Dashboard Akun 🚀
                                        </button>
                                        <div class="d-flex justify-content-between mt-1 gap-2">
                                            <button class="btn btn-sm btn-outline-secondary flex-grow-1 py-2 fw-bold" onclick='copyToken(<?= htmlspecialchars(json_encode($acc['access_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($acc['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)' style="border-radius: 10px;">
                                                <i class="ti ti-copy me-1"></i> Salin Token
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger flex-grow-1 py-2 fw-bold" onclick='deleteAccount(<?= htmlspecialchars(json_encode($acc['ig_user_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($acc['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)' style="border-radius: 10px;">
                                                <i class="ti ti-trash me-1"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- PANDUAN SETUP ACCORDION -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <span class="fs-4 me-2">📖</span>
                    <h4 class="fw-bold mb-0 text-dark">Panduan Setup Webhook & Instagram API</h4>
                </div>
                
                <div class="alert alert-primary border-dashed p-4 rounded-4 mb-4" style="background: rgba(107, 70, 193, 0.04); border: 1px dashed rgba(107, 70, 193, 0.3);">
                    <h6 class="fw-bold mb-2 text-primary"><i class="ti ti-info-circle me-1"></i> Syarat Utama Integrasi:</h6>
                    <ol class="mb-0 fs-3 text-dark-50" style="padding-left: 20px;">
                        <li class="mb-1">Akun Instagram Anda harus diubah menjadi tipe <strong>Professional</strong> (Creator atau Business).</li>
                        <li class="mb-1">Akun Instagram tersebut wajib dihubungkan dengan salah satu <strong>Halaman Facebook (Facebook Page)</strong> yang Anda kelola.</li>
                        <li>Daftarkan aplikasi Anda di portal pengembang <strong>Meta Developers</strong> untuk mendapatkan App ID & Secret.</li>
                    </ol>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light-subtle h-100">
                            <h5 class="fw-bold text-dark mb-2"><i class="ti ti-apps me-1 text-primary"></i> Step 1: Konfigurasi Aplikasi Meta</h5>
                            <p class="text-muted small mb-0">Buat aplikasi dengan tipe <strong>Business</strong> di Meta Developers. Aktifkan produk <strong>Instagram Graph API</strong> serta produk <strong>Webhooks</strong>, lalu hubungkan halaman Facebook Anda.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light-subtle h-100">
                            <h5 class="fw-bold text-dark mb-2"><i class="ti ti-webhook me-1 text-accent"></i> Step 2: Hubungkan Webhook Endpoint</h5>
                            <p class="text-muted small mb-3">Masukkan parameter konfigurasi Callback URL berikut pada Meta Developers Portal Anda:</p>
                            <div class="p-3 rounded-3 text-white small" style="background: #1e293b; font-family: 'Consolas', monospace; line-height: 1.6;">
                                <strong class="text-info">Callback URL:</strong> <?= base_url('webhook') ?><br>
                                <strong class="text-info">Verify Token:</strong> <?= WEBHOOK_VERIFY_TOKEN ?><br>
                                <strong class="text-info">Object:</strong> instagram<br>
                                <strong class="text-info">Fields:</strong> comments, messages, live_comments
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- ACCOUNT DETAIL DASHBOARD PANEL -->
    <!-- ========================================================================= -->
    <div id="accountDashboardPanel" class="panel-transition panel-hidden" style="display: none;">
        <!-- HEADER DETAIL CARD -->
        <div class="card border-0 bg-white shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-secondary me-3 rounded-circle d-flex justify-content-center align-items-center" onclick="closeAccountDashboard()" style="width: 44px; height: 44px; transition: all 0.2s ease;">
                        <i class="ti ti-arrow-left fs-5"></i>
                    </button>
                    <!-- Avatar Ring in detail -->
                    <div class="ig-avatar-ring me-3" style="padding: 2px;">
                        <div class="ig-avatar-inner" style="padding: 2px;">
                            <img id="detailProfilePic" src="" class="ig-avatar-img" style="width:48px; height:48px;" onerror="this.src='https://ui-avatars.com/api/?name=Instagram&background=8a3ab9&color=fff'">
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-primary text-white rounded-pill px-3 py-1 mb-1" style="font-size: 0.72rem; background: var(--ig-gradient) !important; font-weight: 700;">Dashboard Akun</span>
                        <h3 class="fw-bold mb-0 text-dark" id="activeAccountName">@username</h3>
                        <span class="small text-muted" id="activeAccountId" style="font-size: 0.78rem;">ID: -</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary py-2.5 px-4 rounded-3 fw-bold" onclick="refreshAll()" style="border-radius: 12px;">
                        <i class="ti ti-refresh me-1"></i> Refresh Data
                    </button>
                    <button class="btn btn-primary py-2.5 px-4 rounded-3 fw-bold btn-gradient-instagram" onclick="fetchMedia(activeIgUserId)" style="border-radius: 12px;">
                        <i class="ti ti-rotate me-1"></i> Sync Media & Comments
                    </button>
                </div>
            </div>
        </div>

        <!-- STATS GRID -->
        <div class="row g-4 mb-4">
            <div class="col-xl-4 col-sm-6">
                <div class="card stats-card-premium shadow-sm bg-white" style="border-top: 4px solid var(--ig-blue) !important;">
                    <div class="card-body p-4">
                        <span class="stat-bg-icon">🔔</span>
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: rgba(79, 70, 229, 0.08); color: var(--primary);">
                                <i class="ti ti-bell-ringing fs-5"></i>
                            </div>
                            <span class="text-muted font-weight-medium" style="font-size: 0.9rem;">Webhook Events</span>
                        </div>
                        <h2 class="fw-bold mb-0 text-dark" id="statWebhooks">-</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6">
                <div class="card stats-card-premium shadow-sm bg-white" style="border-top: 4px solid var(--ig-pink) !important;">
                    <div class="card-body p-4">
                        <span class="stat-bg-icon">💬</span>
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: rgba(220, 39, 67, 0.08); color: var(--ig-pink);">
                                <i class="ti ti-message-2 fs-5"></i>
                            </div>
                            <span class="text-muted font-weight-medium" style="font-size: 0.9rem;">Total Komentar</span>
                        </div>
                        <h2 class="fw-bold mb-0 text-dark" id="statComments">-</h2>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-12">
                <div class="card stats-card-premium shadow-sm bg-white" style="border-top: 4px solid var(--ig-orange) !important;">
                    <div class="card-body p-4">
                        <span class="stat-bg-icon">✉️</span>
                        <div class="d-flex align-items-center mb-2">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: rgba(250, 126, 30, 0.08); color: var(--ig-orange);">
                                <i class="ti ti-mail-opened fs-5"></i>
                            </div>
                            <span class="text-muted font-weight-medium" style="font-size: 0.9rem;">Total Pesan / DM</span>
                        </div>
                        <h2 class="fw-bold mb-0 text-dark" id="statMessages">-</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABS NAVIGATION & CARD CONTENTS -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <div class="d-flex border-bottom overflow-auto">
                    <button class="tab-btn-premium active" onclick="switchTab('comments', this)">💬 Komentar</button>
                    <button class="tab-btn-premium" onclick="switchTab('media', this)">📸 Media</button>
                    <button class="tab-btn-premium" onclick="switchTab('messages', this)">✉️ Pesan (DM)</button>
                    <button class="tab-btn-premium" onclick="switchTab('sentiment', this)"><i class="ti ti-chart-donut me-1"></i> Sentimen</button>
                    <button class="tab-btn-premium" onclick="switchTab('monitoring', this)"><i class="ti ti-activity me-1"></i> Monitoring</button>
                    <button class="tab-btn-premium" onclick="switchTab('replies', this)"><i class="ti ti-message-reply me-1"></i> Balasan</button>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- TAB: WEBHOOK LOGS -->
                <div class="tab-content-panel" id="tab-webhooks" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-dark"><span class="live-dot"></span> Real-time Webhook Logs</h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadWebhookLogs()">🔄 Refresh</button>
                    </div>
                    <p class="text-muted small mb-3">
                        Log payload dari Meta/Instagram yang diterima secara real-time. Data diperbarui otomatis setiap <strong>10 detik</strong>.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Waktu</th>
                                    <th>Tipe Objek</th>
                                    <th>Event</th>
                                    <th>Value Payload</th>
                                </tr>
                            </thead>
                            <tbody id="webhookLogsBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Memuat data log...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB: KOMENTAR -->
                <div class="tab-content-panel" id="tab-comments" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-dark">💬 Komentar Terbaru</h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadComments()">🔄 Refresh</button>
                    </div>
                    <div id="commentsFeedContainer" class="py-2" style="max-height: 520px; overflow-y: auto; padding-right: 5px;">
                        <div class="text-center text-muted py-4">Memuat komentar...</div>
                    </div>
                </div>

                <!-- TAB: MEDIA -->
                <div class="tab-content-panel" id="tab-media" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 text-dark">📸 Media / Postingan Terkini</h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadMedia()">🔄 Refresh</button>
                    </div>
                    <div class="row g-4" id="mediaGridContainer">
                        <div class="col-12 text-center text-muted py-4">Memuat postingan...</div>
                    </div>
                </div>

                <!-- TAB: PESAN / DM -->
                <div class="tab-content-panel" id="tab-messages" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-dark">✉️ Direct Messages (DM)</h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadMessages()">🔄 Refresh</button>
                    </div>
                    <div class="chat-bubble-container" id="messagesChatContainer">
                        <div class="text-center text-muted py-4">Memuat percakapan...</div>
                    </div>
                </div>

                <!-- TAB: ANALISIS SENTIMEN -->
                <div class="tab-content-panel" id="tab-sentiment" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="ti ti-chart-donut me-1 text-primary"></i> Diagram Analisis Sentimen Akun</h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadSentimentAnalysis()">Refresh</button>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="date" id="sentimentDateFrom" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <input type="date" id="sentimentDateTo" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <select id="sentimentSourceFilter" class="form-select form-select-sm">
                                <option value="all">Semua sumber</option>
                                <option value="comment">Komentar</option>
                                <option value="message">DM</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" id="sentimentMediaFilter" class="form-control form-control-sm" placeholder="Media ID">
                        </div>
                        <div class="col-md-2">
                            <select id="sentimentAnalyzer" class="form-select form-select-sm">
                                <option value="local">Keyword lokal</option>
                                <option value="ai">AI-ready</option>
                            </select>
                        </div>
                    </div>
                    <div id="sentimentEmptyState" class="text-center text-muted py-5 bg-light rounded-4 border" style="display:none;">
                        Belum ada komentar atau DM berisi teks untuk dianalisis.
                    </div>
                    <div id="sentimentDashboard">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="sentiment-metric">
                                    <span class="text-muted small d-block">Positif</span>
                                    <h3 class="fw-bold text-success mb-0" id="sentimentPositive">0</h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sentiment-metric">
                                    <span class="text-muted small d-block">Netral</span>
                                    <h3 class="fw-bold text-secondary mb-0" id="sentimentNeutral">0</h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sentiment-metric">
                                    <span class="text-muted small d-block">Negatif</span>
                                    <h3 class="fw-bold text-danger mb-0" id="sentimentNegative">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-lg-5">
                                <div class="border rounded-4 p-3 h-100">
                                    <h6 class="fw-bold mb-3">Komposisi Sentimen</h6>
                                    <div id="sentimentDonutChart" style="min-height: 280px;"></div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="border rounded-4 p-3 h-100">
                                    <h6 class="fw-bold mb-3">Tren Sentimen Harian</h6>
                                    <div id="sentimentTrendChart" style="min-height: 280px;"></div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="border rounded-4 p-3 h-100">
                                    <h6 class="fw-bold mb-3">Sumber Percakapan</h6>
                                    <div id="sentimentSourceChart" style="min-height: 260px;"></div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="border rounded-4 p-3 h-100">
                                    <h6 class="fw-bold mb-3">Teks Terbaru yang Dianalisis</h6>
                                    <div id="sentimentRecentList" class="sentiment-list"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: MONITORING & EXPORT -->
                <div class="tab-content-panel" id="tab-monitoring" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="ti ti-activity me-1 text-primary"></i> Webhook Health & Export</h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadWebhookHealth()">Refresh</button>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="sentiment-metric">
                                <span class="text-muted small d-block">Status Webhook</span>
                                <h5 class="fw-bold mb-1" id="healthWebhookStatus">-</h5>
                                <small class="text-muted" id="healthLastEvent">Event terakhir: -</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sentiment-metric">
                                <span class="text-muted small d-block">Status Token</span>
                                <h5 class="fw-bold mb-1" id="healthTokenStatus">-</h5>
                                <small class="text-muted" id="healthTokenExpires">Expired: -</small>
                                <button class="btn btn-sm btn-outline-primary mt-2" onclick="refreshAccessToken()">Refresh Token</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sentiment-metric">
                                <span class="text-muted small d-block">Export Data</span>
                                <div class="d-flex gap-2 flex-wrap mt-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="exportData('comments')">Komentar</button>
                                    <button class="btn btn-sm btn-outline-primary" onclick="exportData('messages')">DM</button>
                                    <button class="btn btn-sm btn-outline-primary" onclick="exportData('webhooks')">Webhook</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border rounded-4 p-3">
                        <h6 class="fw-bold mb-3">Ringkasan Event Webhook</h6>
                        <div id="healthEventCounts" class="row g-2"></div>
                    </div>
                </div>

                <!-- TAB: TEMPLATE BALASAN -->
                <div class="tab-content-panel" id="tab-replies" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="ti ti-message-reply me-1 text-primary"></i> Template & Auto-reply</h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="loadReplyTemplates()">Refresh</button>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="border rounded-4 p-3">
                                <input type="hidden" id="replyTemplateId">
                                <div class="mb-2">
                                    <label class="small text-muted">Nama Template</label>
                                    <input type="text" id="replyTemplateName" class="form-control" placeholder="Contoh: Harga">
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="small text-muted">Channel</label>
                                        <select id="replyTemplateChannel" class="form-select">
                                            <option value="all">Komentar & DM</option>
                                            <option value="comment">Komentar</option>
                                            <option value="message">DM</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted">Keyword</label>
                                        <input type="text" id="replyTemplateKeyword" class="form-control" placeholder="harga">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="small text-muted">Isi Balasan</label>
                                    <textarea id="replyTemplateText" class="form-control" rows="4" placeholder="Tulis balasan cepat..."></textarea>
                                </div>
                                <div class="d-flex gap-3 mb-3">
                                    <label class="form-check-label"><input type="checkbox" id="replyTemplateActive" class="form-check-input" checked> Aktif</label>
                                    <label class="form-check-label"><input type="checkbox" id="replyTemplateAuto" class="form-check-input"> Auto-reply</label>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary flex-grow-1" onclick="saveReplyTemplate()">Simpan</button>
                                    <button class="btn btn-outline-secondary" onclick="resetReplyTemplateForm()">Reset</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div id="replyTemplatesList" class="border rounded-4 p-3" style="min-height: 260px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>dist/js/apexcharts.min.js"></script>
<script>
    const API_BASE = "<?= base_url('dashboard/'); ?>";
    const REFRESH_INTERVAL = 10000;
    let autoRefreshTimer = null;
    let latestMessageId = null;
    let sentimentDonutChart = null;
    let sentimentTrendChart = null;
    let sentimentSourceChart = null;
    let replyTemplateCache = {};
    
    // Active Account State
    let activeIgUserId = null;
    let activeUsername = null;

    $(document).ready(function() {
        // Check if callback returned success toast parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'success') {
            const username = urlParams.get('username') ? '@' + decodeURIComponent(urlParams.get('username')) : 'Instagram';
            alertify.success(`✅ Akun ${username} berhasil terhubung!`);
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    // ---- DASHBOARD PANEL NAVIGATION SYSTEM ----
    function openAccountDashboard(igUserId, username, profilePicUrl) {
        activeIgUserId = igUserId;
        activeUsername = username;
        
        $('#activeAccountName').text('@' + username);
        $('#activeAccountId').text('ID: ' + igUserId);
        $('.tab-content-panel').hide();
        $('.tab-btn-premium').removeClass('active');
        $('#tab-comments').show();
        $('.tab-btn-premium').first().addClass('active');
        
        const fallbackAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(username)}&background=8a3ab9&color=fff`;
        $('#detailProfilePic').attr('src', profilePicUrl ? profilePicUrl : fallbackAvatar);

        // Transition panels smoothly with CSS classes
        $('#mainAccountsPanel').addClass('panel-hidden');
        setTimeout(() => {
            $('#mainAccountsPanel').hide();
            $('#accountDashboardPanel').show();
            // Force redraw before removing class to trigger CSS transition
            $('#accountDashboardPanel')[0].offsetHeight;
            $('#accountDashboardPanel').removeClass('panel-hidden');
            
            // Fetch fresh data
            refreshAll();
            startAutoRefresh();
        }, 350);
    }

    function closeAccountDashboard() {
        stopAutoRefresh();
        $('#accountDashboardPanel').addClass('panel-hidden');
        setTimeout(() => {
            $('#accountDashboardPanel').hide();
            $('#mainAccountsPanel').show();
            // Force redraw
            $('#mainAccountsPanel')[0].offsetHeight;
            $('#mainAccountsPanel').removeClass('panel-hidden');
            
            activeIgUserId = null;
            activeUsername = null;
            latestMessageId = null;
        }, 350);
    }

    // ---- TAB SYSTEM ----
    function switchTab(tabName, btn) {
        $('.tab-content-panel').hide();
        $('.tab-btn-premium').removeClass('active');

        $('#tab-' + tabName).show();
        $(btn).addClass('active');
        
        // Auto scroll DM container to bottom if switching to messages
        if (tabName === 'messages') {
            setTimeout(() => {
                const container = $('#messagesChatContainer');
                if (container.length && container[0].scrollHeight) {
                    container.scrollTop(container[0].scrollHeight);
                }
            }, 50);
        }

        if (tabName === 'sentiment') {
            loadSentimentAnalysis();
        }
        if (tabName === 'monitoring') {
            loadWebhookHealth();
        }
        if (tabName === 'replies') {
            loadReplyTemplates();
        }
    }

    // ---- FETCH STATS ----
    function loadStats() {
        if (!activeIgUserId) return;
        $.getJSON(API_BASE + 'get_stats?ig_user_id=' + activeIgUserId, function(response) {
            if (response.success) {
                const d = response.data;
                $('#statWebhooks').text(d.total_webhook_events);
                $('#statComments').text(d.total_comments);
                $('#statMessages').text(d.total_messages);
            }
        });
    }

    // ---- FETCH WEBHOOK LOGS ----
    function loadWebhookLogs() {
        if (!activeIgUserId) return;
        $.getJSON(API_BASE + 'get_webhook_logs?limit=30&ig_user_id=' + activeIgUserId, function(response) {
            const tbody = $('#webhookLogsBody');
            if (!response.success || response.data.length === 0) {
                tbody.html(`<tr><td colspan="5" class="text-center text-muted py-5"><div class="fs-1 mb-1">🔔</div>Belum ada log webhook masuk untuk akun ini.</td></tr>`);
                return;
            }

            let html = '';
            response.data.forEach(log => {
                let valStr = '';
                try {
                    const parsed = JSON.parse(log.value);
                    valStr = JSON.stringify(parsed, null, 2);
                } catch (e) {
                    valStr = log.value;
                }

                html += `<tr>
                    <td class="fw-bold">#${log.id}</td>
                    <td class="text-muted small" style="white-space: nowrap;"><i class="ti ti-calendar-event me-1 text-primary"></i>${formatTime(log.created_at)}</td>
                    <td><span class="badge bg-secondary-subtle text-secondary py-1.5 px-3 rounded-pill fw-semibold">${esc(log.object || '-')}</span></td>
                    <td><span class="badge bg-primary-subtle text-primary py-1.5 px-3 rounded-pill fw-semibold">${esc(log.event_type || '-')}</span></td>
                    <td style="width: 50%;"><pre class="webhook-card-event"><code>${esc(valStr)}</code></pre></td>
                </tr>`;
            });
            tbody.html(html);
        });
    }

    // ---- FETCH COMMENTS (FEED VIEW) ----
    function loadComments() {
        if (!activeIgUserId) return;
        $.getJSON(API_BASE + 'get_comments?limit=30&ig_user_id=' + activeIgUserId, function(response) {
            const container = $('#commentsFeedContainer');
            if (!response.success || response.data.length === 0) {
                container.html(`<div class="text-center text-muted py-5 bg-light rounded-4 border"><div class="fs-1 mb-2">💬</div>Belum ada komentar terekam untuk akun ini.</div>`);
                return;
            }

            let html = '';
            response.data.forEach(c => {
                const targetIG = c.target_ig_username ? `@${c.target_ig_username}` : '';
                const initial = (c.from_username ? c.from_username.substring(0, 2) : 'IG').toUpperCase();
                const sourceBadge = c.is_from_webhook == 1 
                    ? '<span class="badge bg-success-subtle text-success py-1 px-2.5 rounded-pill small fw-bold">Webhook Event</span>' 
                    : '<span class="badge bg-info-subtle text-info py-1 px-2.5 rounded-pill small fw-bold">API Pull</span>';

                html += `
                <div class="comment-feed-item">
                    <div class="comment-avatar">
                        ${initial}
                    </div>
                    <div class="comment-bubble">
                        <div class="comment-meta">
                            <div>
                                <span class="fw-bold text-dark">@${esc(c.from_username || 'unknown')}</span>
                                ${targetIG ? `<span class="badge bg-light-primary text-primary ms-2 px-2.5 py-0.5 rounded-pill" style="font-size: 0.72rem; font-weight:600;">untuk ${esc(targetIG)}</span>` : ''}
                            </div>
                            <span class="text-muted small"><i class="ti ti-clock me-1"></i>${formatTime(c.created_at)}</span>
                        </div>
                        <p class="text-dark mb-2" style="font-size: 0.95rem;">${esc(c.text)}</p>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-muted" style="font-size:0.75rem;">Media ID: <code class="bg-light px-2 py-0.5 rounded text-dark border small">${esc(c.media_id)}</code></small>
                            ${sourceBadge}
                        </div>
                    </div>
                </div>`;
            });
            container.html(html);
        });
    }

    // ---- FETCH MEDIA (GALLERY GRID VIEW) ----
    function loadMedia() {
        if (!activeIgUserId) return;
        $.getJSON(API_BASE + 'get_media?limit=20&ig_user_id=' + activeIgUserId, function(response) {
            const container = $('#mediaGridContainer');
            if (!response.success || response.data.length === 0) {
                container.html(`<div class="col-12 text-center text-muted py-5 bg-light rounded-4 border"><div class="fs-1 mb-2">📸</div>Belum ada media postingan. Silakan klik "Sync Media & Comments" terlebih dahulu.</div>`);
                return;
            }

            let html = '';
            response.data.forEach(m => {
                const icon = m.media_type === 'IMAGE' ? '<i class="ti ti-photo me-1"></i> Gambar' : (m.media_type === 'VIDEO' ? '<i class="ti ti-video me-1"></i> Video' : '<i class="ti ti-slideshow me-1"></i> Carousel');
                const caption = m.caption || '';
                
                // Use default placehold if no URL is returned or access is blocked
                const mediaUrl = m.media_url ? m.media_url : 'https://placehold.co/600x600/6b46c1/ffffff?text=' + encodeURIComponent(m.media_type);
                const mediaHtml = m.media_type === 'VIDEO'
                    ? `<video src="${esc(mediaUrl)}" controls playsinline preload="metadata"></video>`
                    : `<img src="${esc(mediaUrl)}" onerror="this.src='https://placehold.co/600x600/6b46c1/ffffff?text=${encodeURIComponent(m.media_type || 'MEDIA')}'">`;
                const safeMediaId = esc(m.media_id || '');

                html += `
                <div class="col-md-6 col-xl-4">
                    <div class="card media-grid-card h-100">
                        <div class="media-card-img-wrapper">
                            ${mediaHtml}
                            <div class="media-card-overlay">
                                <span><i class="ti ti-heart-filled text-danger me-1"></i> ${formatNumber(m.like_count || 0)}</span>
                                <span><i class="ti ti-message-circle-2-filled text-white me-1"></i> ${formatNumber(m.comments_count || 0)}</span>
                            </div>
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-secondary-subtle text-secondary py-1 px-2.5 rounded-pill small fw-bold">${icon}</span>
                                    <span class="text-muted small"><i class="ti ti-clock me-1"></i>${formatTime(m.timestamp)}</span>
                                </div>
                                <div class="instagram-actions mb-2">
                                    <i class="ti ti-heart"></i>
                                    <button class="btn btn-link p-0 text-dark" onclick='loadMediaComments(${JSON.stringify(m.media_id || '')})'><i class="ti ti-message-circle"></i></button>
                                    ${m.permalink ? `<a href="${esc(m.permalink)}" target="_blank" class="text-dark"><i class="ti ti-send"></i></a>` : '<i class="ti ti-send"></i>'}
                                </div>
                                <div class="fw-bold text-dark small mb-1">${formatNumber(m.like_count || 0)} suka</div>
                                <p class="text-dark small mb-2" style="line-height:1.4;"><span class="fw-bold">@${esc(activeUsername || 'instagram')}</span> ${caption ? esc(caption) : '<span class="text-muted">Tidak ada caption</span>'}</p>
                                <button class="btn btn-link text-muted small p-0 mb-2 text-start" onclick='loadMediaComments(${JSON.stringify(m.media_id || '')})'>
                                    Lihat ${formatNumber(m.comments_count || 0)} komentar
                                </button>
                            </div>
                            <div class="media-comments-panel p-3 mt-2" id="mediaComments-${safeDomId(m.media_id)}" style="display:none;">
                                <div class="text-muted small">Memuat komentar...</div>
                            </div>
                            ${m.permalink ? `<a href="${esc(m.permalink)}" target="_blank" class="btn btn-sm btn-outline-primary w-100 rounded-3 fw-bold mt-3"><i class="ti ti-external-link me-1"></i> Buka di Instagram</a>` : ''}
                        </div>
                    </div>
                </div>`;
            });
            container.html(html);
        });
    }

    function loadMediaComments(mediaId) {
        if (!activeIgUserId || !mediaId) return;
        const panel = $('#mediaComments-' + safeDomId(mediaId));
        panel.show().html('<div class="text-muted small">Memuat komentar...</div>');

        $.getJSON(API_BASE + 'get_media_comments?' + $.param({ ig_user_id: activeIgUserId, media_id: mediaId }), function(response) {
            if (!response.success) {
                panel.html(`<div class="text-danger small">${esc(response.error || 'Gagal memuat komentar.')}</div>`);
                return;
            }

            const comments = response.data || [];
            if (!comments.length) {
                panel.html(`
                    <div class="text-muted small mb-3">Belum ada komentar.</div>
                    ${renderReplyBox(mediaId, '', 'Tulis komentar...')}
                `);
                return;
            }

            const parents = comments.filter(c => !c.parent_id);
            const replies = {};
            comments.filter(c => c.parent_id).forEach(c => {
                if (!replies[c.parent_id]) replies[c.parent_id] = [];
                replies[c.parent_id].push(c);
            });

            let html = '';
            parents.forEach(c => {
                html += renderMediaComment(c, mediaId, false);
                (replies[c.comment_id] || []).forEach(reply => {
                    html += renderMediaComment(reply, mediaId, true);
                });
            });
            html += renderReplyBox(mediaId, '', 'Tulis komentar...');
            panel.html(html);
        });
    }

    function renderMediaComment(c, mediaId, isReply) {
        const username = c.from_username || (isReply ? activeUsername : 'unknown');
        const initial = username.substring(0, 1).toUpperCase();
        return `
            <div class="media-comment-item ${isReply ? 'reply' : ''}">
                <div class="media-comment-avatar">${esc(initial)}</div>
                <div class="flex-grow-1">
                    <div class="small text-dark">
                        <span class="fw-bold">@${esc(username)}</span>
                        <span>${esc(c.text || '')}</span>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-1">
                        <span class="text-muted" style="font-size:0.72rem;">${formatTime(c.created_at || c.timestamp)}</span>
                        <span class="text-muted" style="font-size:0.72rem;">${formatNumber(c.like_count || 0)} suka</span>
                        ${!isReply ? `<button class="btn btn-link p-0 text-muted fw-bold" style="font-size:0.72rem;" onclick='showCommentReplyBox(${JSON.stringify(mediaId)}, ${JSON.stringify(c.comment_id)})'>Balas</button>` : ''}
                    </div>
                    ${!isReply ? `<div id="replyBox-${safeDomId(c.comment_id)}" style="display:none;">${renderReplyBox(mediaId, c.comment_id, 'Balas @' + username)}</div>` : ''}
                </div>
            </div>`;
    }

    function renderReplyBox(mediaId, commentId, placeholder) {
        const targetId = commentId ? commentId : mediaId;
        return `
            <div class="d-flex align-items-center gap-2 mt-3">
                <input type="text" class="form-control form-control-sm rounded-pill" id="replyInput-${safeDomId(targetId)}" placeholder="${esc(placeholder)}">
                <button class="btn btn-sm btn-primary rounded-pill px-3" onclick='submitCommentReply(${JSON.stringify(mediaId)}, ${JSON.stringify(targetId)}, ${JSON.stringify(commentId || '')})'>Kirim</button>
            </div>`;
    }

    function showCommentReplyBox(mediaId, commentId) {
        $('#replyBox-' + safeDomId(commentId)).toggle();
        $('#replyInput-' + safeDomId(commentId)).focus();
    }

    function submitCommentReply(mediaId, targetId, parentCommentId) {
        const input = $('#replyInput-' + safeDomId(targetId));
        const message = input.val().trim();
        if (!message) {
            alertify.warning('Balasan masih kosong.');
            return;
        }

        const commentId = parentCommentId || targetId;
        $.post(API_BASE + 'reply_comment', {
            ig_user_id: activeIgUserId,
            media_id: mediaId,
            comment_id: commentId,
            message: message
        }, function(response) {
            if (response.success) {
                alertify.success('Balasan komentar terkirim.');
                input.val('');
                loadMediaComments(mediaId);
                loadComments();
                loadStats();
            } else {
                alertify.error(response.error || 'Gagal membalas komentar.');
            }
        }, 'json');
    }

    // ---- FETCH MESSAGES (CHAT BUBBLES VIEW) ----
    function loadMessages() {
        if (!activeIgUserId) return;
        $.getJSON(API_BASE + 'get_messages?limit=30&ig_user_id=' + activeIgUserId, function(response) {
            const container = $('#messagesChatContainer');
            if (!response.success || response.data.length === 0) {
                container.html(`<div class="text-center text-muted py-5"><div class="fs-1 mb-2">✉️</div>Belum ada pesan terekam untuk akun ini.</div>`);
                return;
            }

            // Notify on new messages if page already loaded once
            if (latestMessageId !== null) {
                const newMsgs = response.data.filter(m => parseInt(m.id) > latestMessageId).reverse();
                newMsgs.forEach(m => {
                    const messageText = m.message_text || '';
                    const snippet = messageText.length > 40 ? messageText.substring(0, 40) + '...' : messageText;
                    alertify.success(`✉️ Pesan Baru: "${snippet}"`);
                });
            }
            latestMessageId = parseInt(response.data[0].id);

            let html = '';
            // Sort chronologically (earliest message first for chat timeline)
            const sortedData = [...response.data].reverse();

            sortedData.forEach(m => {
                let directionClass = '';
                let counterpartName = '';
                let targetAcc = '';
                let isOutbound = false;
                
                if (m.sender_username) {
                    directionClass = 'outbound';
                    counterpartName = esc(m.recipient_id);
                    targetAcc = `@${m.sender_username}`;
                    isOutbound = true;
                } else if (m.recipient_username) {
                    directionClass = 'inbound';
                    counterpartName = esc(m.sender_id);
                    targetAcc = `@${m.recipient_username}`;
                    isOutbound = false;
                } else {
                    directionClass = 'inbound';
                    counterpartName = esc(m.sender_id);
                    targetAcc = '-';
                    isOutbound = false;
                }

                html += `
                <div class="chat-bubble ${directionClass}">
                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.72rem; opacity: 0.85; font-weight: 600;">
                        <span>${isOutbound ? 'Anda (' + targetAcc + ')' : 'Pengirim (' + counterpartName + ')'}</span>
                    </div>
                    <div class="chat-text" style="word-break: break-word;">${esc(m.message_text)}</div>
                    <div class="chat-time">
                        <i class="ti ti-clock" style="font-size: 0.65rem;"></i>${formatTime(m.created_at)}
                    </div>
                </div>`;
            });
            container.html(html);
            
            // Auto scroll to bottom
            setTimeout(() => {
                container.scrollTop(container[0].scrollHeight);
            }, 80);
        });
    }

    // ---- FETCH SENTIMENT ANALYSIS ----
    function loadSentimentAnalysis() {
        if (!activeIgUserId) return;
        const params = $.param({
            ig_user_id: activeIgUserId,
            date_from: $('#sentimentDateFrom').val(),
            date_to: $('#sentimentDateTo').val(),
            source: $('#sentimentSourceFilter').val(),
            media_id: $('#sentimentMediaFilter').val(),
            analyzer: $('#sentimentAnalyzer').val()
        });
        $.getJSON(API_BASE + 'get_sentiment_analysis?' + params, function(response) {
            if (!response.success) {
                $('#sentimentEmptyState').show().text(response.error || 'Gagal memuat analisis sentimen.');
                $('#sentimentDashboard').hide();
                return;
            }

            const data = response.data || {};
            const summary = data.summary || { positive: 0, neutral: 0, negative: 0, total: 0 };
            const total = parseInt(summary.total || 0);

            $('#sentimentPositive').text(summary.positive || 0);
            $('#sentimentNeutral').text(summary.neutral || 0);
            $('#sentimentNegative').text(summary.negative || 0);

            if (total === 0) {
                $('#sentimentEmptyState').show().text('Belum ada komentar atau DM berisi teks untuk dianalisis.');
                $('#sentimentDashboard').hide();
                destroySentimentCharts();
                return;
            }

            $('#sentimentEmptyState').hide();
            $('#sentimentDashboard').show();
            renderSentimentCharts(data);
            renderSentimentRecent(data.recent || []);
        });
    }

    function destroySentimentCharts() {
        [sentimentDonutChart, sentimentTrendChart, sentimentSourceChart].forEach(chart => {
            if (chart) chart.destroy();
        });
        sentimentDonutChart = null;
        sentimentTrendChart = null;
        sentimentSourceChart = null;
    }

    function renderSentimentCharts(data) {
        if (typeof ApexCharts === 'undefined') {
            $('#sentimentDonutChart').html('<div class="text-muted text-center py-5">Library chart belum termuat.</div>');
            return;
        }

        destroySentimentCharts();
        const summary = data.summary || {};
        const colors = ['#22c55e', '#94a3b8', '#ef4444'];

        sentimentDonutChart = new ApexCharts(document.querySelector('#sentimentDonutChart'), {
            chart: { type: 'donut', height: 280 },
            series: [parseInt(summary.positive || 0), parseInt(summary.neutral || 0), parseInt(summary.negative || 0)],
            labels: ['Positif', 'Netral', 'Negatif'],
            colors: colors,
            legend: { position: 'bottom' },
            dataLabels: { enabled: true },
            stroke: { width: 0 }
        });
        sentimentDonutChart.render();

        const trend = data.trend || [];
        sentimentTrendChart = new ApexCharts(document.querySelector('#sentimentTrendChart'), {
            chart: { type: 'area', height: 280, toolbar: { show: false } },
            series: [
                { name: 'Positif', data: trend.map(row => parseInt(row.positive || 0)) },
                { name: 'Netral', data: trend.map(row => parseInt(row.neutral || 0)) },
                { name: 'Negatif', data: trend.map(row => parseInt(row.negative || 0)) }
            ],
            xaxis: { categories: trend.map(row => row.date), labels: { rotate: -30 } },
            colors: colors,
            stroke: { curve: 'smooth', width: 3 },
            fill: { opacity: 0.16 },
            dataLabels: { enabled: false },
            grid: { borderColor: '#eef2f6' }
        });
        sentimentTrendChart.render();

        const source = data.source_summary || {};
        const commentSource = source.comment || {};
        const messageSource = source.message || {};
        sentimentSourceChart = new ApexCharts(document.querySelector('#sentimentSourceChart'), {
            chart: { type: 'bar', height: 260, stacked: true, toolbar: { show: false } },
            series: [
                { name: 'Positif', data: [parseInt(commentSource.positive || 0), parseInt(messageSource.positive || 0)] },
                { name: 'Netral', data: [parseInt(commentSource.neutral || 0), parseInt(messageSource.neutral || 0)] },
                { name: 'Negatif', data: [parseInt(commentSource.negative || 0), parseInt(messageSource.negative || 0)] }
            ],
            xaxis: { categories: ['Komentar', 'DM'] },
            colors: colors,
            plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
            dataLabels: { enabled: false },
            legend: { position: 'bottom' },
            grid: { borderColor: '#eef2f6' }
        });
        sentimentSourceChart.render();
    }

    function renderSentimentRecent(recent) {
        const colorMap = {
            positive: '#22c55e',
            neutral: '#94a3b8',
            negative: '#ef4444'
        };
        const labelMap = {
            positive: 'Positif',
            neutral: 'Netral',
            negative: 'Negatif'
        };

        if (!recent.length) {
            $('#sentimentRecentList').html('<div class="text-muted py-4 text-center">Belum ada teks terbaru.</div>');
            return;
        }

        let html = '';
        recent.forEach(item => {
            const sentiment = item.sentiment || 'neutral';
            html += `
                <div class="border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-light text-dark border">${item.source === 'message' ? 'DM' : 'Komentar'}</span>
                        <span class="small fw-bold" style="color:${colorMap[sentiment]};">
                            <span class="sentiment-dot me-1" style="background:${colorMap[sentiment]};"></span>${labelMap[sentiment]}
                        </span>
                    </div>
                    <div class="text-dark small mb-1">${esc(item.text)}</div>
                    <div class="text-muted" style="font-size:0.75rem;">${formatTime(item.created_at)}</div>
                </div>`;
        });
        $('#sentimentRecentList').html(html);
    }

    // ---- WEBHOOK HEALTH & EXPORT ----
    function loadWebhookHealth() {
        if (!activeIgUserId) return;
        $.getJSON(API_BASE + 'get_webhook_health?ig_user_id=' + encodeURIComponent(activeIgUserId), function(response) {
            if (!response.success) {
                alertify.error(response.error || 'Gagal memuat monitoring.');
                return;
            }

            const d = response.data || {};
            const tokenClass = d.token_status === 'ok' ? 'text-success' : (d.token_status === 'warning' ? 'text-warning' : 'text-danger');
            $('#healthWebhookStatus').text(d.webhook_status === 'receiving' ? 'Menerima event' : 'Belum ada event').attr('class', d.webhook_status === 'receiving' ? 'fw-bold mb-1 text-success' : 'fw-bold mb-1 text-warning');
            $('#healthLastEvent').text('Event terakhir: ' + (d.last_event_at || '-'));
            $('#healthTokenStatus').text(d.token_status + (d.days_left !== null ? ` (${d.days_left} hari)` : '')).attr('class', 'fw-bold mb-1 ' + tokenClass);
            $('#healthTokenExpires').text('Expired: ' + (d.expires_at || '-'));

            if (d.token_status === 'warning' || d.token_status === 'expired') {
                alertify.warning('Token akun ini perlu diperhatikan. Hubungkan ulang akun jika sudah expired atau hampir expired.');
            }

            const counts = d.event_counts || [];
            if (!counts.length) {
                $('#healthEventCounts').html('<div class="col-12 text-muted">Belum ada event webhook.</div>');
                return;
            }

            let html = '';
            counts.forEach(item => {
                html += `<div class="col-md-3 col-6"><div class="bg-light rounded-3 p-3 border"><span class="small text-muted d-block">${esc(item.event_type || '-')}</span><strong>${item.total}</strong></div></div>`;
            });
            $('#healthEventCounts').html(html);
        });
    }

    function exportData(type) {
        if (!activeIgUserId) return;
        window.location.href = API_BASE + 'export_data?' + $.param({ ig_user_id: activeIgUserId, type: type });
    }

    function refreshAccessToken() {
        if (!activeIgUserId) return;
        alertify.message('Mencoba refresh token...');
        $.getJSON(API_BASE + 'refresh_access_token?ig_user_id=' + encodeURIComponent(activeIgUserId), function(response) {
            if (response.success) {
                alertify.success('Token berhasil di-refresh.');
                loadWebhookHealth();
            } else {
                alertify.error(response.error || 'Gagal refresh token. Hubungkan ulang akun jika token sudah tidak valid.');
            }
        });
    }

    // ---- REPLY TEMPLATES ----
    function loadReplyTemplates() {
        if (!activeIgUserId) return;
        $.getJSON(API_BASE + 'get_reply_templates?ig_user_id=' + encodeURIComponent(activeIgUserId), function(response) {
            if (!response.success) {
                $('#replyTemplatesList').html('<div class="text-danger">Gagal memuat template.</div>');
                return;
            }

            const templates = response.data || [];
            replyTemplateCache = {};
            if (!templates.length) {
                $('#replyTemplatesList').html('<div class="text-muted text-center py-5">Belum ada template balasan.</div>');
                return;
            }

            let html = '';
            templates.forEach(t => {
                replyTemplateCache[t.id] = t;
                html += `
                    <div class="border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <h6 class="fw-bold mb-1">${esc(t.name)}</h6>
                                <div class="small text-muted mb-2">Channel: ${esc(t.channel)} · Keyword: ${esc(t.keyword || '-')}</div>
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-secondary" onclick="editReplyTemplate(${t.id})">Edit</button>
                                <button class="btn btn-sm btn-outline-primary" onclick="copyReplyTextById(${t.id})">Copy</button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteReplyTemplate(${t.id})">Hapus</button>
                            </div>
                        </div>
                        <div class="small text-dark">${esc(t.response_text)}</div>
                        <div class="mt-2">
                            <span class="badge ${t.is_active == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}">${t.is_active == 1 ? 'Aktif' : 'Nonaktif'}</span>
                            <span class="badge ${t.auto_reply == 1 ? 'bg-primary-subtle text-primary' : 'bg-light text-muted border'}">${t.auto_reply == 1 ? 'Auto-reply ON' : 'Manual'}</span>
                        </div>
                    </div>`;
            });
            $('#replyTemplatesList').html(html);
        });
    }

    function saveReplyTemplate() {
        if (!activeIgUserId) return;
        $.post(API_BASE + 'save_reply_template', {
            id: $('#replyTemplateId').val(),
            ig_user_id: activeIgUserId,
            name: $('#replyTemplateName').val(),
            channel: $('#replyTemplateChannel').val(),
            keyword: $('#replyTemplateKeyword').val(),
            response_text: $('#replyTemplateText').val(),
            is_active: $('#replyTemplateActive').is(':checked') ? 1 : 0,
            auto_reply: $('#replyTemplateAuto').is(':checked') ? 1 : 0
        }, function(response) {
            if (response.success) {
                alertify.success('Template balasan tersimpan.');
                resetReplyTemplateForm();
                loadReplyTemplates();
            } else {
                alertify.error(response.error || 'Gagal menyimpan template.');
            }
        }, 'json');
    }

    function editReplyTemplate(id) {
        const t = replyTemplateCache[id];
        if (!t) return;
        $('#replyTemplateId').val(t.id);
        $('#replyTemplateName').val(t.name);
        $('#replyTemplateChannel').val(t.channel);
        $('#replyTemplateKeyword').val(t.keyword);
        $('#replyTemplateText').val(t.response_text);
        $('#replyTemplateActive').prop('checked', t.is_active == 1);
        $('#replyTemplateAuto').prop('checked', t.auto_reply == 1);
    }

    function resetReplyTemplateForm() {
        $('#replyTemplateId').val('');
        $('#replyTemplateName').val('');
        $('#replyTemplateChannel').val('all');
        $('#replyTemplateKeyword').val('');
        $('#replyTemplateText').val('');
        $('#replyTemplateActive').prop('checked', true);
        $('#replyTemplateAuto').prop('checked', false);
    }

    function deleteReplyTemplate(id) {
        alertify.confirm('Hapus Template', 'Hapus template balasan ini?',
            function() {
                $.getJSON(API_BASE + 'delete_reply_template?' + $.param({ ig_user_id: activeIgUserId, id: id }), function(response) {
                    if (response.success) {
                        alertify.success('Template dihapus.');
                        loadReplyTemplates();
                    } else {
                        alertify.error(response.error || 'Gagal menghapus template.');
                    }
                });
            },
            function() {}
        );
    }

    function copyReplyText(text) {
        navigator.clipboard.writeText(text || '').then(function() {
            alertify.success('Isi balasan disalin.');
        });
    }

    function copyReplyTextById(id) {
        const t = replyTemplateCache[id];
        copyReplyText(t ? t.response_text : '');
    }

    // ---- FETCH MEDIA API CALL ----
    function fetchMedia(igUserId) {
        alertify.message('📥 Mengambil profil & media terbaru dari Instagram...');
        $.getJSON(API_BASE + 'fetch_media?ig_user_id=' + igUserId, function(response) {
            if (response.success) {
                alertify.success(`✅ Sukses! ${response.count} postingan & ${response.comments_count || 0} komentar disinkronkan.`);
                loadMedia();
                loadComments();
                loadStats();
            } else {
                alertify.error(`❌ Gagal sinkronisasi: ${response.error}`);
            }
        });
    }

    // ---- REFRESH ALL DATA ----
    function refreshAll() {
        loadStats();
        loadComments();
        loadMedia();
        loadMessages();
        if ($('#tab-sentiment').is(':visible')) {
            loadSentimentAnalysis();
        }
    }

    // ---- AUTO REFRESH TICKER ----
    function startAutoRefresh() {
        stopAutoRefresh();
        autoRefreshTimer = setInterval(function() {
            loadStats();
            loadComments();
            loadMessages();
            if ($('#tab-sentiment').is(':visible')) {
                loadSentimentAnalysis();
            }
        }, REFRESH_INTERVAL);
    }

    function stopAutoRefresh() {
        if (autoRefreshTimer) {
            clearInterval(autoRefreshTimer);
            autoRefreshTimer = null;
        }
    }

    // ---- HELPERS ----
    function esc(str) {
        if (str === null || str === undefined) return '';
        return $('<div/>').text(str).html();
    }

    function formatTime(dateStr) {
        if (!dateStr || dateStr === '-') return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short'
            }) + ' ' +
            d.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
    }

    function formatNumber(value) {
        const number = parseInt(value || 0);
        return number.toLocaleString('id-ID');
    }

    function safeDomId(value) {
        return String(value || '').replace(/[^a-zA-Z0-9_-]/g, '_');
    }

    function copyToken(text, username) {
        navigator.clipboard.writeText(text).then(function() {
            alertify.success(`📋 Token @${username} berhasil disalin!`);
        }).catch(function(err) {
            alertify.error('❌ Gagal menyalin token: ' + err);
        });
    }

    function deleteAccount(igUserId, username) {
        alertify.confirm("Hapus Akun", `Apakah Anda yakin ingin menghapus koneksi akun @${username}? Semua data terkait di dashboard lokal akan terputus.`,
            function() {
                $.getJSON(API_BASE + 'delete_account?ig_user_id=' + igUserId, function(response) {
                    if (response.success) {
                        alertify.success(`🗑️ Koneksi akun @${username} berhasil dihapus.`);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alertify.error(`❌ Gagal menghapus: ${response.error}`);
                    }
                });
            },
            function() {
                // cancelled
            }
        );
    }
</script>
