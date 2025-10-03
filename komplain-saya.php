<?php
require_once 'config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Filter parameters
$status_filter = isset($_GET['status']) ? clean($_GET['status']) : '';
$kategori_filter = isset($_GET['kategori']) ? clean($_GET['kategori']) : '';
$search = isset($_GET['search']) ? clean($_GET['search']) : '';

// Build query
if($role == 'admin') {
    $query = "SELECT k.*, u.nama, u.nim FROM komplain k JOIN users u ON k.user_id = u.id WHERE 1=1";
} else {
    $query = "SELECT k.*, u.nama, u.nim FROM komplain k JOIN users u ON k.user_id = u.id WHERE k.user_id = $user_id";
}

if($status_filter) {
    $query .= " AND k.status = '$status_filter'";
}
if($kategori_filter) {
    $query .= " AND k.kategori = '$kategori_filter'";
}
if($search) {
    $query .= " AND (k.judul LIKE '%$search%' OR k.deskripsi LIKE '%$search%')";
}

$query .= " ORDER BY k.tanggal_submit DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $role == 'admin' ? 'Semua Komplain' : 'Komplain Saya'; ?> - Sistem Komplain FST</title>
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="komplain-saya.php" class="active"><?php echo $role == 'admin' ? 'Semua Komplain' : 'Komplain Saya'; ?></a></li>
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
                    <h1><i class="fas fa-list"></i> <?php echo $role == 'admin' ? 'Semua Komplain' : 'Komplain Saya'; ?></h1>
                    <p>Kelola dan pantau status komplain</p>
                </div>
                <?php if($role == 'mahasiswa'): ?>
                <a href="komplain-baru.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Buat Komplain Baru
                </a>
                <?php endif; ?>
            </div>

            <div class="card">
                <!-- Filter Section -->
                <div style="padding: 1.5rem; background: #f9fafb; border-radius: 10px; margin-bottom: 2rem;">
                    <form method="GET" action="">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="search"><i class="fas fa-search"></i> Cari</label>
                                <input type="text" id="search" name="search" class="form-control" 
                                       placeholder="Cari judul atau deskripsi..." value="<?php echo $search; ?>">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="status"><i class="fas fa-filter"></i> Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="diproses" <?php echo $status_filter == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                    <option value="selesai" <?php echo $status_filter == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="ditolak" <?php echo $status_filter == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="kategori"><i class="fas fa-tag"></i> Kategori</label>
                                <select id="kategori" name="kategori" class="form-control">
                                    <option value="">Semua Kategori</option>
                                    <option value="akademik" <?php echo $kategori_filter == 'akademik' ? 'selected' : ''; ?>>Akademik</option>
                                    <option value="fasilitas" <?php echo $kategori_filter == 'fasilitas' ? 'selected' : ''; ?>>Fasilitas</option>
                                    <option value="administrasi" <?php echo $kategori_filter == 'administrasi' ? 'selected' : ''; ?>>Administrasi</option>
                                    <option value="dosen" <?php echo $kategori_filter == 'dosen' ? 'selected' : ''; ?>>Dosen</option>
                                    <option value="lainnya" <?php echo $kategori_filter == 'lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                                </select>
                            </div>
                            <div style="display: flex; gap: 0.5rem; align-items: end;">
                                <button type="submit" class="btn btn-primary-form" style="flex: 1;">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="komplain-saya.php" class="btn" style="background: #6b7280; color: white; flex: 1; text-align: center; line-height: 2.5;">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Results Info -->
                <div style="padding: 0 0 1rem 0; color: #6b7280;">
                    <i class="fas fa-info-circle"></i> 
                    Menampilkan <?php echo mysqli_num_rows($result); ?> komplain
                </div>

                <!-- Komplain List -->
                <?php if(mysqli_num_rows($result) > 0): ?>
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
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><strong>#<?php echo $row['id']; ?></strong></td>
                                <?php if($role == 'admin'): ?>
                                <td>
                                    <strong><?php echo $row['nama']; ?></strong><br>
                                    <small style="color: #6b7280;"><?php echo $row['nim']; ?></small>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <strong><?php echo substr($row['judul'], 0, 50) . (strlen($row['judul']) > 50 ? '...' : ''); ?></strong><br>
                                    <small style="color: #6b7280;">
                                        <?php echo substr($row['deskripsi'], 0, 80) . (strlen($row['deskripsi']) > 80 ? '...' : ''); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge" style="background: #e0e7ff; color: #3730a3;">
                                        <?php echo ucfirst($row['kategori']); ?>
                                    </span>
                                </td>
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
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
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
                                    <span class="badge <?php echo $prioritas_class; ?>">
                                        <?php echo ucfirst($row['prioritas']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['tanggal_submit'])); ?></td>
                                <td>
                                    <a href="detail-komplain.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-primary-form" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 4rem; color: #6b7280;">
                    <i class="fas fa-inbox" style="font-size: 5rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <h3 style="margin-bottom: 1rem;">Tidak Ada Komplain</h3>
                    <p style="margin-bottom: 2rem;">
                        <?php if($search || $status_filter || $kategori_filter): ?>
                            Tidak ditemukan komplain dengan filter yang dipilih.
                        <?php else: ?>
                            Belum ada komplain yang diajukan.
                        <?php endif; ?>
                    </p>
                    <?php if($role == 'mahasiswa' && !$search && !$status_filter && !$kategori_filter): ?>
                    <a href="komplain-baru.php" class="btn btn-primary-form">
                        <i class="fas fa-plus"></i> Buat Komplain Pertama
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
</body>
</html>