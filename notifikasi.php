<?php
require_once 'config.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Mark as read
if(isset($_GET['mark_read']) && isset($_GET['id'])) {
    $notif_id = intval($_GET['id']);
    mysqli_query($conn, "UPDATE notifikasi SET is_read=1 WHERE id=$notif_id AND user_id=$user_id");
    
    // Get komplain_id and redirect
    $notif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT komplain_id FROM notifikasi WHERE id=$notif_id"));
    if($notif && $notif['komplain_id']) {
        redirect('detail-komplain.php?id=' . $notif['komplain_id']);
    } else {
        redirect('notifikasi.php');
    }
}

// Mark all as read
if(isset($_GET['mark_all_read'])) {
    mysqli_query($conn, "UPDATE notifikasi SET is_read=1 WHERE user_id=$user_id");
    redirect('notifikasi.php');
}

// Delete notification
if(isset($_GET['delete']) && isset($_GET['id'])) {
    $notif_id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM notifikasi WHERE id=$notif_id AND user_id=$user_id");
    redirect('notifikasi.php');
}

// Delete all notifications
if(isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM notifikasi WHERE user_id=$user_id");
    redirect('notifikasi.php');
}

// Get notifications
$filter = isset($_GET['filter']) ? clean($_GET['filter']) : 'all';
$query = "SELECT * FROM notifikasi WHERE user_id=$user_id";

if($filter == 'unread') {
    $query .= " AND is_read=0";
} elseif($filter == 'read') {
    $query .= " AND is_read=1";
}

$query .= " ORDER BY tanggal DESC";
$result = mysqli_query($conn, $query);

