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
                                </div>
                                
                                <!-- Card Actions -->
                                <div>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-gradient-instagram py-2.5 rounded-3 fw-bold" onclick="openAccountDashboard('<?= $acc['ig_user_id'] ?>', '<?= htmlspecialchars($acc['username']) ?>', '<?= htmlspecialchars($acc['profile_picture_url'] ?? '') ?>')">
                                            Buka Dashboard Akun 🚀
                                        </button>
                                        <div class="d-flex justify-content-between mt-1 gap-2">
                                            <button class="btn btn-sm btn-outline-secondary flex-grow-1 py-2 fw-bold" onclick="copyToken('<?= htmlspecialchars($acc['access_token']) ?>', '<?= htmlspecialchars($acc['username']) ?>')" style="border-radius: 10px;">
                                                <i class="ti ti-copy me-1"></i> Salin Token
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger flex-grow-1 py-2 fw-bold" onclick="deleteAccount('<?= $acc['ig_user_id'] ?>', '<?= htmlspecialchars($acc['username']) ?>')" style="border-radius: 10px;">
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
                    <button class="tab-btn-premium active" onclick="switchTab('webhooks', this)">
                        <span class="live-dot"></span> Webhook Logs
                    </button>
                    <button class="tab-btn-premium" onclick="switchTab('comments', this)">💬 Komentar</button>
                    <button class="tab-btn-premium" onclick="switchTab('media', this)">📸 Media</button>
                    <button class="tab-btn-premium" onclick="switchTab('messages', this)">✉️ Pesan (DM)</button>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- TAB: WEBHOOK LOGS -->
                <div class="tab-content-panel" id="tab-webhooks">
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
            </div>
        </div>
    </div>
</div>

<script>
    const API_BASE = "<?= base_url('dashboard/'); ?>";
    const REFRESH_INTERVAL = 10000;
    let autoRefreshTimer = null;
    let latestMessageId = null;
    
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
                const caption = m.caption ? (m.caption.length > 90 ? m.caption.substring(0, 90) + '...' : m.caption) : '<span class="text-muted italic">Tidak ada caption</span>';
                
                // Use default placehold if no URL is returned or access is blocked
                const mediaUrl = m.media_url ? m.media_url : 'https://placehold.co/600x600/6b46c1/ffffff?text=' + encodeURIComponent(m.media_type);

                html += `
                <div class="col-md-6 col-lg-4">
                    <div class="card media-grid-card h-100">
                        <div class="media-card-img-wrapper">
                            <img src="${esc(mediaUrl)}" onerror="this.src='https://placehold.co/600x600/6b46c1/ffffff?text=${m.media_type}'">
                            <div class="media-card-overlay">
                                <span><i class="ti ti-heart-filled text-danger me-1"></i> ${m.like_count}</span>
                                <span><i class="ti ti-message-circle-2-filled text-white me-1"></i> ${m.comments_count}</span>
                            </div>
                        </div>
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-secondary-subtle text-secondary py-1 px-2.5 rounded-pill small fw-bold">${icon}</span>
                                    <span class="text-muted small"><i class="ti ti-clock me-1"></i>${formatTime(m.timestamp)}</span>
                                </div>
                                <p class="text-dark small mb-3" style="line-height:1.4;">${esc(caption)}</p>
                            </div>
                            <div>
                                ${m.permalink ? `<a href="${m.permalink}" target="_blank" class="btn btn-sm btn-outline-primary w-100 rounded-3 fw-bold"><i class="ti ti-external-link me-1"></i> Lihat di Instagram</a>` : ''}
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            container.html(html);
        });
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
                    const snippet = m.message_text.length > 40 ? m.message_text.substring(0, 40) + '...' : m.message_text;
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

    // ---- FETCH MEDIA API CALL ----
    function fetchMedia(igUserId) {
        alertify.message('📥 Mengambil profil & media terbaru dari Instagram...');
        $.getJSON(API_BASE + 'fetch_media?ig_user_id=' + igUserId, function(response) {
            if (response.success) {
                alertify.success(`✅ Sukses! Data profil & ${response.count} postingan disinkronkan.`);
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
        loadWebhookLogs();
        loadComments();
        loadMedia();
        loadMessages();
    }

    // ---- AUTO REFRESH TICKER ----
    function startAutoRefresh() {
        stopAutoRefresh();
        autoRefreshTimer = setInterval(function() {
            loadStats();
            loadWebhookLogs();
            loadComments();
            loadMessages();
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
        if (!str) return '';
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