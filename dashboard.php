<?php
require_once 'config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get statistics
if($role == 'admin') {
    $total_komplain = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain"))['total'];
    $pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE status='pending'"))['total'];
    $diproses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE status='diproses'"))['total'];
    $selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE status='selesai'"))['total'];
} else {
    $total_komplain = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE user_id=$user_id"))['total'];
    $pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE user_id=$user_id AND status='pending'"))['total'];
    $diproses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE user_id=$user_id AND status='diproses'"))['total'];
    $selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE user_id=$user_id AND status='selesai'"))['total'];
}

// Get recent komplain
if($role == 'admin') {
    $query_komplain = "SELECT k.*, u.nama, u.nim 
                       FROM komplain k 
                       JOIN users u ON k.user_id = u.id 
                       ORDER BY k.tanggal_submit DESC 
                       LIMIT 10";
} else {
    $query_komplain = "SELECT k.*, u.nama, u.nim 
                       FROM komplain k 
                       JOIN users u ON k.user_id = u.id 
                       WHERE k.user_id = $user_id 
                       ORDER BY k.tanggal_submit DESC 
                       LIMIT 10";
}
$result_komplain = mysqli_query($conn, $query_komplain);

// Get unread notifications
$notif_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM notifikasi WHERE user_id=$user_id AND is_read=0"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Komplain FST</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-university"></i>
                <span>Komplain FST</span>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="notifikasi.php">
                    <i class="fas fa-bell"></i> Notifikasi 
                    <?php if($notif_count > 0): ?>
                        <span class="badge badge-danger" style="font-size: 0.7rem;"><?php echo $notif_count; ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="profil.php"><i class="fas fa-user"></i> <?php echo $_SESSION['nama']; ?></a></li>
                <li><a href="logout.php" class="btn-nav-outline">Logout</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <section class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <div>
                    <h1>Dashboard <?php echo $role == 'admin' ? 'Admin' : 'Mahasiswa'; ?></h1>
                    <p>Selamat datang, <?php echo $_SESSION['nama']; ?>!</p>
                </div>
                <?php if($role == 'mahasiswa'): ?>
                <a href="komplain-baru.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Buat Komplain Baru
                </a>
                <?php endif; ?>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                    <h3 class="stat-number"><?php echo $total_komplain; ?></h3>
                    <p class="stat-label">Total Komplain</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <h3 class="stat-number"><?php echo $pending; ?></h3>
                    <p class="stat-label">Pending</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                    <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                    <h3 class="stat-number"><?php echo $diproses; ?></h3>
                    <p class="stat-label">Diproses</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <h3 class="stat-number"><?php echo $selesai; ?></h3>
                    <p class="stat-label">Selesai</p>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Sidebar -->
                <aside class="sidebar">
                    <ul class="sidebar-menu">
                        <li><a href="dashboard.php" class="active">
                            <i class="fas fa-home"></i> Dashboard
                        </a></li>
                        <?php if($role == 'mahasiswa'): ?>
                        <li><a href="komplain-baru.php">
                            <i class="fas fa-plus-circle"></i> Buat Komplain
                        </a></li>
                        <?php endif; ?>
                        <li><a href="komplain-saya.php">
                            <i class="fas fa-list"></i> <?php echo $role == 'admin' ? 'Semua' : 'Komplain Saya'; ?>
                        </a></li>
                        <?php if($role == 'admin'): ?>
                        <li><a href="laporan.php">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a></li>
                        <li><a href="pengguna.php">
                            <i class="fas fa-users"></i> Data Pengguna
                        </a></li>
                        <?php endif; ?>
                        <li><a href="profil.php">
                            <i class="fas fa-user-circle"></i> Profil Saya
                        </a></li>
                        <li><a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a></li>
                    </ul>
                </aside>

                <!-- Main Content -->
                <main class="main-content">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-history"></i> Komplain Terbaru</h3>
                        </div>
                        
                        <?php if(mysqli_num_rows($result_komplain) > 0): ?>
                        <div style="overflow-x: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <?php if($role == 'admin'): ?>
                                        <th>Mahasiswa</th>
                                        <?php endif; ?>
                                        <th>Judul</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Prioritas</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($result_komplain)): ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <?php if($role == 'admin'): ?>
                                        <td>
                                            <strong><?php echo $row['nama']; ?></strong><br>
                                            <small><?php echo $row['nim']; ?></small>
                                        </td>
                                        <?php endif; ?>
                                        <td><?php echo substr($row['judul'], 0, 40) . (strlen($row['judul']) > 40 ? '...' : ''); ?></td>
                                        <td><span class="badge" style="background: #e0e7ff; color: #3730a3;"><?php echo ucfirst($row['kategori']); ?></span></td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch($row['status']) {
                                                case 'pending': $status_class = 'badge-pending'; break;
                                                case 'diproses': $status_class = 'badge-process'; break;
                                                case 'selesai': $status_class = 'badge-success'; break;
                                                case 'ditolak': $status_class = 'badge-danger'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $prioritas_class = '';
                                            switch($row['prioritas']) {
                                                case 'rendah': $prioritas_class = 'badge-success'; break;
                                                case 'sedang': $prioritas_class = 'badge-pending'; break;
                                                case 'tinggi': $prioritas_class = 'badge-danger'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $prioritas_class; ?>"><?php echo ucfirst($row['prioritas']); ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_submit'])); ?></td>
                                        <td>
                                            <a href="detail-komplain.php?id=<?php echo $row['id']; ?>" class="btn btn-primary-form" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div style="text-align: center; padding: 3rem; color: #6b7280;">
                            <i class="fas fa-inbox" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                            <p style="font-size: 1.2rem;">Belum ada komplain</p>
                            <?php if($role == 'mahasiswa'): ?>
                            <a href="komplain-baru.php" class="btn btn-primary-form" style="margin-top: 1rem;">
                                <i class="fas fa-plus"></i> Buat Komplain Pertama
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </main>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
</body>
</html>