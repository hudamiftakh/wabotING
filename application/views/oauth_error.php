<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram OAuth Error</title>
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/wabot.png') ?>" />
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background: #f4f7f6;
            color: #1f2937;
        }
        .box {
            width: min(680px, calc(100% - 32px));
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        }
        h1 {
            margin: 0 0 10px;
            font-size: 24px;
            color: #b91c1c;
        }
        p {
            line-height: 1.6;
            margin: 0 0 18px;
        }
        .debug {
            background: #111827;
            color: #d1d5db;
            border-radius: 8px;
            padding: 14px;
            max-height: 260px;
            overflow: auto;
            font-size: 12px;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        a {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
        }
        .primary {
            background: #6b46c1;
            color: #fff;
        }
        .secondary {
            background: #eef2ff;
            color: #4338ca;
        }
        .muted {
            color: #6b7280;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <main class="box">
        <h1><?= htmlspecialchars($title ?? 'OAuth Error') ?></h1>
        <p><?= htmlspecialchars($msg ?? 'Terjadi kesalahan saat menghubungkan Instagram.') ?></p>
        <p class="muted">Redirect URI aktif: <?= htmlspecialchars($redirect_uri ?? '-') ?></p>
        <?php if (!empty($debug)): ?>
            <div class="debug"><?= htmlspecialchars($debug) ?></div>
        <?php endif; ?>
        <div class="actions">
            <a class="primary" href="<?= base_url('dashboard/instagram_login') ?>">Coba Hubungkan Ulang</a>
            <a class="secondary" href="<?= base_url('dashboard') ?>">Kembali ke Dashboard</a>
        </div>
    </main>
</body>
</html>
