<?php
require_once 'config.php';

if(isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = clean($_POST['nim']);
    $nama = clean($_POST['nama']);
    $email = clean($_POST['email']);
    $jurusan = clean($_POST['jurusan']);
    $no_telp = clean($_POST['no_telp']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(empty($nim) || empty($nama) || empty($email) || empty($jurusan) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    } elseif($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif(strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Check if NIM already exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE nim = '$nim'");
        if(mysqli_num_rows($check) > 0) {
            $error = 'NIM sudah terdaftar!';
        } else {
            // Check if email already exists
            $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
            if(mysqli_num_rows($check) > 0) {
                $error = 'Email sudah terdaftar!';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $query = "INSERT INTO users (nim, nama, email, password, jurusan, no_telp) 
                         VALUES ('$nim', '$nama', '$email', '$hashed_password', '$jurusan', '$no_telp')";
                
                if(mysqli_query($conn, $query)) {
                    $success = 'Registrasi berhasil! Silakan login.';
                } else {
                    $error = 'Registrasi gagal: ' . mysqli_error($conn);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Komplain FST</title>
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
                <li><a href="index.php">Beranda</a></li>
                <li><a href="login.php" class="btn-nav">Login</a></li>
            </ul>
        </div>
    </nav>

    <div class="form-container" style="max-width: 600px; margin-top: 40px; margin-bottom: 40px;">
        <h2><i class="fas fa-user-plus"></i> Registrasi Mahasiswa</h2>
        
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
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nim"><i class="fas fa-id-card"></i> NIM *</label>
                <input type="text" id="nim" name="nim" class="form-control" 
                       placeholder="Contoh: 2021001" required value="<?php echo isset($nim) ? $nim : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="nama"><i class="fas fa-user"></i> Nama Lengkap *</label>
                <input type="text" id="nama" name="nama" class="form-control" 
                       placeholder="Masukkan nama lengkap" required value="<?php echo isset($nama) ? $nama : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="email@example.com" required value="<?php echo isset($email) ? $email : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="jurusan"><i class="fas fa-graduation-cap"></i> Jurusan *</label>
                <select id="jurusan" name="jurusan" class="form-control" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <option value="Teknik Informatika" <?php echo (isset($jurusan) && $jurusan == 'Teknik Informatika') ? 'selected' : ''; ?>>Teknik Informatika</option>
                    <option value="Sistem Informasi" <?php echo (isset($jurusan) && $jurusan == 'Sistem Informasi') ? 'selected' : ''; ?>>Sistem Informasi</option>
                    <option value="Teknik Elektro" <?php echo (isset($jurusan) && $jurusan == 'Teknik Elektro') ? 'selected' : ''; ?>>Teknik Elektro</option>
                    <option value="Teknik Sipil" <?php echo (isset($jurusan) && $jurusan == 'Teknik Sipil') ? 'selected' : ''; ?>>Teknik Sipil</option>
                    <option value="Matematika" <?php echo (isset($jurusan) && $jurusan == 'Matematika') ? 'selected' : ''; ?>>Matematika</option>
                    <option value="Fisika" <?php echo (isset($jurusan) && $jurusan == 'Fisika') ? 'selected' : ''; ?>>Fisika</option>
                    <option value="Kimia" <?php echo (isset($jurusan) && $jurusan == 'Kimia') ? 'selected' : ''; ?>>Kimia</option>
                    <option value="Biologi" <?php echo (isset($jurusan) && $jurusan == 'Biologi') ? 'selected' : ''; ?>>Biologi</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="no_telp"><i class="fas fa-phone"></i> No. Telepon</label>
                <input type="tel" id="no_telp" name="no_telp" class="form-control" 
                       placeholder="08xxxxxxxxxx" value="<?php echo isset($no_telp) ? $no_telp : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password *</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Minimal 6 karakter" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Konfirmasi Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                       placeholder="Ulangi password" required>
            </div>
            
            <button type="submit" class="btn btn-primary-form btn-block">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>
        
        <p class="text-center mt-2">
            Sudah punya akun? <a href="login.php" style="color: var(--primary); font-weight: 600;">Login di sini</a>
        </p>
    </div>

    <script src="js/main.js"></script>
</body>
</html>