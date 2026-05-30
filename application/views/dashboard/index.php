<!-- CSS Custom for Dashboard -->
<style>
    .stats-card-premium {
        border: none;
        border-radius: 20px;
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }

    .stats-card-premium:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08) !important;
    }

    .stats-card-premium .stat-bg-icon {
        position: absolute;
        right: -10px;
        bottom: -20px;
        font-size: 8rem;
        opacity: 0.06;
        pointer-events: none;
    }

    .tab-btn-premium {
        border: none;
        background: transparent;
        padding: 12px 24px;
        font-weight: 600;
        font-size: 1rem;
        color: #64748b;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .tab-btn-premium.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
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

    .code-block-premium {
        background: #1e293b;
        color: #f1f5f9;
        font-family: 'Courier New', Courier, monospace;
        padding: 20px;
        border-radius: 15px;
        font-size: 0.9rem;
        position: relative;
        margin-top: 15px;
        overflow-x: auto;
    }
</style>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-extrabold mb-1"><span style="color: var(--primary)">📸</span> Instagram API Dashboard</h2>
        <p class="text-muted mb-0">Monitor logs webhook, kelola akun, dan salin token akses Instagram.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <button class="btn btn-outline-primary py-2.5 px-4 rounded-3 me-2 fw-semibold" onclick="refreshAll()">
            <i class="ti ti-refresh me-1"></i> Refresh Data
        </button>
        <a href="<?php echo base_url('dashboard/instagram_login'); ?>" class="btn btn-primary py-2.5 px-4 rounded-3 fw-semibold" style="background: linear-gradient(45deg, #405de6, #e1306c, #f77737); border: none;">
            <i class="ti ti-link me-1"></i> Hubungkan Instagram
        </a>
    </div>
</div>

<!-- STATS GRID -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card stats-card-premium shadow-sm bg-white">
            <div class="card-body p-4">
                <span class="stat-bg-icon">👤</span>
                <span class="text-muted font-weight-medium d-block mb-2">Akun Terhubung</span>
                <h2 class="fw-bold mb-0 text-dark" id="statAccounts">-</h2>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card stats-card-premium shadow-sm bg-white">
            <div class="card-body p-4">
                <span class="stat-bg-icon">🔔</span>
                <span class="text-muted font-weight-medium d-block mb-2">Webhook Events</span>
                <h2 class="fw-bold mb-0 text-dark" id="statWebhooks">-</h2>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card stats-card-premium shadow-sm bg-white">
            <div class="card-body p-4">
                <span class="stat-bg-icon">💬</span>
                <span class="text-muted font-weight-medium d-block mb-2">Total Komentar</span>
                <h2 class="fw-bold mb-0 text-dark" id="statComments">-</h2>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card stats-card-premium shadow-sm bg-white">
            <div class="card-body p-4">
                <span class="stat-bg-icon">✉️</span>
                <span class="text-muted font-weight-medium d-block mb-2">Total Pesan / DM</span>
                <h2 class="fw-bold mb-0 text-dark" id="statMessages">-</h2>
            </div>
        </div>
    </div>
</div>

<!-- CONNECTED ACCOUNTS CARD -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-3"><i class="ti ti-users me-1 text-primary"></i> Akun Instagram Terhubung</h4>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">Username</th>
                        <th class="border-0">IG Account ID</th>
                        <th class="border-0">Token Expire</th>
                        <th class="border-0">Terakhir Update</th>
                        <th class="border-0 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="accountsTableBody">
                    <?php if (empty($accounts)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="mb-3" style="font-size: 2.5rem;">🔗</div>
                                <h6 class="fw-bold mb-1">Belum Ada Akun Instagram Terhubung</h6>
                                <p class="small text-muted mb-3">Integrasikan akun bisnis atau creator Instagram Anda sekarang.</p>
                                <a href="<?php echo base_url('dashboard/instagram_login'); ?>" class="btn btn-primary btn-sm py-2 px-3 rounded-3">Hubungkan Sekarang</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($accounts as $acc): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-light-primary rounded-circle p-2 text-primary fw-bold me-2 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                                            IG
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0">@<?= htmlspecialchars($acc['username'] ?? 'N/A') ?></h6>
                                            <span class="small text-muted"><?= htmlspecialchars($acc['name'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><code><?= htmlspecialchars($acc['ig_user_id']) ?></code></td>
                                <td>
                                    <?php
                                    $expires = $acc['expires_at'] ? strtotime($acc['expires_at']) : 0;
                                    $isExpired = $expires < time();
                                    ?>
                                    <span class="badge rounded-pill py-2 px-3 <?= $isExpired ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?>">
                                        <?= $isExpired ? 'EXPIRED' : date('d M Y', $expires) ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?= formatTimeCI($acc['updated_at']) ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary py-2 px-3 rounded-3 fw-bold me-2" onclick="fetchMedia('<?= $acc['ig_user_id'] ?>')">
                                        📥 Fetch Media
                                    </button>
                                    <button class="btn btn-sm btn-success py-2 px-3 rounded-3 fw-bold" onclick="copyToken('<?= htmlspecialchars($acc['access_token']) ?>', '<?= htmlspecialchars($acc['username']) ?>')">
                                        🔑 Salin Token
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TABS NAVIGATION -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <div class="d-flex border-bottom overflow-auto">
            <button class="tab-btn-premium active" onclick="switchTab('webhooks', this)">
                <span class="live-dot"></span> Webhook Logs
            </button>
            <button class="tab-btn-premium" onclick="switchTab('comments', this)">💬 Komentar</button>
            <button class="tab-btn-premium" onclick="switchTab('media', this)">📸 Media</button>
            <button class="tab-btn-premium" onclick="switchTab('messages', this)">✉️ Pesan (DM)</button>
            <button class="tab-btn-premium" onclick="switchTab('guide', this)">📖 Panduan Setup</button>
        </div>
    </div>

    <div class="card-body p-4">
        <!-- TAB: WEBHOOK LOGS -->
        <div class="tab-content-panel" id="tab-webhooks">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><span class="live-dot"></span> Real-time Webhook Logs</h5>
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
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody id="webhookLogsBody">
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Memuat...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: KOMENTAR -->
        <div class="tab-content-panel" id="tab-comments" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">💬 Komentar Terbaru</h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="loadComments()">🔄 Refresh</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Username</th>
                            <th>Isi Komentar</th>
                            <th>Media ID</th>
                            <th>Sumber</th>
                        </tr>
                    </thead>
                    <tbody id="commentsBody">
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Memuat...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: MEDIA -->
        <div class="tab-content-panel" id="tab-media" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">📸 Media / Postingan Terkini</h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="loadMedia()">🔄 Refresh</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Tipe</th>
                            <th>Caption</th>
                            <th>❤️ Likes</th>
                            <th>💬 Komentar</th>
                            <th>Link</th>
                        </tr>
                    </thead>
                    <tbody id="mediaBody">
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Memuat...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: PESAN -->
        <div class="tab-content-panel" id="tab-messages" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">✉️ Direct Messages (DM)</h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="loadMessages()">🔄 Refresh</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Pengirim ID</th>
                            <th>Isi Pesan</th>
                        </tr>
                    </thead>
                    <tbody id="messagesBody">
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Memuat...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: PANDUAN SETUP -->
        <div class="tab-content-panel" id="tab-guide" style="display:none;">
            <h4 class="fw-bold mb-3">📖 Panduan Setup Webhook & Instagram API</h4>
            <div class="alert alert-info border-dashed p-4 rounded-4 mb-4">
                <h6 class="fw-bold mb-2"><i class="ti ti-info-circle text-info me-1"></i> Syarat Utama:</h6>
                <ol class="mb-0 fs-3">
                    <li>Akun Instagram Anda harus berupa akun **Professional** (Creator atau Business).</li>
                    <li>Akun Instagram tersebut harus dihubungkan dengan salah satu **Facebook Page** yang Anda kelola.</li>
                    <li>Buat akun developer dan daftarkan aplikasi di **Meta Developer Portal**.</li>
                </ol>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-md-6">
                    <h5 class="fw-bold">Step 1: Konfigurasi di Meta Developers</h5>
                    <p class="text-muted">Buat aplikasi dengan tipe **Business**, aktifkan produk **Instagram Graph API** dan **Webhooks**.</p>
                </div>
                <div class="col-md-6">
                    <h5 class="fw-bold">Step 2: Hubungkan Webhook Endpoint</h5>
                    <p class="text-muted">Masukkan parameter URL callback di Meta Developers Portal Anda:</p>
                    <div class="code-block-premium">
                        <strong>Callback URL:</strong> <?= base_url('webhook.php') ?><br>
                        <strong>Verify Token:</strong> <?= WEBHOOK_VERIFY_TOKEN ?><br>
                        <strong>Object:</strong> instagram<br>
                        <strong>Fields to Subscribe:</strong> comments, messages, live_comments
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inline helper for formatting time inside table (PHP context)
function formatTimeCI($timeStr)
{
    if (!$timeStr) return '-';
    $time = strtotime($timeStr);
    return date('d M Y H:i:s', $time);
}
?>

<script>
    const API_BASE = "<?= base_url('dashboard/'); ?>";
    const REFRESH_INTERVAL = 10000;
    let autoRefreshTimer = null;
    let latestMessageId = null;

    $(document).ready(function() {
        loadStats();
        loadWebhookLogs();
        loadComments();
        loadMedia();
        loadMessages();

        startAutoRefresh();

        // Check if callback returned success toast parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'success') {
            const username = urlParams.get('username') ? '@' + decodeURIComponent(urlParams.get('username')) : 'Instagram';
            alertify.success(`✅ Akun ${username} berhasil terhubung!`);
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    // ---- TAB SYSTEM ----
    function switchTab(tabName, btn) {
        $('.tab-content-panel').hide();
        $('.tab-btn-premium').removeClass('active');

        $('#tab-' + tabName).show();
        $(btn).addClass('active');
    }

    // ---- FETCH STATS ----
    function loadStats() {
        $.getJSON(API_BASE + 'get_stats', function(response) {
            if (response.success) {
                const d = response.data;
                $('#statAccounts').text(d.total_accounts);
                $('#statWebhooks').text(d.total_webhook_events);
                $('#statComments').text(d.total_comments);
                $('#statMessages').text(d.total_messages);
            }
        });
    }

    // ---- FETCH WEBHOOK LOGS ----
    function loadWebhookLogs() {
        $.getJSON(API_BASE + 'get_webhook_logs?limit=30', function(response) {
            const tbody = $('#webhookLogsBody');
            if (!response.success || response.data.length === 0) {
                tbody.html(`<tr><td colspan="5" class="text-center text-muted py-4">Belum ada log webhook masuk.</td></tr>`);
                return;
            }

            let html = '';
            response.data.forEach(log => {
                let valStr = '';
                try {
                    const parsed = JSON.parse(log.value);
                    valStr = JSON.stringify(parsed);
                } catch (e) {
                    valStr = log.value;
                }
                if (valStr.length > 100) valStr = valStr.substring(0, 100) + '...';

                html += `<tr>
                    <td>${log.id}</td>
                    <td>${formatTime(log.created_at)}</td>
                    <td><span class="badge bg-secondary-subtle text-secondary py-1.5 px-3 rounded-pill">${esc(log.object || '-')}</span></td>
                    <td><span class="badge bg-primary-subtle text-primary py-1.5 px-3 rounded-pill">${esc(log.event_type || '-')}</span></td>
                    <td><code class="small" style="color: var(--primary)">${esc(valStr)}</code></td>
                </tr>`;
            });
            tbody.html(html);
        });
    }

    // ---- FETCH COMMENTS ----
    function loadComments() {
        $.getJSON(API_BASE + 'get_comments?limit=30', function(response) {
            const tbody = $('#commentsBody');
            if (!response.success || response.data.length === 0) {
                tbody.html(`<tr><td colspan="5" class="text-center text-muted py-4">Belum ada komentar terekam.</td></tr>`);
                return;
            }

            let html = '';
            response.data.forEach(c => {
                html += `<tr>
                    <td>${formatTime(c.created_at)}</td>
                    <td><strong>@${esc(c.from_username || 'unknown')}</strong></td>
                    <td>${esc(c.text)}</td>
                    <td><code>${esc(c.media_id)}</code></td>
                    <td><span class="badge ${c.is_from_webhook == 1 ? 'bg-success-subtle text-success' : 'bg-info-subtle text-info'} py-1.5 px-3 rounded-pill">
                        ${c.is_from_webhook == 1 ? 'Webhook' : 'API Pull'}
                    </span></td>
                </tr>`;
            });
            tbody.html(html);
        });
    }

    // ---- FETCH MEDIA ----
    function loadMedia() {
        $.getJSON(API_BASE + 'get_media?limit=20', function(response) {
            const tbody = $('#mediaBody');
            if (!response.success || response.data.length === 0) {
                tbody.html(`<tr><td colspan="6" class="text-center text-muted py-4">Belum ada media postingan. Silakan klik "Fetch Media" terlebih dahulu.</td></tr>`);
                return;
            }

            let html = '';
            response.data.forEach(m => {
                const icon = m.media_type === 'IMAGE' ? '🖼️' : (m.media_type === 'VIDEO' ? '🎬' : '🎠');
                const caption = m.caption ? (m.caption.length > 70 ? m.caption.substring(0, 70) + '...' : m.caption) : '-';

                html += `<tr>
                    <td>${formatTime(m.timestamp)}</td>
                    <td>${icon} ${esc(m.media_type)}</td>
                    <td>${esc(caption)}</td>
                    <td>❤️ ${m.like_count}</td>
                    <td>💬 ${m.comments_count}</td>
                    <td>
                        ${m.permalink ? `<a href="${m.permalink}" target="_blank" class="btn btn-xs btn-outline-primary py-1 px-2.5 rounded-2 small fw-bold">Open Link</a>` : '-'}
                    </td>
                </tr>`;
            });
            tbody.html(html);
        });
    }

    // ---- FETCH MESSAGES ----
    function loadMessages() {
        $.getJSON(API_BASE + 'get_messages?limit=30', function(response) {
            const tbody = $('#messagesBody');
            if (!response.success || response.data.length === 0) {
                tbody.html(`<tr><td colspan="3" class="text-center text-muted py-4">Belum ada pesan terekam.</td></tr>`);
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
            response.data.forEach(m => {
                html += `<tr>
                    <td>${formatTime(m.created_at)}</td>
                    <td><code>${esc(m.sender_id)}</code></td>
                    <td>${esc(m.message_text)}</td>
                </tr>`;
            });
            tbody.html(html);
        });
    }

    // ---- FETCH MEDIA API CALL ----
    function fetchMedia(igUserId) {
        alertify.message('📥 Mengambil media dari Instagram...');
        $.getJSON(API_BASE + 'fetch_media?ig_user_id=' + igUserId, function(response) {
            if (response.success) {
                alertify.success(`✅ Sukses! ${response.count} postingan diambil.`);
                loadMedia();
                loadStats();
            } else {
                alertify.error(`❌ Gagal: ${response.error}`);
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
        alertify.success('🔄 Semua data diperbarui!');
    }

    // ---- AUTO REFRESH TICKER ----
    function startAutoRefresh() {
        autoRefreshTimer = setInterval(function() {
            loadStats();
            loadWebhookLogs();
            loadComments();
            loadMessages();
        }, REFRESH_INTERVAL);
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
</script>