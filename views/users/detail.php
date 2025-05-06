<?php
/**
 * Halaman untuk detail pengguna
 */

// Load model
require_once('models/User.php');
require_once('models/Peminjaman.php');

// Inisialisasi model
$userModel = new User($conn);
$peminjamanModel = new Peminjaman($conn);

// Dapatkan ID pengguna dari URL
$id = isset($param) ? (int)$param : 0;

// Jika ID tidak valid, redirect ke halaman pengguna
if ($id <= 0) {
    $_SESSION['message'] = "ID Pengguna tidak valid!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/users');
}

// Get data pengguna berdasarkan ID
$user = $userModel->getUserById($id);

// Jika data tidak ditemukan, redirect ke halaman pengguna
if (!$user) {
    $_SESSION['message'] = "Data Pengguna tidak ditemukan!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/users');
}

// Pagination untuk peminjaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Get data peminjaman user
$peminjaman = $peminjamanModel->getPeminjamanByUser($id, $offset, $limit);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Pengguna</h1>
        <div>
            <?php if ($_SESSION['role'] == 'admin'): ?>
            <a href="<?php echo ROOT_URL; ?>/users/edit/<?php echo $id; ?>" class="btn btn-warning text-white me-2">
                <i class="bi bi-pencil me-2"></i> Edit
            </a>
            <?php endif; ?>
            <a href="<?php echo ROOT_URL; ?>/users" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <div class="row">
        <div class="col-lg-4 mb-4">
            <!-- User Profile Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge me-2"></i> Profil Pengguna
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php if (empty($user['foto'])): ?>
                            <img src="<?php echo ROOT_URL; ?>/assets/img/avatar.png" alt="User Avatar" class="img-profile rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <img src="<?php echo ROOT_URL; ?>/uploads/<?php echo $user['foto']; ?>" alt="User Avatar" class="img-profile rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php endif; ?>
                        <h4 class="mb-0"><?php echo $user['nama_lengkap']; ?></h4>
                        <span class="badge rounded-pill <?php echo ($user['role'] == 'admin') ? 'bg-warning text-dark' : 'bg-info'; ?> mt-2">
                            <?php echo ($user['role'] == 'admin') ? 'Administrator' : 'Siswa'; ?>
                        </span>
                    </div>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-person me-2"></i> Username</div>
                            <div class="info-value"><?php echo $user['username']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-envelope me-2"></i> Email</div>
                            <div class="info-value"><?php echo $user['email']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-telephone me-2"></i> No. Telepon</div>
                            <div class="info-value"><?php echo !empty($user['no_telp']) ? $user['no_telp'] : '<span class="text-muted">-</span>'; ?></div>
                        </div>
                        
                        <?php if ($user['role'] == 'user' && !empty($user['nis'])): ?>
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-card-text me-2"></i> NIS</div>
                            <div class="info-value"><?php echo $user['nis']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-buildings me-2"></i> Jurusan</div>
                            <div class="info-value"><?php echo $user['jurusan']; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-people me-2"></i> Kelas</div>
                            <div class="info-value"><?php echo $user['kelas']; ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <div class="info-label"><i class="bi bi-calendar me-2"></i> Terdaftar</div>
                            <div class="info-value"><?php echo formatDate($user['created_at']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- User Activity -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i> Riwayat Peminjaman
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (count($peminjaman) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Sarpras</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($peminjaman as $pinjam): ?>
                                <tr>
                                    <td><strong><?php echo $pinjam['kode']; ?></strong></td>
                                    <td><?php echo $pinjam['nama_sarpras']; ?></td>
                                    <td>
                                        <?php echo formatDate($pinjam['tanggal_pinjam']); ?> - 
                                        <?php echo formatDate($pinjam['tanggal_kembali']); ?>
                                    </td>
                                    <td>
                                        <?php echo getStatusBadge($pinjam['status']); ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $pinjam['id']; ?>" class="btn btn-sm btn-info text-white">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i> Belum ada riwayat peminjaman.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- User Statistics -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bar-chart me-2"></i> Statistik Peminjaman
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">Peminjaman Aktif</h6>
                                            <small class="text-muted">Status: Dipinjam</small>
                                        </div>
                                        <h3 class="mb-0 text-primary">
                                            <?php 
                                            $active = 0;
                                            foreach($peminjaman as $pinjam) {
                                                if ($pinjam['status'] == 'Dipinjam') $active++;
                                            }
                                            echo $active;
                                            ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">Total Peminjaman</h6>
                                            <small class="text-muted">Semua waktu</small>
                                        </div>
                                        <h3 class="mb-0 text-primary"><?php echo count($peminjaman); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-list {
    margin-top: 20px;
}
.info-item {
    margin-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 10px;
}
.info-label {
    font-weight: bold;
    color: #6c757d;
    font-size: 0.9rem;
}
.info-value {
    font-size: 1rem;
    padding-left: 28px;
    margin-top: 3px;
}
</style>

<?php
/**
 * Helper function to generate status badge
 */
function getStatusBadge($status) {
    switch ($status) {
        case 'Menunggu':
            return '<span class="badge bg-warning text-dark">Menunggu</span>';
        case 'Disetujui':
            return '<span class="badge bg-info">Disetujui</span>';
        case 'Ditolak':
            return '<span class="badge bg-danger">Ditolak</span>';
        case 'Dipinjam':
            return '<span class="badge bg-primary">Dipinjam</span>';
        case 'Dikembalikan':
            return '<span class="badge bg-success">Dikembalikan</span>';
        case 'Terlambat':
            return '<span class="badge bg-danger">Terlambat</span>';
        default:
            return '<span class="badge bg-secondary">' . $status . '</span>';
    }
}
?> 