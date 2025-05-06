<?php
/**
 * Halaman untuk detail sarpras
 */

// Load model
require_once('models/Sarpras.php');
require_once('models/Kategori.php');
require_once('models/Peminjaman.php');

// Inisialisasi model
$sarprasModel = new Sarpras($conn);
$kategoriModel = new Kategori($conn);
$peminjamanModel = new Peminjaman($conn);

// Dapatkan ID sarpras dari URL
$id = isset($param) ? (int)$param : 0;

// Jika ID tidak valid, redirect ke halaman sarpras
if ($id <= 0) {
    $_SESSION['message'] = "ID Sarpras tidak valid!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/sarpras');
}

// Get data sarpras berdasarkan ID
$sarpras = $sarprasModel->getSarprasById($id);

// Jika data tidak ditemukan, redirect ke halaman sarpras
if (!$sarpras) {
    $_SESSION['message'] = "Data Sarpras tidak ditemukan!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/sarpras');
}

// Dapatkan data peminjaman sarpras ini
$query = "SELECT p.*, u.nama_lengkap as nama_peminjam, 
          r.id as id_pengembalian, r.tanggal_kembali_aktual, r.kondisi as kondisi_kembali
          FROM peminjaman p
          JOIN users u ON p.user_id = u.id
          LEFT JOIN pengembalian r ON p.id = r.peminjaman_id
          WHERE p.sarpras_id = {$id}
          ORDER BY p.created_at DESC LIMIT 10";
$result = mysqli_query($conn, $query);
$riwayatPeminjaman = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $riwayatPeminjaman[] = $row;
    }
}

