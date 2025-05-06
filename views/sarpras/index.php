<?php
/**
 * Halaman untuk manajemen sarpras
 */

// Load model
require_once('models/Sarpras.php');
require_once('models/Kategori.php');

// Inisialisasi model
$sarprasModel = new Sarpras($conn);
$kategoriModel = new Kategori($conn);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter dan pencarian
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? (int)$_GET['search'] : '';
$kondisi = isset($_GET['kondisi']) ? sanitize($_GET['kondisi']) : '';

// Get data sarpras
$sarpras = $sarprasModel->getAllSarpras($offset, $limit, $search);
$totalSarpras = $sarprasModel->getTotalSarpras($search);

// Hitung pagination
$totalPages = ceil($totalSarpras / $limit);

// Get data kategori untuk filter
$kategoriDropdown = $kategoriModel->getKategoriDropdown();

// Proses hapus data jika ada
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    
    if ($sarprasModel->deleteSarpras($id)) {
        $_SESSION['message'] = "Data sarpras berhasil dihapus!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Gagal menghapus data sarpras! Sarpras masih dalam peminjaman atau terjadi kesalahan.";
        $_SESSION['message_type'] = "danger";
    }
    
    // Redirect untuk refresh halaman
    redirect(ROOT_URL . '/sarpras');
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Sarpras</h1>
        <a href="<?php echo ROOT_URL; ?>/sarpras/add" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i> Tambah Sarpras
        </a>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <!-- Filter dan pencarian -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo ROOT_URL; ?>/sarpras" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" placeholder="Cari nama, kode, atau kategori..." value="<?php echo $search; ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <select name="kategori" class="form-select">
                        <option value="">-- Semua Kategori --</option>
                        <?php foreach ($kategoriDropdown as $kat): ?>
                        <option value="<?php echo $kat['id']; ?>" <?php echo ($kategori == $kat['id']) ? 'selected' : ''; ?>>
                            <?php echo $kat['nama']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select name="kondisi" class="form-select">
                        <option value="">-- Semua Kondisi --</option>
                        <option value="Baik" <?php echo ($kondisi == 'Baik') ? 'selected' : ''; ?>>Baik</option>
                        <option value="Rusak Ringan" <?php echo ($kondisi == 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                        <option value="Rusak Berat" <?php echo ($kondisi == 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
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
    
    <!-- Tabel Sarpras -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-box-seam me-2"></i> Daftar Sarana dan Prasarana
            </h5>
            <div>
                <button class="btn btn-sm btn-light" onclick="printData('dataSarpras')">
                    <i class="bi bi-printer"></i>
                </button>
                <button class="btn btn-sm btn-light" onclick="exportTableToExcel('dataTable', 'data_sarpras')">
                    <i class="bi bi-file-excel"></i>
                </button>
            </div>
        </div>
        <div class="card-body table-responsive" id="dataSarpras">
            <?php if (count($sarpras) > 0): ?>
            <table class="table table-hover table-bordered datatable" id="dataTable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Kode</th>
                        <th width="15%">Foto</th>
                        <th width="20%">Nama Sarpras</th>
                        <th width="15%">Kategori</th>
                        <th width="10%">Stok</th>
                        <th width="10%">Tersedia</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sarpras as $index => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo $offset + $index + 1; ?></td>
                        <td><?php echo $item['kode']; ?></td>
                        <td class="text-center">
                            <?php if (!empty($item['foto'])): ?>
                            <img src="<?php echo ROOT_URL; ?>/uploads/<?php echo $item['foto']; ?>" alt="<?php echo $item['nama']; ?>" class="img-thumbnail" style="max-height: 80px;">
                            <?php else: ?>
                            <img src="<?php echo ROOT_URL; ?>/assets/img/no-image.png" alt="No Image" class="img-thumbnail" style="max-height: 80px;">
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo $item['nama']; ?></strong>
                            <br>
                            <span class="badge rounded-pill 
                                <?php 
                                    if ($item['kondisi'] == 'Baik') echo 'bg-success';
                                    else if ($item['kondisi'] == 'Rusak Ringan') echo 'bg-warning';
                                    else if ($item['kondisi'] == 'Rusak Berat') echo 'bg-danger';
                                ?>">
                                <?php echo $item['kondisi']; ?>
                            </span>
                        </td>
                        <td><?php echo $item['nama_kategori']; ?></td>
                        <td class="text-center"><?php echo $item['stok']; ?></td>
                        <td class="text-center">
                            <?php if ($item['tersedia'] > 0): ?>
                            <span class="badge bg-success"><?php echo $item['tersedia']; ?></span>
                            <?php else: ?>
                            <span class="badge bg-danger">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo ROOT_URL; ?>/sarpras/detail/<?php echo $item['id']; ?>" class="btn btn-sm btn-info text-white mb-1" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?php echo ROOT_URL; ?>/sarpras/edit/<?php echo $item['id']; ?>" class="btn btn-sm btn-warning text-white mb-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger mb-1" title="Hapus" onclick="confirmDelete(<?php echo $item['id']; ?>, '<?php echo $item['nama']; ?>')">
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
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/sarpras?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&kategori=<?php echo $kategori; ?>&kondisi=<?php echo $kondisi; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/sarpras?page=<?php echo $i; ?>&search=<?php echo $search; ?>&kategori=<?php echo $kategori; ?>&kondisi=<?php echo $kondisi; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/sarpras?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&kategori=<?php echo $kategori; ?>&kondisi=<?php echo $kondisi; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <img src="<?php echo ROOT_URL; ?>/assets/img/empty.svg" alt="Data tidak ditemukan" class="img-fluid mb-3" style="max-height: 200px;">
                <h5>Data Sarpras Tidak Ditemukan</h5>
                <p class="text-muted">Belum ada data sarpras atau tidak ada data yang sesuai dengan filter yang dipilih.</p>
                <a href="<?php echo ROOT_URL; ?>/sarpras/add" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle me-2"></i> Tambah Sarpras Baru
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
                <p>Apakah Anda yakin ingin menghapus sarpras <strong id="itemName"></strong>?</p>
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