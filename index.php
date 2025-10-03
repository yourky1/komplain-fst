<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Komplain FST - Beranda</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-university"></i>
                <span>Komplain FST</span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Beranda</a></li>
                <li><a href="#tentang">Tentang</a></li>
                <li><a href="#layanan">Layanan</a></li>
                <li><a href="#kontak">Kontak</a></li>
                <?php if(isLoggedIn()): ?>
                    <li><a href="dashboard.php" class="btn-nav">Dashboard</a></li>
                    <li><a href="logout.php" class="btn-nav-outline">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-nav">Login</a></li>
                    <li><a href="register.php" class="btn-nav-outline">Register</a></li>
                <?php endif; ?>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1 class="hero-title">Sistem Informasi Layanan Penanganan Komplain Mahasiswa</h1>
            <p class="hero-subtitle">Fakultas Sains dan Teknologi - Sampaikan keluhan Anda dengan mudah dan cepat</p>
            <div class="hero-buttons">
                <?php if(isLoggedIn()): ?>
                    <a href="dashboard.php" class="btn-primary">Dashboard</a>
                    <a href="komplain-baru.php" class="btn-secondary">Buat Komplain</a>
                <?php else: ?>
                    <a href="register.php" class="btn-primary">Mulai Sekarang</a>
                    <a href="login.php" class="btn-secondary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="stat-number">
                        <?php
                        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain");
                        $data = mysqli_fetch_assoc($result);
                        echo $data['total'];
                        ?>
                    </h3>
                    <p class="stat-label">Total Komplain</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="stat-number">
                        <?php
                        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE status='diproses'");
                        $data = mysqli_fetch_assoc($result);
                        echo $data['total'];
                        ?>
                    </h3>
                    <p class="stat-label">Sedang Diproses</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="stat-number">
                        <?php
                        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE status='selesai'");
                        $data = mysqli_fetch_assoc($result);
                        echo $data['total'];
                        ?>
                    </h3>
                    <p class="stat-label">Telah Diselesaikan</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="stat-number">
                        <?php
                        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='mahasiswa'");
                        $data = mysqli_fetch_assoc($result);
                        echo $data['total'];
                        ?>
                    </h3>
                    <p class="stat-label">Mahasiswa Terdaftar</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="tentang" class="about">
        <div class="container">
            <div class="section-header">
                <h2>Tentang Sistem</h2>
                <p>Platform digital untuk menyampaikan dan mengelola komplain mahasiswa FST</p>
            </div>
            <div class="about-grid">
                <div class="about-card">
                    <div class="about-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Cepat & Efisien</h3>
                    <p>Proses pengajuan komplain yang mudah dan respon yang cepat dari pihak fakultas</p>
                </div>
                <div class="about-card">
                    <div class="about-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Aman & Terpercaya</h3>
                    <p>Data Anda dijamin aman dengan sistem keamanan berlapis dan terenkripsi</p>
                </div>
                <div class="about-card">
                    <div class="about-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Tracking Real-time</h3>
                    <p>Pantau status komplain Anda secara real-time dengan notifikasi otomatis</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="layanan" class="services">
        <div class="container">
            <div class="section-header">
                <h2>Kategori Layanan</h2>
                <p>Berbagai kategori komplain yang dapat Anda sampaikan</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Akademik</h3>
                    <p>Perkuliahan, jadwal, nilai, kurikulum</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-building"></i>
                    <h3>Fasilitas</h3>
                    <p>Ruang kelas, lab, perpustakaan</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-file-invoice"></i>
                    <h3>Administrasi</h3>
                    <p>KRS, KHS, surat, legalisir</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3>Dosen</h3>
                    <p>Pengajaran, bimbingan, konsultasi</p>
                </div>
                <div class="service-card">
                    <i class="fas fa-ellipsis-h"></i>
                    <h3>Lainnya</h3>
                    <p>Komplain di luar kategori utama</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="kontak" class="contact">
        <div class="container">
            <div class="section-header">
                <h2>Hubungi Kami</h2>
                <p>Kami siap membantu Anda</p>
            </div>
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Alamat</h4>
                            <p>Jl.Fakultas Sains dan Teknologi,<br>UIN Walisongo,Semarang</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Telepon</h4>
                            <p>+62 21 1234 5678</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p>komplain@fst.ac.id</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h4>Jam Operasional</h4>
                            <p>Senin - Jumat: 08:00 - 16:00 WIB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="map-section">
        <h2 style="display:flex; justify-content:center; margin:20px 0;">Lokasi FST</h2>
        <div style="display:flex; justify-content:center; margin:20px 0;">
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1980.0738540686768!2d110.34521458230289!3d-6.991877817321884!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e708b52604b5545%3A0x56e11e4c32355475!2sFakultas%20Sains%20dan%20Teknologi%20Kampus%203%20UIN!5e0!3m2!1sid!2sid!4v1759332645914!5m2!1sid!2sid" 
                width="300" 
                height="250" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-university"></i> Komplain FST</h3>
                    <p>Sistem Informasi Layanan Penanganan Komplain Mahasiswa Fakultas Sains dan Teknologi</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="#tentang">Tentang</a></li>
                        <li><a href="#layanan">Layanan</a></li>
                        <li><a href="#kontak">Kontak</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Fakultas Sains dan Teknologi. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>