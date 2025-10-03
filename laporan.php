<?php
require_once 'config.php';

if(!isLoggedIn() || !isAdmin()) {
    redirect('dashboard.php');
}

// Date filter
$start_date = isset($_GET['start_date']) ? clean($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? clean($_GET['end_date']) : date('Y-m-d');

// General Statistics
$total_komplain = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE tanggal_submit BETWEEN '$start_date' AND '$end_date 23:59:59'"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE status='pending' AND tanggal_submit BETWEEN '$start_date' AND '$end_date 23:59:59'"))['total'];
$diproses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE status='diproses' AND tanggal_submit BETWEEN '$start_date' AND '$end_date 23:59:59'"))['total'];
$selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE status='selesai' AND tanggal_submit BETWEEN '$start_date' AND '$end_date 23:59:59'"))['total'];
$ditolak = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM komplain WHERE status='ditolak' AND tanggal_submit BETWEEN '$start_date' AND '$end_date 23:59:59'"))['total'];

// By Category
$kategori_result = mysqli_query($conn, "SELECT kategori, COUNT(*) as total FROM komplain WHERE tanggal_submit BETWEEN '$start_date' AND '$end_date 23:59:59' GROUP BY kategori ORDER BY total DESC");

// By Priority
$prioritas_result = mysqli_query($conn, "SELECT prioritas, COUNT(*) as total FROM komplain WHERE tanggal_submit BETWEEN '$start_date' AND '$end_date 23:59:59' GROUP BY prioritas ORDER BY FIELD(prioritas, 'tinggi', 'sedang', 'rendah')");

// Top Complainants
$top_users = mysqli_query($conn, "SELECT u.nama, u.nim, u.jurusan, COUNT(k.id) as total FROM komplain k JOIN users u ON k.user_id = u.id WHERE k.tanggal_submit BETWEEN '$start_date' AND '$end_date 23:59:59' GROUP BY k.user_id ORDER BY total DESC LIMIT 10");

// Response Time Analysis
$response_time = mysqli_query($conn, "SELECT k.id, k.judul, k.tanggal_submit, MIN(t.tanggal) as first_response, TIMESTAMPDIFF(HOUR, k.tanggal_submit, MIN(t.tanggal)) as response_hours FROM komplain k LEFT JOIN tanggapan t ON k.id = t.komplain_id WHERE k.tanggal_submit BETWEEN '$start_date' AND '$end_date 23:59:59' GROUP BY k.id HAVING first_response IS NOT NULL ORDER BY response_hours DESC LIMIT 10");

// Daily Trend
$daily_trend = mysqli_query($conn, "SELECT DATE(tanggal_submit) as tanggal, COUNT(*) as total FROM komplain WHERE tanggal_submit BETWEEN '$start_date' AND '$end_date 23:59:59' GROUP BY DATE(tanggal_submit) ORDER BY tanggal");

