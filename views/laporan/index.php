<?php
/**
 * Halaman utama laporan
 */

// Load model yang diperlukan
require_once('models/Peminjaman.php');
require_once('models/Pengembalian.php');
require_once('models/Sarpras.php');
require_once('models/User.php');

// Inisialisasi model
$peminjamanModel = new Peminjaman($conn);
$pengembalianModel = new Pengembalian($conn);
$sarprasModel = new Sarpras($conn);
$userModel = new User($conn);

// Mendapatkan statistik untuk dashboard laporan
$statPeminjaman = $peminjamanModel->getStatistik();
$statPengembalian = $pengembalianModel->getStatistik();
$statSarpras = $sarprasModel->getStatistik();
$statUser = $userModel->getStatistik();

// Data untuk grafik peminjaman bulanan
$monthlyStats = $peminjamanModel->getMonthlyStats();

// Mendapatkan 5 sarpras paling sering dipinjam
$query = "SELECT s.id, s.nama, s.kode, COUNT(p.id) as total_peminjaman
          FROM sarpras s
          JOIN peminjaman p ON s.id = p.sarpras_id
          GROUP BY s.id
          ORDER BY total_peminjaman DESC
          LIMIT 5";
$result = mysqli_query($conn, $query);
$topSarpras = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $topSarpras[] = $row;
    }
}

// Mendapatkan 5 peminjam paling aktif
$query = "SELECT u.id, u.nama_lengkap, COUNT(p.id) as total_peminjaman
          FROM users u
          JOIN peminjaman p ON u.id = p.user_id
          GROUP BY u.id
          ORDER BY total_peminjaman DESC
          LIMIT 5";
