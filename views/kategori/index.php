<?php
/**
 * Halaman untuk manajemen kategori
 */

// Load model
require_once('models/Kategori.php');

// Inisialisasi model
$kategoriModel = new Kategori($conn);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter dan pencarian
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Get data kategori
$kategori = $kategoriModel->getAllKategori($offset, $limit, $search);
$totalKategori = $kategoriModel->getTotalKategori($search);

// Hitung pagination
$totalPages = ceil($totalKategori / $limit);

// Proses hapus data jika ada
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    
    if ($kategoriModel->deleteKategori($id)) {
        $_SESSION['message'] = "Data kategori berhasil dihapus!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus data kategori! Kategori masih memiliki sarpras atau terjadi kesalahan.";
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirect untuk refresh halaman
    redirect(ROOT_URL . '/kategori');
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Kategori</h1>
        <a href="<?php echo ROOT_URL; ?>/kategori/add" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i> Tambah Kategori
        </a>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <!-- Filter dan pencarian -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo ROOT_URL; ?>/kategori" class="row g-3">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" placeholder="Cari nama atau deskripsi kategori..." value="<?php echo $search; ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter me-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabel Kategori -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-tags me-2"></i> Daftar Kategori
            </h5>
            <div>
                <button class="btn btn-sm btn-light" onclick="printData('dataKategori')">
                    <i class="bi bi-printer"></i>
                </button>
                <button class="btn btn-sm btn-light" onclick="exportTableToExcel('dataTable', 'data_kategori')">
                    <i class="bi bi-file-excel"></i>
                </button>
            </div>
        </div>
        <div class="card-body table-responsive" id="dataKategori">
            <?php if (count($kategori) > 0): ?>
            <table class="table table-hover table-bordered datatable" id="dataTable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="25%">Nama Kategori</th>
                        <th width="45%">Deskripsi</th>
                        <th width="10%">Jumlah Item</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kategori as $index => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo $offset + $index + 1; ?></td>
                        <td><?php echo $item['nama']; ?></td>
                        <td><?php echo !empty($item['deskripsi']) ? $item['deskripsi'] : '<em>Tidak ada deskripsi</em>'; ?></td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill"><?php echo $item['jumlah_item']; ?></span>
                        </td>
                        <td>
                            <a href="<?php echo ROOT_URL; ?>/kategori/edit/<?php echo $item['id']; ?>" class="btn btn-sm btn-warning text-white mb-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger mb-1" title="Hapus" onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo $item['nama']; ?>')" <?php echo $item['jumlah_item'] > 0 ? 'disabled' : ''; ?>>
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
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/kategori?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/kategori?page=<?php echo $i; ?>&search=<?php echo $search; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/kategori?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <img src="<?php echo ROOT_URL; ?>/assets/img/empty.svg" alt="Data tidak ditemukan" class="img-fluid mb-3" style="max-height: 200px;">
                <h5>Data Kategori Tidak Ditemukan</h5>
                <p class="text-muted">Belum ada data kategori atau tidak ada data yang sesuai dengan filter yang dipilih.</p>
                <a href="<?php echo ROOT_URL; ?>/kategori/add" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle me-2"></i> Tambah Kategori Baru
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
                <p>Apakah Anda yakin ingin menghapus kategori <strong id="itemName"></strong>?</p>
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