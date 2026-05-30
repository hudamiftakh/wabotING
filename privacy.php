<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi - WabotING</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #2c3e50;
        }
        h1 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            margin-top: 30px;
        }
        p {
            margin-bottom: 15px;
        }
        ul {
            margin-bottom: 15px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 0.9em;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kebijakan Privasi</h1>
        <p><strong>Terakhir Diperbarui:</strong> <?php echo date('d F Y'); ?></p>

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
