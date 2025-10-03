<?php
require_once 'config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$komplain_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get komplain details
$query = "SELECT k.*, u.nama, u.nim, u.email, u.jurusan 
          FROM komplain k 
          JOIN users u ON k.user_id = u.id 
          WHERE k.id = $komplain_id";

if($role != 'admin') {
    $query .= " AND k.user_id = $user_id";
}

$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    redirect('dashboard.php');
}

$komplain = mysqli_fetch_assoc($result);

// Handle status update (Admin only)
if($role == 'admin' && isset($_POST['update_status'])) {
    $new_status = clean($_POST['status']);
    $update_query = "UPDATE komplain SET status='$new_status' WHERE id=$komplain_id";
    
    if(mysqli_query($conn, $update_query)) {
        // Create notification
        $notif_query = "INSERT INTO notifikasi (user_id, komplain_id, judul, pesan) 
                       VALUES ({$komplain['user_id']}, $komplain_id, 'Status Komplain Diperbarui', 
                       'Status komplain Anda telah diubah menjadi: $new_status')";
        mysqli_query($conn, $notif_query);
        
        header("Location: detail-komplain.php?id=$komplain_id");
        exit();
    }
}

// Handle tanggapan
if(isset($_POST['submit_tanggapan'])) {
    $pesan = clean($_POST['pesan']);
    
    if(!empty($pesan)) {
        $insert = "INSERT INTO tanggapan (komplain_id, user_id, pesan) 
                  VALUES ($komplain_id, $user_id, '$pesan')";
        
        if(mysqli_query($conn, $insert)) {
            // Create notification
            $target_user = ($role == 'admin') ? $komplain['user_id'] : 
                          mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE role='admin' LIMIT 1"))['id'];
            
            $notif_query = "INSERT INTO notifikasi (user_id, komplain_id, judul, pesan) 
                           VALUES ($target_user, $komplain_id, 'Tanggapan Baru', 
                           '{$_SESSION['nama']} memberikan tanggapan pada komplain')";
            mysqli_query($conn, $notif_query);
            
            header("Location: detail-komplain.php?id=$komplain_id#tanggapan");
            exit();
        }
    }
}