$result = mysqli_query($conn, $query);
$topUsers = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $topUsers[] = $row;
    }
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan</h1>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <!-- Menu Laporan -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <a href="<?php echo ROOT_URL; ?>/laporan/peminjaman" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="icon-box bg-primary text-white me-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-arrow-up-right-circle"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Laporan Peminjaman</h5>
                            <p class="mb-0 text-muted small">Data peminjaman berdasarkan periode</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3">
            <a href="<?php echo ROOT_URL; ?>/laporan/pengembalian" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="icon-box bg-success text-white me-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-arrow-down-left-circle"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Laporan Pengembalian</h5>
                            <p class="mb-0 text-muted small">Data pengembalian berdasarkan periode</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3">
            <a href="<?php echo ROOT_URL; ?>/laporan/sarpras" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="icon-box bg-info text-white me-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Laporan Sarpras</h5>
                            <p class="mb-0 text-muted small">Data kondisi dan penggunaan sarpras</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3">
            <a href="<?php echo ROOT_URL; ?>/laporan/masalah" class="text-decoration-none">
                <div class="card shadow-sm h-100 hover-card">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="icon-box bg-warning text-dark me-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Laporan Masalah</h5>
                            <p class="mb-0 text-muted small">Data masalah dan kerusakan</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <!-- Statistik Keseluruhan -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i> Statistik Keseluruhan</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-primary border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Sarpras</h6>
                                    <h3 class="mb-0"><?php echo $statSarpras['total']; ?></h3>
                                </div>
                                <div class="stat-icon-light bg-primary-light text-primary">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i> <?php echo $statSarpras['kondisi_baik']; ?> Baik
                                </span>
                                <span class="badge bg-warning ms-1">
                                    <i class="bi bi-exclamation-circle me-1"></i> <?php echo $statSarpras['rusak_ringan']; ?> Rusak Ringan
                                </span>
                                <span class="badge bg-danger ms-1">
                                    <i class="bi bi-x-circle me-1"></i> <?php echo $statSarpras['rusak_berat']; ?> Rusak Berat
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-success border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Peminjaman</h6>
                                    <h3 class="mb-0"><?php echo $statPeminjaman['total']; ?></h3>
                                </div>
                                <div class="stat-icon-light bg-success-light text-success">
                                    <i class="bi bi-arrow-up-right-circle"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-warning">
                                    <i class="bi bi-hourglass-split me-1"></i> <?php echo $statPeminjaman['menunggu']; ?> Menunggu
                                </span>
                                <span class="badge bg-primary ms-1">
                                    <i class="bi bi-arrow-repeat me-1"></i> <?php echo $statPeminjaman['dipinjam']; ?> Aktif
                                </span>
                                <span class="badge bg-success ms-1">
                                    <i class="bi bi-check-circle me-1"></i> <?php echo $statPeminjaman['dikembalikan']; ?> Selesai
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-info border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Pengembalian</h6>
                                    <h3 class="mb-0"><?php echo $statPengembalian['total']; ?></h3>
                                </div>
                                <div class="stat-icon-light bg-info-light text-info">
                                    <i class="bi bi-arrow-down-left-circle"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i> <?php echo $statPengembalian['kondisi_baik']; ?> Kondisi Baik
                                </span>
                                <span class="badge bg-warning ms-1">
                                    <i class="bi bi-tools me-1"></i> <?php echo $statPengembalian['rusak_ringan'] + $statPengembalian['rusak_berat']; ?> Rusak
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 border-start border-danger border-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Pengguna</h6>
                                    <h3 class="mb-0"><?php echo $statUser['total']; ?></h3>
                                </div>
                                <div class="stat-icon-light bg-danger-light text-danger">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-primary">
                                    <i class="bi bi-person-badge me-1"></i> <?php echo $statUser['admin']; ?> Admin
                                </span>
                                <span class="badge bg-secondary ms-1">
                                    <i class="bi bi-person me-1"></i> <?php echo $statUser['user']; ?> Siswa
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Grafik Peminjaman -->
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i> Grafik Peminjaman Bulanan</h5>
                </div>
                <div class="card-body">
                    <canvas id="peminjaman-chart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Ringkasan Laporan -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-data me-2"></i> Ringkasan Laporan</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Peminjaman Bulan Ini:</span>
                        <span class="fw-bold"><?php echo $monthlyStats['data'][date('n') - 1]; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Peminjaman Sedang Aktif:</span>
                        <span class="fw-bold"><?php echo $statPeminjaman['dipinjam']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Menunggu Persetujuan:</span>
                        <span class="fw-bold"><?php echo $statPeminjaman['menunggu']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Peminjaman Terlambat:</span>
                        <span class="fw-bold text-danger"><?php echo $statPeminjaman['terlambat']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pengembalian Belum Verifikasi:</span>
                        <span class="fw-bold"><?php echo $statPengembalian['belum_verifikasi']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sarpras Tidak Tersedia:</span>
                        <span class="fw-bold"><?php echo $statSarpras['tidak_tersedia']; ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid gap-2 mt-3">
                        <a href="<?php echo ROOT_URL; ?>/laporan/peminjaman" class="btn btn-primary">
                            <i class="bi bi-file-earmark-text me-2"></i> Buat Laporan Lengkap
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mt-2">
        <!-- Sarpras Paling Sering Dipinjam -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i> Sarpras Paling Sering Dipinjam</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($topSarpras)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Sarpras</th>
                                    <th>Total Peminjaman</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topSarpras as $index => $sarpras): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $sarpras['kode']; ?></td>
                                    <td><?php echo $sarpras['nama']; ?></td>
                                    <td><span class="badge bg-primary"><?php echo $sarpras['total_peminjaman']; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">Belum ada data peminjaman</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Peminjam Paling Aktif -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i> Peminjam Paling Aktif</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($topUsers)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Peminjam</th>
                                    <th>Total Peminjaman</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topUsers as $index => $user): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $user['nama_lengkap']; ?></td>
                                    <td><span class="badge bg-primary"><?php echo $user['total_peminjaman']; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">Belum ada data peminjaman</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-box {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon-light {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.bg-primary-light {
    background-color: rgba(78, 56, 43, 0.1);
}

.bg-success-light {
    background-color: rgba(40, 167, 69, 0.1);
}

.bg-info-light {
    background-color: rgba(23, 162, 184, 0.1);
}

.bg-danger-light {
    background-color: rgba(58, 16, 28, 0.1);
}

.hover-card {
    transition: transform 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
}
</style>

<script>
// Grafik Peminjaman Bulanan
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('peminjaman-chart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($monthlyStats['labels']); ?>,
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: <?php echo json_encode($monthlyStats['data']); ?>,
                backgroundColor: 'rgba(78, 56, 43, 0.7)',
                borderColor: 'rgba(78, 56, 43, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>