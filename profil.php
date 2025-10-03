<?php
require_once 'config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);

// Update profile
if(isset($_POST['update_profile'])) {
    $nama = clean($_POST['nama']);
    $email = clean($_POST['email']);
    $jurusan = clean($_POST['jurusan']);
    $no_telp = clean($_POST['no_telp']);
    
    if(empty($nama) || empty($email)) {
        $error = 'Nama dan email wajib diisi!';
    } else {
        // Check if email already used by other user
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND id != $user_id");
        if(mysqli_num_rows($check) > 0) {
            $error = 'Email sudah digunakan pengguna lain!';
        } else {
            $update = "UPDATE users SET nama='$nama', email='$email', jurusan='$jurusan', no_telp='$no_telp' WHERE id=$user_id";
            if(mysqli_query($conn, $update)) {
                $_SESSION['nama'] = $nama;
                $_SESSION['email'] = $email;
                $success = 'Profil berhasil diperbarui!';
                $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
            } else {
                $error = 'Gagal memperbarui profil!';
            }
        }
    }
}

// Change password
if(isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Semua field password wajib diisi!';
    } elseif($new_password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif(strlen($new_password) < 6) {
        $error = 'Password baru minimal 6 karakter!';
    } else {
        // Verify old password
        if(password_verify($old_password, $user['password'])) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = "UPDATE users SET password='$hashed' WHERE id=$user_id";
            if(mysqli_query($conn, $update)) {
                $success = 'Password berhasil diubah!';
            } else {
                $error = 'Gagal mengubah password!';
            }
        } else {
            $error = 'Password lama salah!';
        }
    }
}

// Upload foto profil
if(isset($_POST['upload_photo'])) {
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png');
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $newname = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = 'uploads/profiles/';
            
            if(!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            if(move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path . $newname)) {
                // Delete old photo if exists
                if($user['foto_profil'] != 'default.jpg' && file_exists($upload_path . $user['foto_profil'])) {
                    unlink($upload_path . $user['foto_profil']);
                }
                
                $update = "UPDATE users SET foto_profil='$newname' WHERE id=$user_id";
                if(mysqli_query($conn, $update)) {
                    $success = 'Foto profil berhasil diperbarui!';
                    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
                }
            } else {
                $error = 'Gagal mengupload foto!';
            }
        } else {
            $error = 'Format file tidak valid! Gunakan JPG, JPEG, atau PNG';
        }
    } else {
        $error = 'Pilih foto terlebih dahulu!';
    }
}

// Get user statistics
$stats = [];
if($user['role'] == 'mahasiswa') {
    $stats['total_komplain'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE user_id=$user_id"))['total'];
    $stats['pending'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE user_id=$user_id AND status='pending'"))['total'];
    $stats['selesai'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE user_id=$user_id AND status='selesai'"))['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Sistem Komplain FST</title>
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
                <li><a href="komplain-saya.php">Komplain</a></li>
                <li><a href="profil.php" class="active"><i class="fas fa-user"></i> Profil</a></li>
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
                <h1><i class="fas fa-user-circle"></i> Profil Saya</h1>
                <p>Kelola informasi profil dan keamanan akun Anda</p>
            </div>

            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-top: 2rem;">
                <!-- Profile Card -->
                <div class="card">
                    <div style="text-align: center; padding: 2rem;">
                        <div style="width: 150px; height: 150px; margin: 0 auto 1.5rem; border-radius: 50%; overflow: hidden; border: 5px solid var(--primary); box-shadow: var(--shadow-lg);">
                            <?php 
                            $foto_path = file_exists('uploads/profiles/' . $user['foto_profil']) && $user['foto_profil'] != 'default.jpg' 
                                ? 'uploads/profiles/' . $user['foto_profil'] 
                                : 'https://ui-avatars.com/api/?name=' . urlencode($user['nama']) . '&size=200&background=2563eb&color=fff';
                            ?>
                            <img src="<?php echo $foto_path; ?>" alt="Foto Profil" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <h3 style="color: var(--dark); margin-bottom: 0.5rem;"><?php echo $user['nama']; ?></h3>
                        <p style="color: #6b7280; margin-bottom: 0.5rem;">
                            <i class="fas fa-id-card"></i> <?php echo $user['nim']; ?>
                        </p>
                        <span class="badge" style="background: var(--primary); color: white;">
                            <?php echo ucfirst($user['role']); ?>
                        </span>

                        <form method="POST" enctype="multipart/form-data" style="margin-top: 2rem;">
                            <input type="file" name="foto" accept="image/*" class="form-control" style="margin-bottom: 1rem;">
                            <button type="submit" name="upload_photo" class="btn btn-primary-form btn-block">
                                <i class="fas fa-upload"></i> Upload Foto
                            </button>
                        </form>

                        <?php if($user['role'] == 'mahasiswa'): ?>
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border);">
                            <h4 style="margin-bottom: 1rem; color: var(--dark);">Statistik</h4>
                            <div style="display: grid; gap: 1rem;">
                                <div style="background: #eff6ff; padding: 1rem; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary);">
                                        <?php echo $stats['total_komplain']; ?>
                                    </div>
                                    <div style="font-size: 0.9rem; color: #6b7280;">Total Komplain</div>
                                </div>
                                <div style="background: #fef3c7; padding: 1rem; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #92400e;">
                                        <?php echo $stats['pending']; ?>
                                    </div>
                                    <div style="font-size: 0.9rem; color: #6b7280;">Pending</div>
                                </div>
                                <div style="background: #d1fae5; padding: 1rem; border-radius: 8px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #065f46;">
                                        <?php echo $stats['selesai']; ?>
                                    </div>
                                    <div style="font-size: 0.9rem; color: #6b7280;">Selesai</div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Forms -->
                <div>
                    <!-- Update Profile -->
                    <div class="card" style="margin-bottom: 2rem;">
                        <div class="card-header">
                            <h3><i class="fas fa-edit"></i> Edit Profil</h3>
                        </div>
                        <div style="padding: 2rem;">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="nim"><i class="fas fa-id-card"></i> NIM</label>
                                    <input type="text" id="nim" class="form-control" value="<?php echo $user['nim']; ?>" disabled>
                                    <small style="color: #6b7280;">NIM tidak dapat diubah</small>
                                </div>

                                <div class="form-group">
                                    <label for="nama"><i class="fas fa-user"></i> Nama Lengkap *</label>
                                    <input type="text" id="nama" name="nama" class="form-control" value="<?php echo $user['nama']; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="jurusan"><i class="fas fa-graduation-cap"></i> Jurusan</label>
                                    <input type="text" id="jurusan" name="jurusan" class="form-control" value="<?php echo $user['jurusan']; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="no_telp"><i class="fas fa-phone"></i> No. Telepon</label>
                                    <input type="tel" id="no_telp" name="no_telp" class="form-control" value="<?php echo $user['no_telp']; ?>">
                                </div>

                                <button type="submit" name="update_profile" class="btn btn-primary-form btn-block">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-key"></i> Ubah Password</h3>
                        </div>
                        <div style="padding: 2rem;">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="old_password"><i class="fas fa-lock"></i> Password Lama *</label>
                                    <input type="password" id="old_password" name="old_password" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="new_password"><i class="fas fa-lock"></i> Password Baru *</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" minlength="6" required>
                                    <small style="color: #6b7280;">Minimal 6 karakter</small>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password"><i class="fas fa-lock"></i> Konfirmasi Password Baru *</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                </div>

                                <button type="submit" name="change_password" class="btn btn-warning btn-block">
                                    <i class="fas fa-key"></i> Ubah Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
</body>
</html>