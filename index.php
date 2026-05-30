<?php
/**
 * =============================================
 * DASHBOARD UTAMA - INSTAGRAM API
 * =============================================
 * Halaman ini menampilkan:
 * - Status koneksi akun Instagram
 * - Webhook logs real-time  
 * - Komentar terbaru
 * - Media/Post
 * - Panduan setup
 */
require_once __DIR__ . '/config.php';
$db = getDB();

// Cek akun yang terhubung
$accounts = $db->query("SELECT * FROM access_tokens ORDER BY updated_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram API Dashboard</title>
    <meta name="description" content="Dashboard monitoring Instagram API dengan Webhook real-time">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h1>📸 Instagram API Dashboard</h1>
        <div class="header-actions">
            <button class="btn btn-outline btn-sm" onclick="refreshAll()">🔄 Refresh</button>
            <a href="auth.php" class="btn btn-ig btn-sm">🔗 Hubungkan Instagram</a>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card purple">
            <div class="stat-icon">👤</div>
            <div class="stat-value" id="statAccounts">-</div>
            <div class="stat-label">Akun Terhubung</div>
        </div>
        <div class="stat-card pink">
            <div class="stat-icon">🔔</div>
            <div class="stat-value" id="statWebhooks">-</div>
            <div class="stat-label">Webhook Events</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon">💬</div>
            <div class="stat-value" id="statComments">-</div>
            <div class="stat-label">Komentar</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon">✉️</div>
            <div class="stat-value" id="statMessages">-</div>
            <div class="stat-label">Pesan</div>
        </div>
    </div>

    <!-- AKUN TERHUBUNG -->
    <div class="card grid-full">
        <div class="section-header">
            <h2>👤 Akun Instagram Terhubung</h2>
        </div>
        <?php if (empty($accounts)): ?>
        <div class="empty-state">
            <div class="empty-icon">🔗</div>
            <p>Belum ada akun Instagram yang terhubung.</p>
            <a href="auth.php" class="btn btn-ig">Hubungkan Sekarang</a>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>IG Account ID</th>
                        <th>Page ID</th>
                        <th>Token Expires</th>
                        <th>Updated</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($accounts as $acc): ?>
                    <tr>
                        <td><strong>@<?= htmlspecialchars($acc['username'] ?? 'N/A') ?></strong></td>
                        <td><code><?= htmlspecialchars($acc['ig_user_id']) ?></code></td>
                        <td><code><?= htmlspecialchars($acc['page_id'] ?? '-') ?></code></td>
                        <td>
                            <?php 
                                $expires = $acc['expires_at'] ? strtotime($acc['expires_at']) : 0;
                                $isExpired = $expires < time();
                            ?>
                            <span class="badge <?= $isExpired ? 'badge-red' : 'badge-green' ?>">
                                <?= $isExpired ? 'EXPIRED' : date('d M Y', $expires) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($acc['updated_at']) ?></td>
                        <td style="display: flex; gap: 8px;">
                            <button class="btn btn-sm btn-outline" onclick="fetchMedia('<?= $acc['ig_user_id'] ?>')">
                                📥 Fetch Media
                            </button>
                            <button class="btn btn-sm btn-outline" style="color: var(--success); border-color: var(--success);" onclick="copyToClipboard('<?= htmlspecialchars($acc['access_token']) ?>', 'Token @<?= htmlspecialchars($acc['username']) ?>')">
                                🔑 Salin Token
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- TABS -->
    <div class="tabs">
        <button class="tab active" onclick="switchTab('webhooks', this)">
            <span class="live-dot"></span> Webhook Logs
        </button>
        <button class="tab" onclick="switchTab('comments', this)">💬 Komentar</button>
        <button class="tab" onclick="switchTab('media', this)">📸 Media</button>
        <button class="tab" onclick="switchTab('messages', this)">✉️ Pesan</button>
        <button class="tab" onclick="switchTab('guide', this)">📖 Panduan Setup</button>
    </div>

    <!-- TAB: WEBHOOK LOGS -->
    <div class="tab-content" id="tab-webhooks">
        <div class="card">
            <div class="section-header">
                <h2><span class="live-dot"></span> Webhook Logs (Real-time)</h2>
                <button class="btn btn-sm btn-outline" onclick="loadWebhookLogs()">🔄 Refresh</button>
            </div>
            <p style="color: var(--text-muted); margin-bottom: 12px; font-size: 0.85rem;">
                Setiap event dari Instagram (komentar, pesan, dll) akan muncul di sini secara real-time.
                Data diambil setiap <strong>10 detik</strong> otomatis.
            </p>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Waktu</th>
                            <th>Object</th>
                            <th>Event</th>
                            <th>Entry ID</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody id="webhookLogsBody">
                        <tr><td colspan="6" style="text-align:center; color: var(--text-muted);">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: KOMENTAR -->
    <div class="tab-content" id="tab-comments" style="display:none;">
        <div class="card">
            <div class="section-header">
                <h2>💬 Komentar Terbaru</h2>
                <button class="btn btn-sm btn-outline" onclick="loadComments()">🔄 Refresh</button>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Username</th>
                            <th>Komentar</th>
                            <th>Media ID</th>
                            <th>Sumber</th>
                        </tr>
                    </thead>
                    <tbody id="commentsBody">
                        <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: MEDIA -->
    <div class="tab-content" id="tab-media" style="display:none;">
        <div class="card">
            <div class="section-header">
                <h2>📸 Media / Posts</h2>
                <button class="btn btn-sm btn-outline" onclick="loadMedia()">🔄 Refresh</button>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Tipe</th>
                            <th>Caption</th>
                            <th>❤️ Likes</th>
                            <th>💬 Comments</th>
                            <th>Link</th>
                        </tr>
                    </thead>
                    <tbody id="mediaBody">
                        <tr><td colspan="6" style="text-align:center; color: var(--text-muted);">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: PESAN -->
    <div class="tab-content" id="tab-messages" style="display:none;">
        <div class="card">
            <div class="section-header">
                <h2>✉️ Pesan / DM</h2>
                <button class="btn btn-sm btn-outline" onclick="loadMessages()">🔄 Refresh</button>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Sender</th>
                            <th>Pesan</th>
                        </tr>
                    </thead>
                    <tbody id="messagesBody">
                        <tr><td colspan="3" style="text-align:center; color: var(--text-muted);">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: PANDUAN SETUP -->
    <div class="tab-content" id="tab-guide" style="display:none;">
        <div class="card">
            <h2>📖 Panduan Setup Instagram API + Webhook</h2>
            
            <div class="alert alert-info">
                <strong>ℹ️ Prerequisite:</strong>
                <p>1. Akun Instagram harus <strong>Business</strong> atau <strong>Creator</strong> (bukan Personal)</p>
                <p>2. Instagram harus terhubung ke <strong>Facebook Page</strong></p>
                <p>3. Aplikasi harus dibuat di <strong>Meta Developer Dashboard</strong></p>
            </div>

            <div class="flow-steps">
                <div class="flow-step">
                    <div class="step-num">1</div>
                    <h4>Buat Aplikasi</h4>
                    <p>Buka <a href="https://developers.facebook.com" target="_blank" style="color:var(--accent)">developers.facebook.com</a> → Buat aplikasi baru → Pilih "Business"</p>
                </div>
                <div class="flow-step">
                    <div class="step-num">2</div>
                    <h4>Setup Instagram API</h4>
                    <p>Di dashboard aplikasi → Tambah produk "Instagram Graph API" → Konfigurasi</p>
                </div>
                <div class="flow-step">
                    <div class="step-num">3</div>
                    <h4>Setup Webhook</h4>
                    <p>Tambah produk "Webhooks" → Set callback URL ke <code>webhook.php</code> kamu</p>
                </div>
                <div class="flow-step">
                    <div class="step-num">4</div>
                    <h4>Subscribe Fields</h4>
                    <p>Subscribe ke field: <code>comments</code>, <code>messages</code>, <code>live_comments</code></p>
                </div>
            </div>

            <h3 style="margin-top: 30px;">🔧 Konfigurasi Webhook di Meta Dashboard</h3>
            <div class="code-block">
// URL Webhook (harus HTTPS & publik):
Callback URL: https://DOMAIN_KAMU/meta_api/webhook.php

// Verify Token (harus sama dengan di config.php):
Verify Token: <?= WEBHOOK_VERIFY_TOKEN ?>

// Fields yang perlu di-subscribe untuk object "instagram":
✅ comments      → Notifikasi komentar baru
✅ live_comments  → Notifikasi komentar di live
✅ messages       → Notifikasi DM masuk
✅ messaging_seen → Notifikasi pesan dibaca
            </div>

            <h3 style="margin-top: 30px;">🌐 Testing dengan Ngrok (Localhost)</h3>
            <div class="code-block">
// 1. Install ngrok: https://ngrok.com
// 2. Jalankan di terminal:
ngrok http 80

// 3. Dapat URL seperti:
https://abc123.ngrok-free.app

// 4. Gunakan URL tersebut di Meta Dashboard:
Callback URL: https://abc123.ngrok-free.app/meta_api/webhook.php

// 5. Jangan lupa update IG_REDIRECT_URI di config.php:
IG_REDIRECT_URI: https://abc123.ngrok-free.app/meta_api/auth.php
            </div>

            <h3 style="margin-top: 30px;">📂 Struktur File</h3>
            <div class="code-block">
meta_api/
├── index.php          → Dashboard ini (tampilan utama)
├── config.php         → Konfigurasi DB, API keys, helper functions
├── webhook.php        → Endpoint webhook (terima notifikasi dari Meta)
├── auth.php           → OAuth flow (login Instagram & dapatkan token)
├── api_endpoint.php   → AJAX endpoint (dipanggil dashboard via JS)
├── db.sql             → Schema database (jalankan di phpMyAdmin)
├── assets/
│   └── style.css      → Styling dashboard
└── logs/
    └── app_*.log      → Log file untuk debugging
            </div>

            <h3 style="margin-top: 30px;">🔄 Alur Data</h3>
            <div class="code-block">
┌─────────────────────────────────────────────────────┐
│                    ALUR WEBHOOK                      │
├─────────────────────────────────────────────────────┤
│                                                      │
│  User komentar    Meta/Instagram     webhook.php     │
│  di Instagram  →  kirim POST ke  →  terima &        │
│                   webhook URL       simpan ke DB     │
│                                                      │
│  Dashboard        api_endpoint.php   Database        │
│  (index.php)  →   query data     ←  (MySQL)         │
│                                                      │
├─────────────────────────────────────────────────────┤
│                    ALUR OAUTH                        │
├─────────────────────────────────────────────────────┤
│                                                      │
│  User klik      Redirect ke        User login &     │
│  "Hubungkan" →  Facebook Login  →  izinkan akses    │
│                                                      │
│  Redirect ke    Tukar code →       Simpan token     │
│  auth.php    →  access token    →  ke database      │
│                                                      │
└─────────────────────────────────────────────────────┘
            </div>
        </div>
    </div>

</div>

<!-- TOAST NOTIFICATION -->
<div class="toast" id="toast"></div>

<script>
/**
 * =============================================
 * JAVASCRIPT - Dashboard Logic
 * =============================================
 */

// Auto-refresh interval (10 detik)
const REFRESH_INTERVAL = 10000;
let autoRefreshTimer = null;

// ---- INIT: Load semua data saat halaman dibuka ----
document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadWebhookLogs();
    loadComments();
    loadMedia();
    loadMessages();
    
    // Auto-refresh webhook logs setiap 10 detik
    startAutoRefresh();

    // Cek status sukses dari URL callback
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'success') {
        const username = urlParams.get('username') ? '@' + decodeURIComponent(urlParams.get('username')) : 'Instagram';
        showToast(`✅ Akun ${username} berhasil terhubung!`);
        // Bersihkan query parameters dari URL agar tidak memicu toast lagi saat di-refresh
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

// ---- TAB SWITCHING ----
function switchTab(tabName, btn) {
    // Hide semua tab content
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    // Remove active dari semua tab button
    document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
    
    // Show tab yang dipilih
    document.getElementById('tab-' + tabName).style.display = 'block';
    btn.classList.add('active');
}

// ---- LOAD STATS ----
async function loadStats() {
    try {
        const res = await fetch('api_endpoint.php?action=get_stats');
        const json = await res.json();
        if (json.success) {
            const d = json.data;
            document.getElementById('statAccounts').textContent = d.total_accounts;
            document.getElementById('statWebhooks').textContent = d.total_webhook_events;
            document.getElementById('statComments').textContent = d.total_comments;
            document.getElementById('statMessages').textContent = d.total_messages;
        }
    } catch (e) {
        console.error('Load stats error:', e);
    }
}

// ---- LOAD WEBHOOK LOGS ----
async function loadWebhookLogs() {
    try {
        const res = await fetch('api_endpoint.php?action=get_webhook_logs&limit=30');
        const json = await res.json();
        const tbody = document.getElementById('webhookLogsBody');
        
        if (!json.success || json.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="empty-state">
                <div class="empty-icon">📭</div>
                <p>Belum ada webhook event. Event akan muncul di sini saat ada komentar/pesan baru di Instagram.</p>
            </td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map((log, i) => {
            let valuePreview = '-';
            try {
                const val = typeof log.value === 'string' ? JSON.parse(log.value) : log.value;
                valuePreview = JSON.stringify(val, null, 0).substring(0, 120);
                if (valuePreview.length >= 120) valuePreview += '...';
            } catch(e) { valuePreview = String(log.value).substring(0, 120); }

            const badgeClass = {
                'comments': 'badge-green',
                'messages': 'badge-blue', 
                'messaging': 'badge-blue',
                'live_comments': 'badge-purple',
            }[log.event_type] || 'badge-yellow';

            return `<tr>
                <td>${log.id}</td>
                <td style="white-space:nowrap">${formatTime(log.created_at)}</td>
                <td><span class="badge badge-purple">${esc(log.object || '-')}</span></td>
                <td><span class="badge ${badgeClass}">${esc(log.event_type || log.field || '-')}</span></td>
                <td><code style="font-size:0.8rem">${esc(log.entry_id || '-')}</code></td>
                <td><code style="font-size:0.75rem; color:#a5b4fc">${esc(valuePreview)}</code></td>
            </tr>`;
        }).join('');
    } catch (e) {
        console.error('Load webhook logs error:', e);
    }
}

// ---- LOAD COMMENTS ----
async function loadComments() {
    try {
        const res = await fetch('api_endpoint.php?action=get_comments&limit=30');
        const json = await res.json();
        const tbody = document.getElementById('commentsBody');
        
        if (!json.success || json.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="empty-state">
                <div class="empty-icon">💬</div>
                <p>Belum ada komentar. Komentar akan muncul setelah webhook menerima event atau fetch manual.</p>
            </td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map(c => `<tr>
            <td style="white-space:nowrap">${formatTime(c.created_at)}</td>
            <td><strong>@${esc(c.from_username || 'unknown')}</strong></td>
            <td>${esc(c.text || '')}</td>
            <td><code style="font-size:0.8rem">${esc(c.media_id || '-')}</code></td>
            <td><span class="badge ${c.is_from_webhook == 1 ? 'badge-green' : 'badge-blue'}">
                ${c.is_from_webhook == 1 ? 'Webhook' : 'API Pull'}
            </span></td>
        </tr>`).join('');
    } catch (e) {
        console.error('Load comments error:', e);
    }
}

// ---- LOAD MEDIA ----
async function loadMedia() {
    try {
        const res = await fetch('api_endpoint.php?action=get_media&limit=20');
        const json = await res.json();
        const tbody = document.getElementById('mediaBody');
        
        if (!json.success || json.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="empty-state">
                <div class="empty-icon">📸</div>
                <p>Belum ada media. Klik "Fetch Media" di tabel akun untuk mengambil data post dari Instagram.</p>
            </td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map(m => {
            const typeIcon = { 'IMAGE': '🖼️', 'VIDEO': '🎬', 'CAROUSEL_ALBUM': '🎠' }[m.media_type] || '📄';
            const caption = (m.caption || '').substring(0, 80);
            return `<tr>
                <td style="white-space:nowrap">${formatTime(m.timestamp)}</td>
                <td>${typeIcon} ${esc(m.media_type || '-')}</td>
                <td>${esc(caption)}${(m.caption || '').length > 80 ? '...' : ''}</td>
                <td>❤️ ${m.like_count || 0}</td>
                <td>💬 ${m.comments_count || 0}</td>
                <td>${m.permalink ? `<a href="${esc(m.permalink)}" target="_blank" class="btn btn-sm btn-outline">Buka</a>` : '-'}</td>
            </tr>`;
        }).join('');
    } catch (e) {
        console.error('Load media error:', e);
    }
}

// ---- LOAD MESSAGES ----
async function loadMessages() {
    try {
        const res = await fetch('api_endpoint.php?action=get_messages&limit=30');
        const json = await res.json();
        const tbody = document.getElementById('messagesBody');
        
        if (!json.success || json.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" class="empty-state">
                <div class="empty-icon">✉️</div>
                <p>Belum ada pesan. Pesan akan muncul saat ada DM masuk via webhook.</p>
            </td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map(m => `<tr>
            <td style="white-space:nowrap">${formatTime(m.created_at)}</td>
            <td><code>${esc(m.sender_id || '-')}</code></td>
            <td>${esc(m.message_text || '')}</td>
        </tr>`).join('');
    } catch (e) {
        console.error('Load messages error:', e);
    }
}

// ---- FETCH MEDIA FROM INSTAGRAM API ----
async function fetchMedia(igUserId) {
    showToast('📥 Mengambil media dari Instagram...');
    try {
        const res = await fetch(`api_endpoint.php?action=fetch_media&ig_user_id=${igUserId}`);
        const json = await res.json();
        if (json.success) {
            showToast(`✅ Berhasil! ${json.count} media diambil.`);
            loadMedia();
            loadStats();
        } else {
            showToast(`❌ Error: ${json.error}`);
        }
    } catch (e) {
        showToast('❌ Gagal mengambil media: ' + e.message);
    }
}

// ---- REFRESH ALL DATA ----
function refreshAll() {
    loadStats();
    loadWebhookLogs();
    loadComments();
    loadMedia();
    loadMessages();
    showToast('🔄 Data diperbarui!');
}

// ---- AUTO REFRESH ----
function startAutoRefresh() {
    autoRefreshTimer = setInterval(() => {
        loadStats();
        loadWebhookLogs();
        loadComments();
    }, REFRESH_INTERVAL);
}

// ---- HELPER FUNCTIONS ----
function esc(str) {
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

function formatTime(dateStr) {
    if (!dateStr || dateStr === '-') return '-';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) + ' ' +
           d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}

function copyToClipboard(text, label = 'Token') {
    navigator.clipboard.writeText(text).then(() => {
        showToast(`📋 ${label} berhasil disalin ke clipboard!`);
    }).catch(err => {
        showToast('❌ Gagal menyalin token: ' + err);
    });
}

function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}
</script>

</body>
</html>
