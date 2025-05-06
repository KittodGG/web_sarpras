<?php
/**
 * Dashboard admin
 */

// Query untuk statistik
$totalSarpras = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM sarpras"))['total'];
$totalPinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'Dipinjam'"))['total'];
$totalPengguna = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'user'"))['total'];
$totalPending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'Menunggu'"))['total'];

// Query untuk item terbaru
$latestItems = mysqli_query($conn, "SELECT s.*, k.nama as nama_kategori 
                                   FROM sarpras s 
                                   JOIN kategori k ON s.kategori_id = k.id 
                                   ORDER BY s.created_at DESC LIMIT 5");

// Query untuk peminjaman terbaru
$latestLoans = mysqli_query($conn, "SELECT p.*, s.nama as nama_sarpras, u.nama_lengkap as nama_peminjam 
                                   FROM peminjaman p 
                                   JOIN sarpras s ON p.sarpras_id = s.id 
                                   JOIN users u ON p.user_id = u.id 
                                   ORDER BY p.created_at DESC LIMIT 5");

// Query untuk masalah terbaru
$latestIssues = mysqli_query($conn, "SELECT m.*, p.kode_peminjaman, s.nama as nama_sarpras, u.nama_lengkap as nama_peminjam 
                                    FROM masalah m 
                                    JOIN peminjaman p ON m.peminjaman_id = p.id 
                                    JOIN sarpras s ON p.sarpras_id = s.id 
                                    JOIN users u ON p.user_id = u.id 
                                    ORDER BY m.created_at DESC LIMIT 5");

// Query untuk statistik kategori
$categoryStats = mysqli_query($conn, "SELECT k.nama, COUNT(s.id) as jumlah 
                                     FROM kategori k 
                                     LEFT JOIN sarpras s ON k.id = s.kategori_id 
                                     GROUP BY k.id 
                                     ORDER BY jumlah DESC");

// Konversi data kategori untuk chart
$categoryLabels = [];
$categoryData = [];
while ($row = mysqli_fetch_assoc($categoryStats)) {
    $categoryLabels[] = $row['nama'];
    $categoryData[] = $row['jumlah'];
}

// Query untuk statistik status sarpras
$statusStats = mysqli_query($conn, "SELECT 
                                   SUM(CASE WHEN tersedia = stok THEN 1 ELSE 0 END) as tersedia,
                                   SUM(CASE WHEN tersedia < stok AND tersedia > 0 THEN 1 ELSE 0 END) as sebagian,
                                   SUM(CASE WHEN tersedia = 0 THEN 1 ELSE 0 END) as habis,
                                   SUM(CASE WHEN kondisi = 'Rusak Ringan' THEN 1 ELSE 0 END) as rusak_ringan,
                                   SUM(CASE WHEN kondisi = 'Rusak Berat' THEN 1 ELSE 0 END) as rusak_berat
                                   FROM sarpras");
$statusData = mysqli_fetch_assoc($statusStats);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <div>
            <span class="text-gray">Hari ini: <?php echo getHariIndonesia(date('Y-m-d')) . ', ' . getTanggalIndonesia(date('Y-m-d')); ?></span>
        </div>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <!-- Statistik Utama -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $totalSarpras; ?></div>
                            <div class="stat-text">Total Sarpras</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: var(--color-secondary);">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $totalPinjam; ?></div>
                            <div class="stat-text">Sedang Dipinjam</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: var(--color-accent);">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $totalPengguna; ?></div>
                            <div class="stat-text">Total Pengguna</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: var(--color-danger);">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $totalPending; ?></div>
                            <div class="stat-text">Menunggu Persetujuan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Baris Konten Utama -->
    <div class="row g-4">
        <!-- Kolom Kiri - Grafik -->
        <div class="col-lg-8">
            <div class="row g-4">
                <!-- Grafik Peminjaman -->
                <div class="col-12">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0 font-weight-bold">Statistik Peminjaman Bulanan</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-download me-2"></i> Export</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bi bi-arrow-repeat me-2"></i> Refresh</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="height: 300px;">
                                <canvas id="statsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Grafik Kategori dan Status -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="m-0 font-weight-bold">Distribusi Kategori</h5>
                        </div>
                        <div class="card-body">
                            <div style="height: 250px;">
                                <canvas id="categoriesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="m-0 font-weight-bold">Status Sarpras</h5>
                        </div>
                        <div class="card-body">
                            <div style="height: 250px;">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kolom Kanan - Aktivitas -->
        <div class="col-lg-4">
            <!-- Permintaan Peminjaman Terbaru -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold">Peminjaman Terbaru</h5>
                    <a href="<?php echo ROOT_URL; ?>/peminjaman" class="btn btn-sm btn-primary">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (mysqli_num_rows($latestLoans) > 0): ?>
                            <?php while ($loan = mysqli_fetch_assoc($latestLoans)): ?>
                                <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $loan['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo $loan['nama_sarpras']; ?></h6>
                                            <small class="text-muted">Peminjam: <?php echo $loan['nama_peminjam']; ?></small>
                                        </div>
                                        <span class="badge rounded-pill 
                                            <?php 
                                                if ($loan['status'] == 'Menunggu') echo 'bg-warning';
                                                else if ($loan['status'] == 'Disetujui') echo 'bg-info';
                                                else if ($loan['status'] == 'Dipinjam') echo 'bg-primary';
                                                else if ($loan['status'] == 'Dikembalikan') echo 'bg-success';
                                                else if ($loan['status'] == 'Ditolak') echo 'bg-danger';
                                                else if ($loan['status'] == 'Terlambat') echo 'bg-danger';
                                            ?>">
                                            <?php echo $loan['status']; ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3"></i> <?php echo formatDate($loan['tanggal_pinjam']); ?> - <?php echo formatDate($loan['tanggal_kembali']); ?>
                                    </small>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <div class="mb-2"><i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i></div>
                                <p class="mb-0 text-muted">Belum ada data peminjaman</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Masalah Terbaru -->
            
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data untuk chart kategori
    const categoryLabels = <?php echo json_encode($categoryLabels); ?>;
    const categoryData = <?php echo json_encode($categoryData); ?>;
    
    // Data untuk chart status
    const statusData = {
        labels: ['Tersedia', 'Sebagian', 'Habis', 'Rusak Ringan', 'Rusak Berat'],
        data: [
            <?php echo $statusData['tersedia']; ?>,
            <?php echo $statusData['sebagian']; ?>,
            <?php echo $statusData['habis']; ?>,
            <?php echo $statusData['rusak_ringan']; ?>,
            <?php echo $statusData['rusak_berat']; ?>
        ]
    };
    
    // Setup untuk chart kategori
    const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
    new Chart(categoriesCtx, {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryData,
                backgroundColor: [
                    'rgba(147, 122, 102, 0.7)',
                    'rgba(78, 56, 43, 0.7)',
                    'rgba(84, 40, 39, 0.7)',
                    'rgba(58, 16, 28, 0.7)',
                    'rgba(116, 112, 113, 0.7)',
                    'rgba(40, 167, 69, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(23, 162, 184, 0.7)'
                ],
                borderColor: [
                    'rgba(147, 122, 102, 1)',
                    'rgba(78, 56, 43, 1)',
                    'rgba(84, 40, 39, 1)',
                    'rgba(58, 16, 28, 1)',
                    'rgba(116, 112, 113, 1)',
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(23, 162, 184, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
    
    // Setup untuk chart status
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: statusData.labels,
            datasets: [{
                label: 'Status Sarpras',
                data: statusData.data,
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(23, 162, 184, 0.7)',
                    'rgba(108, 117, 125, 0.7)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(108, 117, 125, 1)'
                ],
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
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Setup untuk chart statistik bulanan
    const statsCtx = document.getElementById('statsChart').getContext('2d');
    new Chart(statsCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Peminjaman',
                data: [12, 19, 3, 5, 2, 3, 7, 8, 9, 10, 11, 5],
                backgroundColor: 'rgba(78, 56, 43, 0.2)',
                borderColor: 'rgba(78, 56, 43, 1)',
                borderWidth: 2,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 5
                    }
                }
            }
        }
    });
});
</script>