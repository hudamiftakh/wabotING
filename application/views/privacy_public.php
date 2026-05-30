<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi - WabotING</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #3f3d56;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            width: 100%;
        }
        h1 {
            color: #4f46e5;
            font-size: 2.2rem;
            margin-top: 0;
            margin-bottom: 5px;
            font-weight: 700;
        }
        h2 {
            color: #1e1b4b;
            font-size: 1.3rem;
            margin-top: 30px;
            margin-bottom: 12px;
            font-weight: 600;
        }
        p {
            margin-bottom: 15px;
            color: #4b5563;
        }
        ul {
            margin-bottom: 15px;
            padding-left: 20px;
        }
        li {
            margin-bottom: 8px;
            color: #4b5563;
        }
        .date {
            color: #9ca3af;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #f3f4f6;
            text-align: center;
            font-size: 0.9em;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kebijakan Privasi</h1>
        <div class="date"><strong>Terakhir Diperbarui:</strong> <?php echo date('d F Y'); ?></div>

        <p>Selamat datang di <strong>WabotING</strong>. Privasi Anda sangat penting bagi kami. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda ketika Anda menggunakan aplikasi kami yang terhubung dengan Meta/Instagram API.</p>

        <h2>1. Informasi yang Kami Kumpulkan</h2>
        <p>Saat Anda menghubungkan akun Instagram Anda ke layanan kami, kami menerima akses ke informasi berikut melalui Instagram Graph API:</p>
        <ul>
            <li><strong>Profil Dasar:</strong> User ID Instagram, Username, Nama, dan Foto Profil.</li>
            <li><strong>Data Interaksi:</strong> Pesan langsung (Direct Messages) dan Komentar pada postingan Anda, jika Anda memberikan izin akses tersebut.</li>
            <li><strong>Akses Token:</strong> Token akses sementara atau jangka panjang yang dienkripsi secara aman untuk menjaga koneksi dengan API Instagram.</li>
        </ul>

        <h2>2. Bagaimana Kami Menggunakan Informasi Anda</h2>
        <p>Informasi yang dikumpulkan hanya digunakan untuk tujuan fungsionalitas aplikasi, yaitu:</p>
        <ul>
            <li>Menampilkan profil Anda di dashboard aplikasi kami.</li>
            <li>Membaca, memproses, dan membalas pesan serta komentar Instagram Anda secara otomatis (sesuai konfigurasi Anda).</li>
            <li>Memastikan layanan webhook berjalan dengan lancar tanpa gangguan.</li>
        </ul>
        <p>Kami <strong>TIDAK PERNAH</strong> menjual, menyewakan, atau membagikan data pribadi Anda kepada pihak ketiga mana pun.</p>

        <h2>3. Penyimpanan dan Keamanan Data</h2>
        <p>Data Anda disimpan secara aman di dalam database hosting kami. Token akses API dijaga dengan enkripsi standar industri. Kami secara berkala melakukan pembersihan data sementara (seperti log aktivitas webhook).</p>

        <h2>4. Permintaan Penghapusan Data (Data Deletion)</h2>
        <p>Sesuai dengan ketentuan Meta dan hak privasi Anda, Anda berhak kapan saja meminta agar seluruh data Anda dihapus dari sistem kami. Jika Anda ingin mencabut akses dan menghapus data Anda:</p>
        <ul>
            <li>Hapus akses aplikasi kami melalui menu <strong>Pengaturan > Aplikasi dan Situs Web</strong> di akun Instagram Anda.</li>
            <li>Hubungi kami melalui email di bawah ini dengan subjek "Permintaan Penghapusan Data", dan sertakan Username Instagram Anda. Kami akan menghapus seluruh data Anda (termasuk Token dan riwayat) dalam waktu maksimal 2x24 jam.</li>
        </ul>

        <h2>5. Perubahan Kebijakan Privasi</h2>
        <p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Setiap perubahan akan diumumkan di halaman ini bersama dengan tanggal pembaruan terbaru.</p>

        <h2>6. Kontak Kami</h2>
        <p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini atau ingin melakukan penghapusan data, silakan hubungi kami di:</p>
        <p><strong>Email:</strong> hudamiftakh8@gmail.com</p>
        
        <div class="footer">
            &copy; <?php echo date('Y'); ?> WabotING. All rights reserved.
        </div>
    </div>
</body>
</html>