// Generate QR Code untuk sarpras
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($sarpras['kode'] . " - " . $sarpras['nama']);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Sarpras</h1>
        <div>
            <a href="<?php echo ROOT_URL; ?>/sarpras/edit/<?php echo $id; ?>" class="btn btn-warning text-white me-2">
                <i class="bi bi-pencil me-2"></i> Edit
            </a>
            <a href="<?php echo ROOT_URL; ?>/sarpras" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Detail Sarpras -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi Sarpras</h5>
                    <div>
                        <button class="btn btn-sm btn-light" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Cetak
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <table class="table table-hover">
                                <tr>
                                    <th width="30%">Kode Sarpras</th>
                                    <td width="70%"><strong><?php echo $sarpras['kode']; ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Nama Sarpras</th>
                                    <td><?php echo $sarpras['nama']; ?></td>
                                </tr>
                                <tr>
                                    <th>Kategori</th>
                                    <td><?php echo $sarpras['nama_kategori']; ?></td>
                                </tr>
                                <tr>
                                    <th>Stok</th>
                                    <td><?php echo $sarpras['stok']; ?> unit</td>
                                </tr>
                                <tr>
                                    <th>Tersedia</th>
                                    <td>
                                        <?php if ($sarpras['tersedia'] > 0): ?>
                                        <span class="badge bg-success"><?php echo $sarpras['tersedia']; ?> unit</span>
                                        <?php else: ?>
                                        <span class="badge bg-danger">Habis</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Kondisi</th>
                                    <td>
                                        <span class="badge rounded-pill 
                                            <?php 
                                                if ($sarpras['kondisi'] == 'Baik') echo 'bg-success';
                                                else if ($sarpras['kondisi'] == 'Rusak Ringan') echo 'bg-warning';
                                                else if ($sarpras['kondisi'] == 'Rusak Berat') echo 'bg-danger';
                                            ?>">
                                            <?php echo $sarpras['kondisi']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Lokasi Penyimpanan</th>
                                    <td><?php echo !empty($sarpras['lokasi']) ? $sarpras['lokasi'] : '<em>Belum diatur</em>'; ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Pengadaan</th>
                                    <td><?php echo formatDate($sarpras['tanggal_pengadaan']); ?></td>
                                </tr>
                                <tr>
                                    <th>Terakhir Diperbarui</th>
                                    <td><?php echo formatDateTime($sarpras['updated_at']); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4 text-center">
                            <p class="mb-2">QR Code</p>
                            <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code <?php echo $sarpras['kode']; ?>" class="img-fluid img-thumbnail mb-2" style="max-width: 150px;">
                            <p><small>Scan untuk melihat detail</small></p>
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <h6 class="mt-3">Deskripsi:</h6>
                        <p><?php echo !empty($sarpras['deskripsi']) ? nl2br($sarpras['deskripsi']) : '<em>Tidak ada deskripsi</em>'; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Riwayat Peminjaman -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Riwayat Peminjaman</h5>
                </div>
                <div class="card-body table-responsive">
                    <?php if (count($riwayatPeminjaman) > 0): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="10%">No</th>
                                <th width="15%">Kode</th>
                                <th width="20%">Peminjam</th>
                                <th width="15%">Tanggal Pinjam</th>
                                <th width="15%">Tanggal Kembali</th>
                                <th width="10%">Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayatPeminjaman as $index => $row): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $row['kode_peminjaman']; ?></td>
                                <td><?php echo $row['nama_peminjam']; ?></td>
                                <td><?php echo formatDate($row['tanggal_pinjam']); ?></td>
                                <td>
                                    <?php if ($row['status'] == 'Dikembalikan' && !empty($row['tanggal_kembali_aktual'])): ?>
                                    <?php echo formatDate($row['tanggal_kembali_aktual']); ?>
                                    <?php else: ?>
                                    <?php echo formatDate($row['tanggal_kembali']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge rounded-pill 
                                        <?php 
                                            if ($row['status'] == 'Menunggu') echo 'bg-warning';
                                            else if ($row['status'] == 'Disetujui') echo 'bg-info';
                                            else if ($row['status'] == 'Dipinjam') echo 'bg-primary';
                                            else if ($row['status'] == 'Dikembalikan') echo 'bg-success';
                                            else if ($row['status'] == 'Ditolak') echo 'bg-danger';
                                            else if ($row['status'] == 'Terlambat') echo 'bg-danger';
                                        ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $row['id']; ?>" class="btn btn-sm btn-info text-white mb-1" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($row['status'] == 'Dikembalikan' && !empty($row['id_pengembalian'])): ?>
                                    <a href="<?php echo ROOT_URL; ?>/pengembalian/detail/<?php echo $row['id_pengembalian']; ?>" class="btn btn-sm btn-success mb-1" title="Detail Pengembalian">
                                        <i class="bi bi-arrow-return-left"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="text-center py-3">
                        <div class="mb-2"><i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i></div>
                        <p class="mb-0 text-muted">Belum ada riwayat peminjaman untuk sarpras ini.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (count($riwayatPeminjaman) > 0): ?>
                <div class="card-footer bg-light">
                    <a href="<?php echo ROOT_URL; ?>/laporan/peminjaman?sarpras_id=<?php echo $id; ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-list-ul me-1"></i> Lihat Semua Riwayat
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Foto Sarpras -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-image me-2"></i> Foto Sarpras</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($sarpras['foto'])): ?>
                    <img src="<?php echo ROOT_URL; ?>/uploads/<?php echo $sarpras['foto']; ?>" alt="<?php echo $sarpras['nama']; ?>" class="img-fluid img-thumbnail" style="max-height: 300px;">
                    <?php else: ?>
                    <img src="<?php echo ROOT_URL; ?>/assets/img/no-image.png" alt="No Image" class="img-fluid img-thumbnail" style="max-height: 300px;">
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Status Ketersediaan -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Status Ketersediaan</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Ketersediaan</span>
                            <span><?php echo $sarpras['tersedia']; ?> dari <?php echo $sarpras['stok']; ?> unit</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar 
                                <?php 
                                    $percentage = ($sarpras['tersedia'] / $sarpras['stok']) * 100;
                                    if ($percentage >= 70) echo 'bg-success';
                                    else if ($percentage >= 30) echo 'bg-warning';
                                    else echo 'bg-danger';
                                ?>" 
                                role="progressbar" 
                                style="width: <?php echo $percentage; ?>%" 
                                aria-valuenow="<?php echo $percentage; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                <?php echo round($percentage); ?>%
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center pt-2">
                        <?php if ($sarpras['tersedia'] > 0): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle me-2"></i> Tersedia untuk dipinjam
                        </div>
                        <?php else: ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-x-circle me-2"></i> Tidak tersedia untuk dipinjam
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i> Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo ROOT_URL; ?>/sarpras/edit/<?php echo $id; ?>" class="btn btn-warning text-white">
                            <i class="bi bi-pencil me-2"></i> Edit Sarpras
                        </a>
                        <a href="<?php echo ROOT_URL; ?>/peminjaman/add?sarpras_id=<?php echo $id; ?>" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i> Buat Peminjaman
                        </a>
                        <a href="<?php echo ROOT_URL; ?>/jadwal?sarpras_id=<?php echo $id; ?>" class="btn btn-info text-white">
                            <i class="bi bi-calendar-event me-2"></i> Lihat Jadwal Peminjaman
                        </a>
                        <button type="button" class="btn btn-secondary" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i> Cetak Informasi
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $id; ?>, '<?php echo $sarpras['nama']; ?>')">
                            <i class="bi bi-trash me-2"></i> Hapus Sarpras
                        </button>
                    </div>
                </div>
            </div>
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
                <form method="POST" action="<?php echo ROOT_URL; ?>/sarpras">
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

<!-- Print Style -->
<style media="print">
    .btn, .sidebar, .navbar, footer, .card-header button {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
        border-bottom: 1px solid #ddd !important;
    }
    
    @page {
        margin: 0.5cm;
    }
    
    body {
        font-size: 12pt;
    }
</style>