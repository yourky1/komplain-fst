<?php
require_once 'config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

if($_SESSION['role'] != 'mahasiswa') {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $kategori = clean($_POST['kategori']);
    $judul = clean($_POST['judul']);
    $deskripsi = clean($_POST['deskripsi']);
    $prioritas = clean($_POST['prioritas']);
    
    if(empty($kategori) || empty($judul) || empty($deskripsi)) {
        $error = 'Semua field wajib diisi!';
    } else {
        // Handle file upload
        $lampiran = '';
        if(isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx');
            $filename = $_FILES['lampiran']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $newname = uniqid() . '.' . $ext;
                $upload_path = 'uploads/' . $newname;
                
                if(!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                
                if(move_uploaded_file($_FILES['lampiran']['tmp_name'], $upload_path)) {
                    $lampiran = $newname;
                }
            }
        }
        
        $query = "INSERT INTO komplain (user_id, kategori, judul, deskripsi, lampiran, prioritas, status) 
                 VALUES ($user_id, '$kategori', '$judul', '$deskripsi', '$lampiran', '$prioritas', 'pending')";
        
        if(mysqli_query($conn, $query)) {
            $komplain_id = mysqli_insert_id($conn);
            
            // Create notification for admin
            $admin_query = mysqli_query($conn, "SELECT id FROM users WHERE role='admin'");
            while($admin = mysqli_fetch_assoc($admin_query)) {
                $notif_query = "INSERT INTO notifikasi (user_id, komplain_id, judul, pesan) 
                               VALUES ({$admin['id']}, $komplain_id, 'Komplain Baru', 'Komplain baru dari {$_SESSION['nama']}: $judul')";
                mysqli_query($conn, $notif_query);
            }
            
            $success = 'Komplain berhasil diajukan!';
            header("refresh:2;url=detail-komplain.php?id=$komplain_id");
        } else {
            $error = 'Gagal mengajukan komplain: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Komplain Baru - Sistem Komplain FST</title>
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
            <div class="form-container" style="max-width: 800px; margin: 40px auto;">
                <h2><i class="fas fa-plus-circle"></i> Buat Komplain Baru</h2>
                
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
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="kategori"><i class="fas fa-tag"></i> Kategori Komplain *</label>
                        <select id="kategori" name="kategori" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="akademik">Akademik</option>
                            <option value="fasilitas">Fasilitas</option>
                            <option value="administrasi">Administrasi</option>
                            <option value="dosen">Dosen</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                        <small style="color: #6b7280; display: block; margin-top: 5px;">
                            Pilih kategori yang sesuai dengan komplain Anda
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="prioritas"><i class="fas fa-exclamation-triangle"></i> Prioritas *</label>
                        <select id="prioritas" name="prioritas" class="form-control" required>
                            <option value="sedang" selected>Sedang</option>
                            <option value="rendah">Rendah</option>
                            <option value="tinggi">Tinggi</option>
                        </select>
                        <small style="color: #6b7280; display: block; margin-top: 5px;">
                            Tentukan tingkat urgensi komplain Anda
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="judul"><i class="fas fa-heading"></i> Judul Komplain *</label>
                        <input type="text" id="judul" name="judul" class="form-control" 
                               placeholder="Ringkasan singkat komplain Anda" required maxlength="200">
                        <small style="color: #6b7280; display: block; margin-top: 5px;">
                            Maksimal 200 karakter
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi"><i class="fas fa-align-left"></i> Deskripsi Detail *</label>
                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="8" 
                                  placeholder="Jelaskan secara detail komplain Anda..." required></textarea>
                        <small style="color: #6b7280; display: block; margin-top: 5px;">
                            Semakin detail deskripsi, semakin mudah kami membantu Anda
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="lampiran"><i class="fas fa-paperclip"></i> Lampiran</label>
                        <input type="file" id="lampiran" name="lampiran" class="form-control" 
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <small style="color: #6b7280; display: block; margin-top: 5px;">
                            Format: JPG, PNG, PDF, DOC, DOCX (Maks. 5MB)
                        </small>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary-form" style="flex: 1;">
                            <i class="fas fa-paper-plane"></i> Kirim Komplain
                        </button>
                        <a href="dashboard.php" class="btn" style="flex: 1; background: #6b7280; color: white; text-align: center; line-height: 2.5;">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
</body>
</html>