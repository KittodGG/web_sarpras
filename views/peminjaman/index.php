<?php
/**
 * Halaman untuk manajemen peminjaman
 */

// Load model
require_once('models/Peminjaman.php');

// Inisialisasi model
$peminjamanModel = new Peminjaman($conn);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter dan pencarian
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Get data peminjaman
$peminjaman = $peminjamanModel->getAllPeminjaman($offset, $limit, $search, $status);
$totalPeminjaman = $peminjamanModel->getTotalPeminjaman($search, $status);

// Hitung pagination
$totalPages = ceil($totalPeminjaman / $limit);

// Proses hapus data jika ada
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    
    if ($peminjamanModel->deletePeminjaman($id)) {
        $_SESSION['message'] = "Data peminjaman berhasil dihapus!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus data peminjaman! Hanya peminjaman dengan status Menunggu atau Ditolak yang dapat dihapus.";
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirect untuk refresh halaman
    redirect(ROOT_URL . '/peminjaman');
}

// Statistik peminjaman
$statistik = $peminjamanModel->getStatistik();

// Update status peminjaman yang terlambat
$peminjamanModel->updateLateLoans();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Peminjaman</h1>
        <a href="<?php echo ROOT_URL; ?>/peminjaman/add" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i> Tambah Peminjaman
        </a>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <!-- Statistik Peminjaman -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['menunggu']; ?></div>
                            <div class="stat-text">Menunggu</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-info text-white">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['disetujui']; ?></div>
                            <div class="stat-text">Disetujui</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <i class="bi bi-arrow-up-right-circle"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['dipinjam']; ?></div>
                            <div class="stat-text">Dipinjam</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-success">
                            <i class="bi bi-arrow-down-left-circle"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['dikembalikan']; ?></div>
                            <div class="stat-text">Dikembalikan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-danger">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['ditolak']; ?></div>
                            <div class="stat-text">Ditolak</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #dc3545;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['terlambat']; ?></div>
                            <div class="stat-text">Terlambat</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter dan pencarian -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo ROOT_URL; ?>/peminjaman" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" placeholder="Cari kode, nama peminjam, atau nama sarpras..." value="<?php echo $search; ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="Menunggu" <?php echo ($status == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="Disetujui" <?php echo ($status == 'Disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                        <option value="Ditolak" <?php echo ($status == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                        <option value="Dipinjam" <?php echo ($status == 'Dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                        <option value="Dikembalikan" <?php echo ($status == 'Dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                        <option value="Terlambat" <?php echo ($status == 'Terlambat') ? 'selected' : ''; ?>>Terlambat</option>
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
    
    <!-- Tabel Peminjaman -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i> Daftar Peminjaman
            </h5>
            <div>
                <button class="btn btn-sm btn-light" onclick="printData('dataPeminjaman')">
                    <i class="bi bi-printer"></i>
                </button>
                <button class="btn btn-sm btn-light" onclick="exportTableToExcel('dataTable', 'data_peminjaman')">
                    <i class="bi bi-file-excel"></i>
                </button>
            </div>
        </div>
        <div class="card-body table-responsive" id="dataPeminjaman">
            <?php if (count($peminjaman) > 0): ?>
            <table class="table table-hover table-bordered datatable" id="dataTable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="12%">Kode</th>
                        <th width="20%">Sarpras</th>
                        <th width="15%">Peminjam</th>
                        <th width="10%">Tgl Pinjam</th>
                        <th width="10%">Tgl Kembali</th>
                        <th width="8%">Status</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peminjaman as $index => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo $offset + $index + 1; ?></td>
                        <td><?php echo $item['kode_peminjaman']; ?></td>
                        <td><?php echo $item['nama_sarpras']; ?></td>
                        <td><?php echo $item['nama_peminjam']; ?></td>
                        <td><?php echo formatDate($item['tanggal_pinjam']); ?></td>
                        <td><?php echo formatDate($item['tanggal_kembali']); ?></td>
                        <td>
                            <span class="badge rounded-pill 
                                <?php 
                                    if ($item['status'] == 'Menunggu') echo 'bg-warning';
                                    else if ($item['status'] == 'Disetujui') echo 'bg-info';
                                    else if ($item['status'] == 'Dipinjam') echo 'bg-primary';
                                    else if ($item['status'] == 'Dikembalikan') echo 'bg-success';
                                    else if ($item['status'] == 'Ditolak') echo 'bg-danger';
                                    else if ($item['status'] == 'Terlambat') echo 'bg-danger';
                                ?>">
                                <?php echo $item['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $item['id']; ?>" class="btn btn-sm btn-info text-white mb-1" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            <?php if ($item['status'] == 'Menunggu'): ?>
                            <a href="<?php echo ROOT_URL; ?>/peminjaman/approve/<?php echo $item['id']; ?>" class="btn btn-sm btn-success mb-1" title="Proses Persetujuan">
                                <i class="bi bi-check-circle"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($item['status'] == 'Disetujui'): ?>
                            <a href="<?php echo ROOT_URL; ?>/peminjaman/edit/<?php echo $item['id']; ?>" class="btn btn-sm btn-primary mb-1" title="Ubah Status">
                                <i class="bi bi-arrow-up-right-circle"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($item['status'] == 'Dipinjam' || $item['status'] == 'Terlambat'): ?>
                            <a href="<?php echo ROOT_URL; ?>/pengembalian/add/<?php echo $item['id']; ?>" class="btn btn-sm btn-success mb-1" title="Proses Pengembalian">
                                <i class="bi bi-arrow-down-left-circle"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($item['status'] == 'Menunggu' || $item['status'] == 'Ditolak'): ?>
                            <button type="button" class="btn btn-sm btn-danger mb-1" title="Hapus" onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo $item['kode_peminjaman']; ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
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
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/peminjaman?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&status=<?php echo $status; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/peminjaman?page=<?php echo $i; ?>&search=<?php echo $search; ?>&status=<?php echo $status; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/peminjaman?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&status=<?php echo $status; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <img src="<?php echo ROOT_URL; ?>/assets/img/empty.svg" alt="Data tidak ditemukan" class="img-fluid mb-3" style="max-height: 200px;">
                <h5>Data Peminjaman Tidak Ditemukan</h5>
                <p class="text-muted">Belum ada data peminjaman atau tidak ada data yang sesuai dengan filter yang dipilih.</p>
                <a href="<?php echo ROOT_URL; ?>/peminjaman/add" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle me-2"></i> Tambah Peminjaman Baru
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
                <p>Apakah Anda yakin ingin menghapus peminjaman dengan kode <strong id="itemCode"></strong>?</p>
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
function confirmDelete(id, code) {
    document.getElementById('deleteId').value = id;
    document.getElementById('itemCode').textContent = code;
    
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Inisialisasi DataTables
document.addEventListener('DOMContentLoaded', function() {
    new DataTable('#dataTable', {
        responsive: true,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            zeroRecords: "Tidak ada data yang cocok",
            emptyTable: "Tidak ada data tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });
});
</script>