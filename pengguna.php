<?php
require_once 'config.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

// Delete user
if(isset($_GET['delete']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // Don't allow deleting own account
    if($user_id != $_SESSION['user_id']) {
        // Delete user (cascade will handle komplain, tanggapan, notifikasi)
        if(mysqli_query($conn, "DELETE FROM users WHERE id=$user_id")) {
            $success = 'Pengguna berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus pengguna!';
        }
    } else {
        $error = 'Tidak dapat menghapus akun sendiri!';
    }
}

// Add new user
if(isset($_POST['add_user'])) {
    $nim = clean($_POST['nim']);
    $nama = clean($_POST['nama']);
    $email = clean($_POST['email']);
    $jurusan = clean($_POST['jurusan']);
    $role = clean($_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if NIM or email exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE nim='$nim' OR email='$email'");
    if(mysqli_num_rows($check) > 0) {
        $error = 'NIM atau Email sudah terdaftar!';
    } else {
        $query = "INSERT INTO users (nim, nama, email, password, jurusan, role) 
                 VALUES ('$nim', '$nama', '$email', '$password', '$jurusan', '$role')";
        if(mysqli_query($conn, $query)) {
            $success = 'Pengguna berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan pengguna!';
        }
    }
}

// Filter
$role_filter = isset($_GET['role']) ? clean($_GET['role']) : '';
$search = isset($_GET['search']) ? clean($_GET['search']) : '';

$query = "SELECT * FROM users WHERE 1=1";
if($role_filter) {
    $query .= " AND role='$role_filter'";
}
if($search) {
    $query .= " AND (nama LIKE '%$search%' OR nim LIKE '%$search%' OR email LIKE '%$search%')";
}
$query .= " ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);

// Statistics
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$total_mahasiswa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='mahasiswa'"))['total'];
$total_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='admin'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengguna - Sistem Komplain FST</title>
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
                <li><a href="komplain-saya.php">Semua Komplain</a></li>
                <li><a href="pengguna.php" class="active"><i class="fas fa-users"></i> Pengguna</a></li>
                <li><a href="laporan.php">Laporan</a></li>
                <li><a href="profil.php"><?php echo $_SESSION['nama']; ?></a></li>
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
                    <h1><i class="fas fa-users"></i> Data Pengguna</h1>
                    <p>Kelola semua pengguna sistem</p>
                </div>
                <button onclick="document.getElementById('addUserModal').style.display='block'" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Tambah Pengguna
                </button>
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

            <!-- Statistics -->
            <div class="stats-grid" style="margin-bottom: 2rem; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <h3 class="stat-number"><?php echo $total_users; ?></h3>
                    <p class="stat-label">Total Pengguna</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                    <h3 class="stat-number"><?php echo $total_mahasiswa; ?></h3>
                    <p class="stat-label">Mahasiswa</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
                    <h3 class="stat-number"><?php echo $total_admin; ?></h3>
                    <p class="stat-label">Admin</p>
                </div>
            </div>

            <div class="card">
                <!-- Filter -->
                <div style="padding: 1.5rem; background: #f9fafb; border-radius: 10px; margin-bottom: 2rem;">
                    <form method="GET" action="">
                        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="search"><i class="fas fa-search"></i> Cari</label>
                                <input type="text" id="search" name="search" class="form-control" 
                                       placeholder="Cari nama, NIM, atau email..." value="<?php echo $search; ?>">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="role"><i class="fas fa-filter"></i> Role</label>
                                <select id="role" name="role" class="form-control">
                                    <option value="">Semua Role</option>
                                    <option value="mahasiswa" <?php echo $role_filter == 'mahasiswa' ? 'selected' : ''; ?>>Mahasiswa</option>
                                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div style="display: flex; gap: 0.5rem; align-items: end;">
                                <button type="submit" class="btn btn-primary-form">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="pengguna.php" class="btn" style="background: #6b7280; color: white;">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Results Info -->
                <div style="padding: 0 0 1rem 0; color: #6b7280;">
                    <i class="fas fa-info-circle"></i> 
                    Menampilkan <?php echo mysqli_num_rows($result); ?> pengguna
                </div>

                <!-- Users Table -->
                <?php if(mysqli_num_rows($result) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Jurusan</th>
                                <th>Role</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['nama']); ?>&size=40&background=2563eb&color=fff" 
                                         style="width: 40px; height: 40px; border-radius: 50%; border: 2px solid var(--primary);">
                                </td>
                                <td><strong><?php echo $user['nim']; ?></strong></td>
                                <td><?php echo $user['nama']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['jurusan']; ?></td>
                                <td>
                                    <span class="badge" style="<?php echo $user['role'] == 'admin' ? 'background: #f59e0b; color: white;' : 'background: #10b981; color: white;'; ?>">
                                        <i class="fas fa-<?php echo $user['role'] == 'admin' ? 'user-shield' : 'user-graduate'; ?>"></i>
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button onclick="viewUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" 
                                                class="btn btn-primary-form" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.9rem;"
                                           onclick="return confirm('Hapus pengguna <?php echo $user['nama']; ?>?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div style="text-align: center; padding: 4rem; color: #6b7280;">
                    <i class="fas fa-users-slash" style="font-size: 5rem; margin-bottom: 1.5rem; opacity: 0.3;"></i>
                    <h3 style="margin-bottom: 1rem;">Tidak Ada Pengguna</h3>
                    <p>Tidak ditemukan pengguna dengan filter yang dipilih</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Add User Modal -->
    <div id="addUserModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; 
                                   background-color: rgba(0,0,0,0.5); overflow: auto;">
        <div style="background: white; margin: 5% auto; padding: 0; border-radius: 15px; width: 90%; max-width: 600px; 
                    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
            <div style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); 
                        color: white; padding: 1.5rem; border-radius: 15px 15px 0 0; display: flex; 
                        justify-content: space-between; align-items: center;">
                <h3 style="margin: 0;"><i class="fas fa-user-plus"></i> Tambah Pengguna Baru</h3>
                <button onclick="document.getElementById('addUserModal').style.display='none'" 
                        style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" style="padding: 2rem;">
                <div class="form-group">
                    <label for="nim_new"><i class="fas fa-id-card"></i> NIM *</label>
                    <input type="text" id="nim_new" name="nim" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="nama_new"><i class="fas fa-user"></i> Nama Lengkap *</label>
                    <input type="text" id="nama_new" name="nama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email_new"><i class="fas fa-envelope"></i> Email *</label>
                    <input type="email" id="email_new" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="jurusan_new"><i class="fas fa-graduation-cap"></i> Jurusan</label>
                    <input type="text" id="jurusan_new" name="jurusan" class="form-control">
                </div>
                <div class="form-group">
                    <label for="role_new"><i class="fas fa-user-tag"></i> Role *</label>
                    <select id="role_new" name="role" class="form-control" required>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password_new"><i class="fas fa-lock"></i> Password *</label>
                    <input type="password" id="password_new" name="password" class="form-control" minlength="6" required>
                    <small style="color: #6b7280;">Minimal 6 karakter</small>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" name="add_user" class="btn btn-primary-form" style="flex: 1;">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button" onclick="document.getElementById('addUserModal').style.display='none'" 
                            class="btn" style="flex: 1; background: #6b7280; color: white;">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View User Modal -->
    <div id="viewUserModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; 
                                    background-color: rgba(0,0,0,0.5); overflow: auto;">
        <div style="background: white; margin: 5% auto; padding: 0; border-radius: 15px; width: 90%; max-width: 500px;">
            <div style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); 
                        color: white; padding: 1.5rem; border-radius: 15px 15px 0 0; display: flex; 
                        justify-content: space-between; align-items: center;">
                <h3 style="margin: 0;"><i class="fas fa-user"></i> Detail Pengguna</h3>
                <button onclick="document.getElementById('viewUserModal').style.display='none'" 
                        style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="userDetailContent" style="padding: 2rem;"></div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        function viewUser(user) {
            const content = `
                <div style="text-align: center; margin-bottom: 2rem;">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(user.nama)}&size=100&background=2563eb&color=fff" 
                         style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid var(--primary);">
                </div>
                <div style="display: grid; gap: 1rem;">
                    <div style="padding: 1rem; background: #f9fafb; border-radius: 8px;">
                        <strong style="color: #6b7280;">NIM:</strong><br>
                        <span style="font-size: 1.1rem;">${user.nim}</span>
                    </div>
                    <div style="padding: 1rem; background: #f9fafb; border-radius: 8px;">
                        <strong style="color: #6b7280;">Nama:</strong><br>
                        <span style="font-size: 1.1rem;">${user.nama}</span>
                    </div>
                    <div style="padding: 1rem; background: #f9fafb; border-radius: 8px;">
                        <strong style="color: #6b7280;">Email:</strong><br>
                        <span style="font-size: 1.1rem;">${user.email}</span>
                    </div>
                    <div style="padding: 1rem; background: #f9fafb; border-radius: 8px;">
                        <strong style="color: #6b7280;">Jurusan:</strong><br>
                        <span style="font-size: 1.1rem;">${user.jurusan}</span>
                    </div>
                    <div style="padding: 1rem; background: #f9fafb; border-radius: 8px;">
                        <strong style="color: #6b7280;">Role:</strong><br>
                        <span class="badge" style="${user.role == 'admin' ? 'background: #f59e0b;' : 'background: #10b981;'} color: white; font-size: 1rem;">
                            ${user.role.toUpperCase()}
                        </span>
                    </div>
                    <div style="padding: 1rem; background: #f9fafb; border-radius: 8px;">
                        <strong style="color: #6b7280;">Terdaftar:</strong><br>
                        <span style="font-size: 1.1rem;">${new Date(user.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}</span>
                    </div>
                </div>
            `;
            document.getElementById('userDetailContent').innerHTML = content;
            document.getElementById('viewUserModal').style.display = 'block';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addUserModal');
            const viewModal = document.getElementById('viewUserModal');
            if (event.target == addModal) {
                addModal.style.display = 'none';
            }
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>