// Get tanggapan
$tanggapan_result = mysqli_query($conn, "SELECT t.*, u.nama, u.role 
                                         FROM tanggapan t 
                                         JOIN users u ON t.user_id = u.id 
                                         WHERE t.komplain_id = $komplain_id 
                                         ORDER BY t.tanggal ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Komplain #<?php echo $komplain_id; ?> - Sistem Komplain FST</title>
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
                <li><a href="komplain-saya.php">Komplain Saya</a></li>
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
            <div style="max-width: 1000px; margin: 40px auto;">
                <!-- Back Button -->
                <a href="<?php echo $role == 'admin' ? 'komplain-saya.php' : 'dashboard.php'; ?>" 
                   class="btn" style="background: #6b7280; color: white; margin-bottom: 1rem;">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>

                <!-- Komplain Detail Card -->
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h3><i class="fas fa-file-alt"></i> Detail Komplain #<?php echo $komplain_id; ?></h3>
                        <?php
                        $status_class = '';
                        $status_text = ucfirst($komplain['status']);
                        switch($komplain['status']) {
                            case 'pending': $status_class = 'background: #fef3c7; color: #92400e;'; break;
                            case 'diproses': $status_class = 'background: #dbeafe; color: #1e40af;'; break;
                            case 'selesai': $status_class = 'background: #d1fae5; color: #065f46;'; break;
                            case 'ditolak': $status_class = 'background: #fee2e2; color: #991b1b;'; break;
                        }
                        ?>
                        <span class="badge" style="<?php echo $status_class; ?> font-size: 1rem; padding: 0.5rem 1rem;">
                            <?php echo $status_text; ?>
                        </span>
                    </div>

                    <div style="padding: 2rem;">
                        <!-- Info Pelapor -->
                        <div style="background: #f9fafb; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                            <h4 style="margin-bottom: 1rem; color: var(--dark);">
                                <i class="fas fa-user"></i> Informasi Pelapor
                            </h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                <div>
                                    <strong>Nama:</strong><br>
                                    <?php echo $komplain['nama']; ?>
                                </div>
                                <div>
                                    <strong>NIM:</strong><br>
                                    <?php echo $komplain['nim']; ?>
                                </div>
                                <div>
                                    <strong>Jurusan:</strong><br>
                                    <?php echo $komplain['jurusan']; ?>
                                </div>
                                <div>
                                    <strong>Email:</strong><br>
                                    <?php echo $komplain['email']; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Detail Komplain -->
                        <div style="margin-bottom: 2rem;">
                            <h4 style="margin-bottom: 1rem; color: var(--dark);">
                                <i class="fas fa-info-circle"></i> Detail Komplain
                            </h4>
                            
                            <div style="margin-bottom: 1.5rem;">
                                <strong style="color: var(--dark);">Kategori:</strong><br>
                                <span class="badge" style="background: #e0e7ff; color: #3730a3; margin-top: 0.5rem;">
                                    <?php echo ucfirst($komplain['kategori']); ?>
                                </span>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <strong style="color: var(--dark);">Prioritas:</strong><br>
                                <?php
                                $prioritas_class = '';
                                switch($komplain['prioritas']) {
                                    case 'rendah': $prioritas_class = 'background: #d1fae5; color: #065f46;'; break;
                                    case 'sedang': $prioritas_class = 'background: #fef3c7; color: #92400e;'; break;
                                    case 'tinggi': $prioritas_class = 'background: #fee2e2; color: #991b1b;'; break;
                                }
                                ?>
                                <span class="badge" style="<?php echo $prioritas_class; ?> margin-top: 0.5rem;">
                                    <?php echo ucfirst($komplain['prioritas']); ?>
                                </span>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <strong style="color: var(--dark);">Judul:</strong><br>
                                <p style="font-size: 1.1rem; margin-top: 0.5rem;"><?php echo $komplain['judul']; ?></p>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <strong style="color: var(--dark);">Deskripsi:</strong><br>
                                <p style="line-height: 1.8; margin-top: 0.5rem; text-align: justify;">
                                    <?php echo nl2br($komplain['deskripsi']); ?>
                                </p>
                            </div>

                            <?php if($komplain['lampiran']): ?>
                            <div style="margin-bottom: 1.5rem;">
                                <strong style="color: var(--dark);">Lampiran:</strong><br>
                                <a href="uploads/<?php echo $komplain['lampiran']; ?>" target="_blank" 
                                   class="btn btn-primary-form" style="margin-top: 0.5rem; display: inline-block;">
                                    <i class="fas fa-download"></i> Unduh Lampiran
                                </a>
                            </div>
                            <?php endif; ?>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                                <div>
                                    <strong style="color: #6b7280;">Tanggal Dibuat:</strong><br>
                                    <?php echo date('d F Y, H:i', strtotime($komplain['tanggal_submit'])); ?>
                                </div>
                                <div>
                                    <strong style="color: #6b7280;">Terakhir Diupdate:</strong><br>
                                    <?php echo date('d F Y, H:i', strtotime($komplain['tanggal_update'])); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Controls -->
                        <?php if($role == 'admin'): ?>
                        <div style="background: #eff6ff; padding: 1.5rem; border-radius: 10px; border-left: 4px solid var(--primary);">
                            <h4 style="margin-bottom: 1rem; color: var(--dark);">
                                <i class="fas fa-cog"></i> Kontrol Admin
                            </h4>
                            <form method="POST" action="" style="display: flex; gap: 1rem; align-items: end;">
                                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                                    <label for="status">Update Status:</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="pending" <?php echo $komplain['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="diproses" <?php echo $komplain['status'] == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                        <option value="selesai" <?php echo $komplain['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="ditolak" <?php echo $komplain['status'] == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_status" class="btn btn-primary-form">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tanggapan Section -->
                <div class="card" id="tanggapan">
                    <div class="card-header">
                        <h3><i class="fas fa-comments"></i> Tanggapan & Diskusi</h3>
                    </div>

                    <div style="padding: 2rem;">
                        <?php if(mysqli_num_rows($tanggapan_result) > 0): ?>
                        <div style="margin-bottom: 2rem;">
                            <?php while($tanggapan = mysqli_fetch_assoc($tanggapan_result)): ?>
                            <div style="background: <?php echo $tanggapan['role'] == 'admin' ? '#eff6ff' : '#f9fafb'; ?>; 
                                        padding: 1.5rem; border-radius: 10px; margin-bottom: 1rem; 
                                        border-left: 4px solid <?php echo $tanggapan['role'] == 'admin' ? 'var(--primary)' : '#9ca3af'; ?>;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                    <strong style="color: var(--dark);">
                                        <i class="fas fa-<?php echo $tanggapan['role'] == 'admin' ? 'user-shield' : 'user'; ?>"></i>
                                        <?php echo $tanggapan['nama']; ?>
                                        <?php if($tanggapan['role'] == 'admin'): ?>
                                        <span class="badge" style="background: var(--primary); color: white; margin-left: 0.5rem;">Admin</span>
                                        <?php endif; ?>
                                    </strong>
                                    <span style="color: #6b7280; font-size: 0.9rem;">
                                        <?php echo date('d/m/Y H:i', strtotime($tanggapan['tanggal'])); ?>
                                    </span>
                                </div>
                                <p style="line-height: 1.6; margin: 0;"><?php echo nl2br($tanggapan['pesan']); ?></p>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #6b7280;">
                            <i class="fas fa-comment-slash" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <p>Belum ada tanggapan</p>
                        </div>
                        <?php endif; ?>

                        <!-- Form Tanggapan -->
                        <?php if($komplain['status'] != 'selesai' && $komplain['status'] != 'ditolak'): ?>
                        <div style="background: #f9fafb; padding: 1.5rem; border-radius: 10px;">
                            <h4 style="margin-bottom: 1rem; color: var(--dark);">
                                <i class="fas fa-reply"></i> Tambah Tanggapan
                            </h4>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <textarea name="pesan" class="form-control" rows="4" 
                                              placeholder="Tulis tanggapan Anda..." required></textarea>
                                </div>
                                <button type="submit" name="submit_tanggapan" class="btn btn-primary-form">
                                    <i class="fas fa-paper-plane"></i> Kirim Tanggapan
                                </button>
                            </form>
                        </div>
                        <?php else: ?>
                        <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; text-align: center; color: #92400e;">
                            <i class="fas fa-info-circle"></i> Komplain ini sudah <?php echo $komplain['status']; ?>. Tidak dapat menambahkan tanggapan baru.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
</body>
</html>