// Average resolution time
$avg_resolution = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(TIMESTAMPDIFF(DAY, tanggal_submit, tanggal_update)) as avg_days FROM komplain WHERE status='selesai' AND tanggal_submit BETWEEN '$start_date' AND '$end_date 23:59:59'"))['avg_days'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Komplain FST</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="pengguna.php">Pengguna</a></li>
                <li><a href="laporan.php" class="active"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <li><a href="profil.php"><?php echo $_SESSION['nama']; ?></a></li>
                <li><a href="logout.php" class="btn-nav-outline">Logout</a></li>
            </ul>
        </div>
    </nav>

    <section class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <div>
                    <h1><i class="fas fa-chart-bar"></i> Laporan & Statistik</h1>
                    <p>Analisis data komplain mahasiswa</p>
                </div>
                <button onclick="window.print()" class="btn-primary">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>

            <!-- Date Filter -->
            <div class="card" style="margin-bottom: 2rem;">
                <div style="padding: 1.5rem;">
                    <form method="GET" action="">
                        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
                            <div class="form-group">
                                <label for="start_date"><i class="fas fa-calendar"></i> Tanggal Mulai</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="form-group">
                                <label for="end_date"><i class="fas fa-calendar"></i> Tanggal Akhir</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary-form">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistik Umum -->
            <div class="grid-5">
                <div class="stat-card">Total: <strong><?php echo $total_komplain; ?></strong></div>
                <div class="stat-card">Pending: <strong><?php echo $pending; ?></strong></div>
                <div class="stat-card">Diproses: <strong><?php echo $diproses; ?></strong></div>
                <div class="stat-card">Selesai: <strong><?php echo $selesai; ?></strong></div>
                <div class="stat-card">Ditolak: <strong><?php echo $ditolak; ?></strong></div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <h3>Kategori Komplain</h3>
                    <canvas id="kategoriChart"></canvas>
                </div>
                <div class="card">
                    <h3>Prioritas Komplain</h3>
                    <canvas id="prioritasChart"></canvas>
                </div>
            </div>

            <div class="card">
                <h3>Tren Harian</h3>
                <canvas id="trendChart"></canvas>
            </div>

            <div class="grid-2">
                <div class="card">
                    <h3>Top 10 Pengguna</h3>
                    <table>
                        <tr><th>Nama</th><th>NIM</th><th>Jurusan</th><th>Total</th></tr>
                        <?php while($row = mysqli_fetch_assoc($top_users)): ?>
                            <tr>
                                <td><?php echo $row['nama']; ?></td>
                                <td><?php echo $row['nim']; ?></td>
                                <td><?php echo $row['jurusan']; ?></td>
                                <td><?php echo $row['total']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
                <div class="card">
                    <h3>Respon Tercepat / Terlambat</h3>
                    <table>
                        <tr><th>Judul</th><th>Submit</th><th>First Response</th><th>Jam</th></tr>
                        <?php while($row = mysqli_fetch_assoc($response_time)): ?>
                            <tr>
                                <td><?php echo $row['judul']; ?></td>
                                <td><?php echo $row['tanggal_submit']; ?></td>
                                <td><?php echo $row['first_response']; ?></td>
                                <td><?php echo $row['response_hours']; ?> jam</td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3>Rata-rata Waktu Penyelesaian</h3>
                <p><strong><?php echo round($avg_resolution,2); ?> hari</strong></p>
            </div>
        </div>
    </section>

<script>
    // Kategori
    const kategoriData = {
        labels: [<?php while($k = mysqli_fetch_assoc($kategori_result)) { echo "'".$k['kategori']."',"; } ?>],
        datasets: [{
            data: [<?php mysqli_data_seek($kategori_result,0); while($k = mysqli_fetch_assoc($kategori_result)) { echo $k['total'].","; } ?>],
            backgroundColor: ['#4CAF50','#FFC107','#F44336','#2196F3','#9C27B0']
        }]
    };
    new Chart(document.getElementById('kategoriChart'), { type:'pie', data:kategoriData });

    // Prioritas
    const prioritasData = {
        labels: [<?php while($p = mysqli_fetch_assoc($prioritas_result)) { echo "'".$p['prioritas']."',"; } ?>],
        datasets: [{
            data: [<?php mysqli_data_seek($prioritas_result,0); while($p = mysqli_fetch_assoc($prioritas_result)) { echo $p['total'].","; } ?>],
            backgroundColor: ['#F44336','#FFC107','#4CAF50']
        }]
    };
    new Chart(document.getElementById('prioritasChart'), { type:'doughnut', data:prioritasData });

    // Tren Harian
    const trendData = {
        labels: [<?php while($d = mysqli_fetch_assoc($daily_trend)) { echo "'".$d['tanggal']."',"; } ?>],
        datasets: [{
            label: 'Jumlah Komplain',
            data: [<?php mysqli_data_seek($daily_trend,0); while($d = mysqli_fetch_assoc($daily_trend)) { echo $d['total'].","; } ?>],
            borderColor: '#2196F3',
            fill:false,
            tension:0.1
        }]
    };
    new Chart(document.getElementById('trendChart'), { type:'line', data:trendData });
</script>
</body>
</html>