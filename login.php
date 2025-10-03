<?php
require_once 'config.php';

if(isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = clean($_POST['nim']);
    $password = $_POST['password'];
    
    if(empty($nim) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } else {
        $query = "SELECT * FROM users WHERE nim = '$nim'";
        $result = mysqli_query($conn, $query);
        
        if(mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nim'] = $user['nim'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                
                redirect('dashboard.php');
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'NIM tidak ditemukan!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Komplain FST</title>
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
                <li><a href="register.php" class="btn-nav">Register</a></li>
            </ul>
        </div>
    </nav>

    <div class="form-container">
        <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
        
        <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nim"><i class="fas fa-id-card"></i> NIM</label>
                <input type="text" id="nim" name="nim" class="form-control" 
                       placeholder="Masukkan NIM Anda" required>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Masukkan Password" required>
            </div>
            
            <button type="submit" class="btn btn-primary-form btn-block">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <p class="text-center mt-2">
            Belum punya akun? <a href="register.php" style="color: var(--primary); font-weight: 600;">Daftar di sini</a>
        </p>
    </div>

    <script src="js/main.js"></script>
</body>
</html>