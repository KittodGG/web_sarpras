<?php
/**
 * Halaman untuk manajemen pengembalian
 */

// Load model
require_once('models/Pengembalian.php');

// Inisialisasi model
$pengembalianModel = new Pengembalian($conn);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter dan pencarian
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Get data pengembalian
$pengembalian = $pengembalianModel->getAllPengembalian($offset, $limit, $search);
$totalPengembalian = $pengembalianModel->getTotalPengembalian($search);

// Hitung pagination
$totalPages = ceil($totalPengembalian / $limit);

// Get peminjaman yang belum dikembalikan
$peminjamanBelumKembali = $pengembalianModel->getPeminjamanBelumKembali();

// Statistik pengembalian
$statistik = $pengembalianModel->getStatistik();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Pengembalian</h1>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-plus-circle me-2"></i> Proses Pengembalian
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                <?php if (count($peminjamanBelumKembali) > 0): ?>
                    <?php foreach ($peminjamanBelumKembali as $pinjam): ?>
                    <li>
                        <a class="dropdown-item" href="<?php echo ROOT_URL; ?>/pengembalian/add/<?php echo $pinjam['id']; ?>">
                            <i class="bi bi-arrow-down-left-circle me-2"></i> 
                            <?php echo $pinjam['kode_peminjaman']; ?> - 
                            <?php echo $pinjam['nama_sarpras']; ?> 
                            (<?php echo $pinjam['nama_peminjam']; ?>)
                        </a>
                    </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>
                        <span class="dropdown-item disabled">
                            <i class="bi bi-exclamation-circle me-2"></i> Tidak ada peminjaman aktif
                        </span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <!-- Statistik Pengembalian -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body p-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <i class="bi bi-arrow-down-left-circle"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['total']; ?></div>
                            <div class="stat-text">Total Pengembalian</div>
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
                            <div class="stat-number"><?php echo $statistik['sudah_verifikasi']; ?></div>
                            <div class="stat-text">Terverifikasi</div>
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
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-number"><?php echo $statistik['belum_verifikasi']; ?></div>
                            <div class="stat-text">Belum Verifikasi</div>
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
                            <div class="stat-number"><?php echo $statistik['rusak_ringan'] + $statistik['rusak_berat']; ?></div>
                            <div class="stat-text">Kembali Rusak</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter dan pencarian -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo ROOT_URL; ?>/pengembalian" class="row g-3">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" name="search" placeholder="Cari kode peminjaman, nama peminjam, atau nama sarpras..." value="<?php echo $search; ?>">
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
    
    <!-- Tabel Pengembalian -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i> Daftar Pengembalian
            </h5>
            <div>
                <button class="btn btn-sm btn-light" onclick="printData('dataPengembalian')">
                    <i class="bi bi-printer"></i>
                </button>
                <button class="btn btn-sm btn-light" onclick="exportTableToExcel('dataTable', 'data_pengembalian')">
                    <i class="bi bi-file-excel"></i>
                </button>
            </div>
        </div>
        <div class="card-body table-responsive" id="dataPengembalian">
            <?php if (count($pengembalian) > 0): ?>
            <table class="table table-hover table-bordered datatable" id="dataTable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Kode Peminjaman</th>
                        <th width="20%">Sarpras</th>
                        <th width="15%">Peminjam</th>
                        <th width="12%">Tgl Kembali</th>
                        <th width="15%">Kondisi</th>
                        <th width="8%">Status</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pengembalian as $index => $item): ?>
                    <tr>
                        <td class="text-center"><?php echo $offset + $index + 1; ?></td>
                        <td><?php echo $item['kode_peminjaman']; ?></td>
                        <td><?php echo $item['nama_sarpras']; ?></td>
                        <td><?php echo $item['nama_peminjam']; ?></td>
                        <td><?php echo formatDate($item['tanggal_kembali_aktual']); ?></td>
                        <td>
                            <span class="badge rounded-pill 
                                <?php 
                                    if ($item['kondisi'] == 'Baik') echo 'bg-success';
                                    else if ($item['kondisi'] == 'Rusak Ringan') echo 'bg-warning';
                                    else if ($item['kondisi'] == 'Rusak Berat') echo 'bg-danger';
                                ?>">
                                <?php echo $item['kondisi']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($item['verified_by']): ?>
                            <span class="badge rounded-pill bg-success">Terverifikasi</span>
                            <?php else: ?>
                            <span class="badge rounded-pill bg-warning">Belum Diverifikasi</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo ROOT_URL; ?>/pengembalian/detail/<?php echo $item['id']; ?>" class="btn btn-sm btn-info text-white mb-1" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            <?php if (!$item['verified_by']): ?>
                            <a href="<?php echo ROOT_URL; ?>/pengembalian/verify/<?php echo $item['id']; ?>" class="btn btn-sm btn-success mb-1" title="Verifikasi">
                                <i class="bi bi-check-circle"></i>
                            </a>
                            <?php endif; ?>
                            
                            <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $item['peminjaman_id']; ?>" class="btn btn-sm btn-primary mb-1" title="Detail Peminjaman">
                                <i class="bi bi-arrow-up-right-circle"></i>
                            </a>
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
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/pengembalian?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/pengembalian?page=<?php echo $i; ?>&search=<?php echo $search; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo ROOT_URL; ?>/pengembalian?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <img src="<?php echo ROOT_URL; ?>/assets/img/empty.svg" alt="Data tidak ditemukan" class="img-fluid mb-3" style="max-height: 200px;">
                <h5>Data Pengembalian Tidak Ditemukan</h5>
                <p class="text-muted">Belum ada data pengembalian atau tidak ada data yang sesuai dengan filter yang dipilih.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Daftar Peminjaman Belum Kembali -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i> Daftar Peminjaman Belum Dikembalikan
            </h5>
        </div>
        <div class="card-body table-responsive">
            <?php if (count($peminjamanBelumKembali) > 0): ?>
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Kode</th>
                        <th width="20%">Sarpras</th>
                        <th width="15%">Peminjam</th>
                        <th width="10%">Tgl Pinjam</th>
                        <th width="10%">Tgl Kembali</th>
                        <th width="10%">Status</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peminjamanBelumKembali as $index => $item): ?>
                    <tr class="<?php echo ($item['status'] == 'Terlambat') ? 'table-danger' : ''; ?>">
                        <td class="text-center"><?php echo $index + 1; ?></td>
                        <td><?php echo $item['kode_peminjaman']; ?></td>
                        <td><?php echo $item['nama_sarpras']; ?></td>
                        <td><?php echo $item['nama_peminjam']; ?></td>
                        <td><?php echo formatDate($item['tanggal_pinjam']); ?></td>
                        <td>
                            <?php echo formatDate($item['tanggal_kembali']); ?>
                            <?php if ($item['status'] == 'Terlambat'): ?>
                            <span class="badge rounded-pill bg-danger">Terlambat</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge rounded-pill 
                                <?php 
                                    if ($item['status'] == 'Dipinjam') echo 'bg-primary';
                                    else if ($item['status'] == 'Terlambat') echo 'bg-danger';
                                ?>">
                                <?php echo $item['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $item['id']; ?>" class="btn btn-sm btn-info text-white mb-1" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            <a href="<?php echo ROOT_URL; ?>/pengembalian/add/<?php echo $item['id']; ?>" class="btn btn-sm btn-success mb-1" title="Proses Pengembalian">
                                <i class="bi bi-arrow-down-left-circle"></i> Proses Pengembalian
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php else: ?>
            <div class="text-center py-4">
                <div class="mb-2"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i></div>
                <h5>Semua Peminjaman Sudah Dikembalikan</h5>
                <p class="text-muted">Tidak ada peminjaman yang belum dikembalikan.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
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