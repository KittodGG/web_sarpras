<?php
/**
 * Halaman untuk manajemen masalah
 */

// Load model
require_once('models/Masalah.php');

// Inisialisasi model
$masalahModel = new Masalah($conn);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter dan pencarian
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Get data masalah
$masalah = $masalahModel->getAllMasalah($offset, $limit, $search, $status);
$totalMasalah = $masalahModel->getTotalMasalah($search, $status);

// Hitung pagination
$totalPages = ceil($totalMasalah / $limit);

// Statistik masalah
$statistik = $masalahModel->getStatistik();

// Proses update status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $new_status = sanitize($_POST['new_status']);
    
    if ($masalahModel->updateStatus($id, $new_status)) {
        $_SESSION['message'] = "Status masalah berhasil diperbarui!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal memperbarui status masalah!";
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirect untuk refresh halaman
    redirect(ROOT_URL . '/masalah');
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Masalah</h1>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <!-- Statistik Masalah -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['total']; ?></div>
                            <div class="stat-text">Total Masalah</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['dilaporkan']; ?></div>
                            <div class="stat-text">Dilaporkan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-info">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['diproses']; ?></div>
                            <div class="stat-text">Diproses</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['selesai']; ?></div>
                            <div class="stat-text">Selesai</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter dan pencarian -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo ROOT_URL; ?>/masalah" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" placeholder="Cari kode peminjaman, nama peminjam, atau nama sarpras..." value="<?php echo $search; ?>">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="Dilaporkan" <?php echo ($status == 'Dilaporkan') ? 'selected' : ''; ?>>Dilaporkan</option>
                        <option value="Diproses" <?php echo ($status == 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
                        <option value="Selesai" <?php echo ($status == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
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
    
    <!-- Tabel Masalah -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i> Daftar Masalah
            </h5>
            <div>
                <button class="btn btn-sm btn-light" onclick="printData('dataMasalah')">
                    <i class="bi bi-printer"></i>
                </button>
                <button class="btn btn-sm btn-light" onclick="exportTableToExcel('dataTable', 'data_masalah')">
                    <i class="bi bi-file-excel"></i>
                </button>
            </div>
        </div>
        <div class="card-body table-responsive" id="dataMasalah">
            <?php if (count($masalah) > 0): ?>
            <table class="table table-hover table-bordered datatable" id="dataTable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Kode Peminjaman</th>
                        <th width="20%">Sarpras</th>
                        <th width="15%">Pelapor</th>
                        <th width="30%">Deskripsi Masalah</th>
                        <th width="10%">Status</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($masalah as $index => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo $offset + $index + 1; ?></td>
                        <td><?php echo $item['kode_peminjaman']; ?></td>
                        <td><?php echo $item['nama_sarpras']; ?></td>
                        <td><?php echo $item['nama_pelapor']; ?></td>
                        <td>
                            <?php 
                            $shortDesc = substr($item['deskripsi'], 0, 100);
                            echo $shortDesc . (strlen($item['deskripsi']) > 100 ? '...' : '');
                            ?>
                        </td>
                        <td>
                            <span class="badge rounded-pill 
                                <?php 
                                    if ($item['status'] == 'Dilaporkan') echo 'bg-warning';
                                    else if ($item['status'] == 'Diproses') echo 'bg-info';
                                    else if ($item['status'] == 'Selesai') echo 'bg-success';
                                ?>">
                                <?php echo $item['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo ROOT_URL; ?>/masalah/detail/<?php echo $item['id']; ?>" class="btn btn-sm btn-info text-white mb-1" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            <?php if ($item['status'] != 'Selesai'): ?>
                            <button type="button" class="btn btn-sm btn-success mb-1" title="Update Status" onclick="showUpdateStatusModal(<?php echo $item['id']; ?>, '<?php echo $item['status']; ?>')">
                                <i class="bi bi-arrow-clockwise"></i>
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
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/masalah?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&status=<?php echo $status; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/masalah?page=<?php echo $i; ?>&search=<?php echo $search; ?>&status=<?php echo $status; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/masalah?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&status=<?php echo $status; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <img src="<?php echo ROOT_URL; ?>/assets/img/empty.svg" alt="Data tidak ditemukan" class="img-fluid mb-3" style="max-height: 200px;">
                <h5>Data Masalah Tidak Ditemukan</h5>
                <p class="text-muted">Belum ada data masalah atau tidak ada data yang sesuai dengan filter yang dipilih.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Status Masalah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="updateStatusForm">
                    <input type="hidden" name="id" id="masalahId">
                    <input type="hidden" name="update_status" value="1">
                    
                    <div class="mb-3">
                        <label for="currentStatus" class="form-label">Status Saat Ini</label>
                        <input type="text" class="form-control" id="currentStatus" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_status" class="form-label">Status Baru</label>
                        <select class="form-select" id="new_status" name="new_status" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="Dilaporkan">Dilaporkan</option>
                            <option value="Diproses">Diproses</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Function untuk menampilkan modal update status
function showUpdateStatusModal(id, currentStatus) {
    document.getElementById('masalahId').value = id;
    document.getElementById('currentStatus').value = currentStatus;
    
    // Set selected option pada status baru berdasarkan status saat ini
    const selectStatus = document.getElementById('new_status');
    for (let i = 0; i < selectStatus.options.length; i++) {
        if (selectStatus.options[i].value === currentStatus) {
            selectStatus.options[i].disabled = true;
        } else {
            selectStatus.options[i].disabled = false;
        }
    }
    
    // Set default next status based on current status
    if (currentStatus === 'Dilaporkan') {
        selectStatus.value = 'Diproses';
    } else if (currentStatus === 'Diproses') {
        selectStatus.value = 'Selesai';
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
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