// Count unread
$unread_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM notifikasi WHERE user_id=$user_id AND is_read=0"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - Sistem Komplain FST</title>
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
                <li><a href="notifikasi.php" class="active">
                    <i class="fas fa-bell"></i> Notifikasi
                    <?php if($unread_count > 0): ?>
                        <span class="badge badge-danger" style="font-size: 0.7rem;"><?php echo $unread_count; ?></span>
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
                    <h1><i class="fas fa-bell"></i> Notifikasi</h1>
                    <p>Pantau semua update dan pesan penting</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <?php if($unread_count > 0): ?>
                    <a href="?mark_all_read" class="btn-secondary" style="padding: 0.8rem 1.5rem;">
                        <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                    </a>
                    <?php endif; ?>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                    <a href="?delete_all" class="btn" style="background: #ef4444; color: white; padding: 0.8rem 1.5rem;" 
                       onclick="return confirm('Hapus semua notifikasi?')">
                        <i class="fas fa-trash"></i> Hapus Semua
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid" style="margin-bottom: 2rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                    <div class="stat-icon"><i class="fas fa-bell"></i></div>
                    <h3 class="stat-number"><?php echo mysqli_num_rows($result); ?></h3>
                    <p class="stat-label">Total Notifikasi</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                    <h3 class="stat-number"><?php echo $unread_count; ?></h3>
                    <p class="stat-label">Belum Dibaca</p>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <div class="stat-icon"><i class="fas fa-envelope-open"></i></div>
                    <h3 class="stat-number">
                        <?php 
                        $read_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM notifikasi WHERE user_id=$user_id AND is_read=1"))['total'];
                        echo $read_count;
                        ?>
                    </h3>
                    <p class="stat-label">Sudah Dibaca</p>
                </div>
            </div>

            <div class="card">
                <!-- Filter -->
                <div style="padding: 1.5rem; background: #f9fafb; border-radius: 10px 10px 0 0; border-bottom: 2px solid var(--border);">
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="?filter=all" class="btn <?php echo $filter == 'all' ? 'btn-primary-form' : ''; ?>" 
                               style="<?php echo $filter != 'all' ? 'background: white; color: var(--text);' : ''; ?> padding: 0.6rem 1.2rem;">
                                <i class="fas fa-list"></i> Semua
                            </a>
                            <a href="?filter=unread" class="btn <?php echo $filter == 'unread' ? 'btn-primary-form' : ''; ?>" 
                               style="<?php echo $filter != 'unread' ? 'background: white; color: var(--text);' : ''; ?> padding: 0.6rem 1.2rem;">
                                <i class="fas fa-envelope"></i> Belum Dibaca
                                <?php if($unread_count > 0): ?>
                                    <span class="badge" style="background: #ef4444; color: white; margin-left: 0.3rem;"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="?filter=read" class="btn <?php echo $filter == 'read' ? 'btn-primary-form' : ''; ?>" 
                               style="<?php echo $filter != 'read' ? 'background: white; color: var(--text);' : ''; ?> padding: 0.6rem 1.2rem;">
                                <i class="fas fa-envelope-open"></i> Sudah Dibaca
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Notifications List -->
                <div style="padding: 0;">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($notif = mysqli_fetch_assoc($result)): ?>
                        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); 
                                    background: <?php echo $notif['is_read'] ? 'white' : '#eff6ff'; ?>; 
                                    transition: all 0.3s; position: relative;"
                             onmouseover="this.style.backgroundColor='#f9fafb'" 
                             onmouseout="this.style.backgroundColor='<?php echo $notif['is_read'] ? 'white' : '#eff6ff'; ?>'">
                            
                            <!-- Unread indicator -->
                            <?php if(!$notif['is_read']): ?>
                            <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--primary);"></div>
                            <?php endif; ?>

                            <div style="display: flex; gap: 1.5rem; align-items: start; padding-left: <?php echo $notif['is_read'] ? '1rem' : '0'; ?>;">
                                <!-- Icon -->
                                <div style="flex-shrink: 0; width: 50px; height: 50px; border-radius: 50%; 
                                            background: <?php echo $notif['is_read'] ? '#e5e7eb' : 'var(--primary)'; ?>; 
                                            display: flex; align-items: center; justify-content: center; 
                                            color: <?php echo $notif['is_read'] ? '#6b7280' : 'white'; ?>; font-size: 1.3rem;">
                                    <i class="fas fa-bell"></i>
                                </div>

                                <!-- Content -->
                                <div style="flex: 1;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                        <h4 style="color: var(--dark); margin: 0; font-size: 1.1rem;">
                                            <?php echo $notif['judul']; ?>
                                            <?php if(!$notif['is_read']): ?>
                                            <span class="badge" style="background: var(--primary); color: white; margin-left: 0.5rem; font-size: 0.7rem;">BARU</span>
                                            <?php endif; ?>
                                        </h4>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <?php if(!$notif['is_read']): ?>
                                            <a href="?mark_read&id=<?php echo $notif['id']; ?>" 
                                               class="btn btn-primary-form" 
                                               style="padding: 0.4rem 0.8rem; font-size: 0.85rem;"
                                               title="Tandai dibaca & lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php elseif($notif['komplain_id']): ?>
                                            <a href="detail-komplain.php?id=<?php echo $notif['komplain_id']; ?>" 
                                               class="btn btn-primary-form" 
                                               style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="?delete&id=<?php echo $notif['id']; ?>" 
                                               class="btn" 
                                               style="background: #ef4444; color: white; padding: 0.4rem 0.8rem; font-size: 0.85rem;"
                                               onclick="return confirm('Hapus notifikasi ini?')"
                                               title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <p style="color: #6b7280; margin: 0 0 0.5rem 0; line-height: 1.6;">
                                        <?php echo $notif['pesan']; ?>
                                    </p>
                                    <div style="display: flex; gap: 1.5rem; font-size: 0.85rem; color: #9ca3af;">
                                        <span><i class="fas fa-clock"></i> <?php echo date('d F Y, H:i', strtotime($notif['tanggal'])); ?></span>
                                        <?php if($notif['komplain_id']): ?>
                                        <span><i class="fas fa-link"></i> Komplain #<?php echo $notif['komplain_id']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                    <div style="text-align: center; padding: 5rem 2rem; color: #6b7280;">
                        <i class="fas fa-bell-slash" style="font-size: 5rem; margin-bottom: 1.5rem; opacity: 0.3;"></i>
                        <h3 style="margin-bottom: 1rem; color: var(--dark);">Tidak Ada Notifikasi</h3>
                        <p>
                            <?php if($filter == 'unread'): ?>
                                Semua notifikasi sudah dibaca
                            <?php elseif($filter == 'read'): ?>
                                Belum ada notifikasi yang dibaca
                            <?php else: ?>
                                Anda belum memiliki notifikasi
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
</body>
</html>