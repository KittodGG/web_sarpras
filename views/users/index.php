<?php
/**
 * Halaman untuk manajemen pengguna
 */

// Load model
require_once('models/User.php');

// Inisialisasi model
$userModel = new User($conn);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter dan pencarian
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$role = isset($_GET['role']) ? sanitize($_GET['role']) : '';

// Get data pengguna
$users = $userModel->getAllUsers($offset, $limit, $search, $role);
$totalUsers = $userModel->getTotalUsers($search, $role);

// Hitung pagination
$totalPages = ceil($totalUsers / $limit);

// Proses hapus data jika ada
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    
    if ($userModel->deleteUser($id)) {
        $_SESSION['message'] = "Data pengguna berhasil dihapus!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus data pengguna! Pengguna masih memiliki peminjaman aktif atau terjadi kesalahan.";
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirect untuk refresh halaman
    redirect(ROOT_URL . '/users');
}

// Get data statistik
$statistik = $userModel->getStatistik();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Pengguna</h1>
        <a href="<?php echo ROOT_URL; ?>/users/add" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i> Tambah Pengguna
        </a>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <!-- Statistik Pengguna -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['total']; ?></div>
                            <div class="stat-text">Total Pengguna</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning text-dark">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['admin']; ?></div>
                            <div class="stat-text">Administrator</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-info">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['user']; ?></div>
                            <div class="stat-text">Siswa/Pengguna</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter dan pencarian -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo ROOT_URL; ?>/users" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" placeholder="Cari nama, username, email, atau NIS..." value="<?php echo $search; ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <select name="role" class="form-select">
                        <option value="">-- Semua Role --</option>
                        <option value="admin" <?php echo ($role == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                        <option value="user" <?php echo ($role == 'user') ? 'selected' : ''; ?>>Siswa/Pengguna</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabel Pengguna -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-people me-2"></i> Daftar Pengguna
            </h5>
            <div>
                <button class="btn btn-sm btn-light" onclick="printData('dataUsers')">
                    <i class="bi bi-printer"></i>
                </button>
                <button class="btn btn-sm btn-light" onclick="exportTableToExcel('dataTable', 'data_pengguna')">
                    <i class="bi bi-file-excel"></i>
                </button>
            </div>
        </div>
        <div class="card-body table-responsive" id="dataUsers">
            <?php if (count($users) > 0): ?>
            <table class="table table-hover table-bordered datatable" id="dataTable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Username</th>
                        <th width="20%">Nama Lengkap</th>
                        <th width="15%">Email</th>
                        <th width="10%">Role</th>
                        <th width="15%">Info</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $index => $user): ?>
                    <tr>
                        <td class="text-center"><?php echo $offset + $index + 1; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['nama_lengkap']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td class="text-center">
                            <span class="badge rounded-pill <?php echo ($user['role'] == 'admin') ? 'bg-warning text-dark' : 'bg-info'; ?>">
                                <?php echo ($user['role'] == 'admin') ? 'Administrator' : 'Siswa'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['role'] == 'user' && !empty($user['nis'])): ?>
                            <small>
                                <i class="bi bi-card-text me-1"></i> <?php echo $user['nis']; ?><br>
                                <i class="bi bi-buildings me-1"></i> <?php echo $user['jurusan']; ?><br>
                                <i class="bi bi-people me-1"></i> <?php echo $user['kelas']; ?>
                            </small>
                            <?php else: ?>
                            <small class="text-muted">Tidak ada info tambahan</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo ROOT_URL; ?>/users/detail/<?php echo $user['id']; ?>" class="btn btn-sm btn-info text-white mb-1" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?php echo ROOT_URL; ?>/users/edit/<?php echo $user['id']; ?>" class="btn btn-sm btn-warning text-white mb-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger mb-1" title="Hapus" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo $user['nama_lengkap']; ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/users?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&role=<?php echo $role; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/users?page=<?php echo $i; ?>&search=<?php echo $search; ?>&role=<?php echo $role; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/users?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&role=<?php echo $role; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <img src="<?php echo ROOT_URL; ?>/assets/img/empty.svg" alt="Data tidak ditemukan" class="img-fluid mb-3" style="max-height: 200px;">
                <h5>Data Pengguna Tidak Ditemukan</h5>
                <p class="text-muted">Belum ada data pengguna atau tidak ada data yang sesuai dengan filter yang dipilih.</p>
                <a href="<?php echo ROOT_URL; ?>/users/add" class="btn btn-primary mt-3">
                    <i class="bi bi-person-plus me-2"></i> Tambah Pengguna Baru
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pengguna <strong id="itemName"></strong>?</p>
                <p class="text-danger mb-0">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('itemName').textContent = name;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>