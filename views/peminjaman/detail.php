<?php
/**
 * Halaman untuk detail peminjaman
 */

// Load model
require_once('models/Peminjaman.php');
require_once('models/Pengembalian.php');
require_once('models/Masalah.php');

// Inisialisasi model
$peminjamanModel = new Peminjaman($conn);
$pengembalianModel = new Pengembalian($conn);
$masalahModel = new Masalah($conn);

// Dapatkan ID peminjaman dari URL
$id = isset($param) ? (int)$param : 0;

// Jika ID tidak valid, redirect ke halaman peminjaman
if ($id <= 0) {
    $_SESSION['message'] = "ID Peminjaman tidak valid!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/peminjaman');
}

// Get data peminjaman berdasarkan ID
$peminjaman = $peminjamanModel->getPeminjamanById($id);

// Jika data tidak ditemukan, redirect ke halaman peminjaman
if (!$peminjaman) {
    $_SESSION['message'] = "Data Peminjaman tidak ditemukan!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/peminjaman');
}

// Cek apakah peminjaman sudah dikembalikan
$pengembalian = $pengembalianModel->getPengembalianByPeminjamanId($id);

// Dapatkan data masalah jika ada
$masalah = $masalahModel->getMasalahByPeminjaman($id);

// Update status peminjaman ke Dipinjam jika status Disetujui
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_dipinjam'])) {
    if ($peminjaman['status'] == 'Disetujui') {
        if ($peminjamanModel->setDipinjam($id)) {
            $_SESSION['message'] = "Status peminjaman berhasil diubah menjadi Dipinjam!";
            $_SESSION['message_type'] = "success";
            
            // Refresh halaman
            redirect(ROOT_URL . '/peminjaman/detail/' . $id);
        } else {
            $_SESSION['message'] = "Gagal mengubah status peminjaman!";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Status peminjaman tidak dapat diubah menjadi Dipinjam!";
        $_SESSION['message_type'] = "danger";
    }
}

// Generate QR Code untuk peminjaman
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($peminjaman['kode_peminjaman'] . " - " . $peminjaman['nama_sarpras']);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Peminjaman</h1>
        <div>
            <?php if ($peminjaman['status'] == 'Menunggu'): ?>
            <a href="<?php echo ROOT_URL; ?>/peminjaman/approve/<?php echo $id; ?>" class="btn btn-success me-2">
                <i class="bi bi-check-circle me-2"></i> Proses Persetujuan
            </a>
            <?php endif; ?>
            
            <?php if ($peminjaman['status'] == 'Dipinjam' || $peminjaman['status'] == 'Terlambat'): ?>
            <a href="<?php echo ROOT_URL; ?>/pengembalian/add/<?php echo $id; ?>" class="btn btn-primary me-2">
                <i class="bi bi-arrow-down-left-circle me-2"></i> Proses Pengembalian
            </a>
            <?php endif; ?>
            
            <a href="<?php echo ROOT_URL; ?>/peminjaman" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Detail Peminjaman -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi Peminjaman</h5>
                    <div>
                        <button class="btn btn-sm btn-light" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Cetak
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <table class="table table-hover">
                                <tr>
                                    <th width="35%">Kode Peminjaman</th>
                                    <td width="65%"><strong><?php echo $peminjaman['kode_peminjaman']; ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge rounded-pill 
                                            <?php 
                                                if ($peminjaman['status'] == 'Menunggu') echo 'bg-warning';
                                                else if ($peminjaman['status'] == 'Disetujui') echo 'bg-info';
                                                else if ($peminjaman['status'] == 'Dipinjam') echo 'bg-primary';
                                                else if ($peminjaman['status'] == 'Dikembalikan') echo 'bg-success';
                                                else if ($peminjaman['status'] == 'Ditolak') echo 'bg-danger';
                                                else if ($peminjaman['status'] == 'Terlambat') echo 'bg-danger';
                                            ?>">
                                            <?php echo $peminjaman['status']; ?>
                                        </span>
                                        
                                        <?php if ($peminjaman['status'] == 'Disetujui'): ?>
                                        <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#setDipinjamModal">
                                            <i class="bi bi-arrow-up-right-circle me-1"></i> Set Dipinjam
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Nama Sarpras</th>
                                    <td><?php echo $peminjaman['nama_sarpras']; ?> (<?php echo $peminjaman['kode_sarpras']; ?>)</td>
                                </tr>
                                <tr>
                                    <th>Kategori</th>
                                    <td><?php echo $peminjaman['nama_kategori']; ?></td>
                                </tr>
                                <tr>
                                    <th>Jumlah</th>
                                    <td><?php echo $peminjaman['jumlah']; ?> unit</td>
                                </tr>
                                <tr>
                                    <th>Peminjam</th>
                                    <td><?php echo $peminjaman['nama_peminjam']; ?></td>
                                </tr>
                                <tr>
                                    <th>Kontak Peminjam</th>
                                    <td>
                                        <i class="bi bi-envelope me-1"></i> <?php echo $peminjaman['email_peminjam']; ?><br>
                                        <?php if (!empty($peminjaman['telp_peminjam'])): ?>
                                        <i class="bi bi-phone me-1"></i> <?php echo $peminjaman['telp_peminjam']; ?>
                                        <?php else: ?>
                                        <i class="bi bi-phone me-1"></i> <em>Tidak ada</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tanggal Pinjam</th>
                                    <td><?php echo formatDate($peminjaman['tanggal_pinjam']); ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Kembali</th>
                                    <td>
                                        <?php echo formatDate($peminjaman['tanggal_kembali']); ?>
                                        <?php if ($peminjaman['status'] == 'Terlambat'): ?>
                                        <span class="badge rounded-pill bg-danger ms-2">Terlambat</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($peminjaman['status'] == 'Dikembalikan' && $pengembalian): ?>
                                <tr>
                                    <th>Tanggal Kembali Aktual</th>
                                    <td><?php echo formatDate($pengembalian['tanggal_kembali_aktual']); ?></td>
                                </tr>
                                <tr>
                                    <th>Kondisi Saat Kembali</th>
                                    <td>
                                        <span class="badge rounded-pill 
                                            <?php 
                                                if ($pengembalian['kondisi'] == 'Baik') echo 'bg-success';
                                                else if ($pengembalian['kondisi'] == 'Rusak Ringan') echo 'bg-warning';
                                                else if ($pengembalian['kondisi'] == 'Rusak Berat') echo 'bg-danger';
                                            ?>">
                                            <?php echo $pengembalian['kondisi']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($peminjaman['status'] == 'Disetujui' || $peminjaman['status'] == 'Ditolak'): ?>
                                <tr>
                                    <th>Disetujui/Ditolak Oleh</th>
                                    <td><?php echo !empty($peminjaman['nama_approval']) ? $peminjaman['nama_approval'] : '-'; ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Persetujuan</th>
                                    <td><?php echo !empty($peminjaman['approved_at']) ? formatDateTime($peminjaman['approved_at']) : '-'; ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Tanggal Dibuat</th>
                                    <td><?php echo formatDateTime($peminjaman['created_at']); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-5 text-center">
                            <p class="mb-2">QR Code Peminjaman</p>
                            <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code <?php echo $peminjaman['kode_peminjaman']; ?>" class="img-fluid img-thumbnail mb-2" style="max-width: 150px;">
                            <p><small>Scan untuk verifikasi</small></p>
                            
                            <?php if (!empty($peminjaman['sarpras_foto'])): ?>
                            <p class="mb-2 mt-4">Foto Sarpras</p>
                            <img src="<?php echo ROOT_URL; ?>/uploads/<?php echo $peminjaman['sarpras_foto']; ?>" alt="<?php echo $peminjaman['nama_sarpras']; ?>" class="img-fluid img-thumbnail" style="max-height: 150px;">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6 class="mb-2">Tujuan Peminjaman:</h6>
                            <p><?php echo nl2br($peminjaman['tujuan_peminjaman']); ?></p>
                            
                            <?php if (!empty($peminjaman['catatan'])): ?>
                            <h6 class="mb-2">Catatan:</h6>
                            <p><?php echo nl2br($peminjaman['catatan']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Data Pengembalian -->
            <?php if ($pengembalian): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-arrow-down-left-circle me-2"></i> Data Pengembalian</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-hover">
                                <tr>
                                    <th width="40%">Tanggal Pengembalian</th>
                                    <td width="60%"><?php echo formatDate($pengembalian['tanggal_kembali_aktual']); ?></td>
                                </tr>
                                <tr>
                                    <th>Kondisi Saat Dikembalikan</th>
                                    <td>
                                        <span class="badge rounded-pill 
                                            <?php 
                                                if ($pengembalian['kondisi'] == 'Baik') echo 'bg-success';
                                                else if ($pengembalian['kondisi'] == 'Rusak Ringan') echo 'bg-warning';
                                                else if ($pengembalian['kondisi'] == 'Rusak Berat') echo 'bg-danger';
                                            ?>">
                                            <?php echo $pengembalian['kondisi']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status Verifikasi</th>
                                    <td>
                                        <?php if ($pengembalian['verified_by']): ?>
                                        <span class="badge rounded-pill bg-success">Terverifikasi</span>
                                        <?php else: ?>
                                        <span class="badge rounded-pill bg-warning">Belum Diverifikasi</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($pengembalian['verified_by']): ?>
                                <tr>
                                    <th>Diverifikasi Oleh</th>
                                    <td><?php echo $pengembalian['nama_verifikator']; ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Verifikasi</th>
                                    <td><?php echo formatDateTime($pengembalian['verified_at']); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <?php if (!empty($pengembalian['catatan'])): ?>
                            <h6 class="mb-2">Catatan Pengembalian:</h6>
                            <p><?php echo nl2br($pengembalian['catatan']); ?></p>
                            <?php endif; ?>
                            
                            <div class="text-end mt-3">
                                <a href="<?php echo ROOT_URL; ?>/pengembalian/detail/<?php echo $pengembalian['id']; ?>" class="btn btn-info text-white">
                                    <i class="bi bi-eye me-2"></i> Detail Pengembalian
                                </a>
                                
                                <?php if (!$pengembalian['verified_by']): ?>
                                <a href="<?php echo ROOT_URL; ?>/pengembalian/verify/<?php echo $pengembalian['id']; ?>" class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i> Verifikasi Pengembalian
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Data Masalah -->
            <?php if (!empty($masalah)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Laporan Masalah</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Tanggal Laporan</th>
                                    <th width="55%">Deskripsi Masalah</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($masalah as $index => $item): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo formatDateTime($item['created_at']); ?></td>
                                    <td><?php echo substr($item['deskripsi'], 0, 100) . (strlen($item['deskripsi']) > 100 ? '...' : ''); ?></td>
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
                                        <a href="<?php echo ROOT_URL; ?>/masalah/detail/<?php echo $item['id']; ?>" class="btn btn-sm btn-info text-white" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <!-- Status Peminjaman -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Status Peminjaman</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker <?php echo $peminjaman['status'] != 'Ditolak' ? 'active' : ''; ?>">
                                <i class="bi bi-journal-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Peminjaman Dibuat</h6>
                                <p class="timeline-text"><?php echo formatDateTime($peminjaman['created_at']); ?></p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker <?php echo in_array($peminjaman['status'], ['Disetujui', 'Dipinjam', 'Terlambat', 'Dikembalikan']) ? 'active' : ($peminjaman['status'] == 'Ditolak' ? 'rejected' : ''); ?>">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">
                                    <?php echo $peminjaman['status'] == 'Ditolak' ? 'Peminjaman Ditolak' : 'Peminjaman Disetujui'; ?>
                                </h6>
                                <p class="timeline-text">
                                    <?php if ($peminjaman['status'] == 'Menunggu'): ?>
                                    <span class="text-muted">Menunggu persetujuan</span>
                                    <?php else: ?>
                                    <?php echo formatDateTime($peminjaman['approved_at']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if ($peminjaman['status'] != 'Ditolak'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker <?php echo in_array($peminjaman['status'], ['Dipinjam', 'Terlambat', 'Dikembalikan']) ? 'active' : ''; ?>">
                                <i class="bi bi-arrow-up-right-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Sarpras Dipinjam</h6>
                                <p class="timeline-text">
                                    <?php if (in_array($peminjaman['status'], ['Dipinjam', 'Terlambat', 'Dikembalikan'])): ?>
                                    <?php echo formatDate($peminjaman['tanggal_pinjam']); ?>
                                    <?php elseif ($peminjaman['status'] == 'Disetujui'): ?>
                                    <span class="text-muted">Menunggu pengambilan</span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="timeline-item">
                            <div class="timeline-marker <?php echo $peminjaman['status'] == 'Dikembalikan' ? 'active' : ''; ?>">
                                <i class="bi bi-arrow-down-left-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Sarpras Dikembalikan</h6>
                                <p class="timeline-text">
                                    <?php if ($peminjaman['status'] == 'Dikembalikan'): ?>
                                    <?php echo formatDate($pengembalian['tanggal_kembali_aktual']); ?>
                                    <?php elseif (in_array($peminjaman['status'], ['Dipinjam', 'Terlambat'])): ?>
                                    <span class="text-muted">Tenggat: <?php echo formatDate($peminjaman['tanggal_kembali']); ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </p>
                            </div>
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
                        <?php if ($peminjaman['status'] == 'Menunggu'): ?>
                        <a href="<?php echo ROOT_URL; ?>/peminjaman/approve/<?php echo $id; ?>" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i> Proses Persetujuan
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($peminjaman['status'] == 'Disetujui'): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#setDipinjamModal">
                            <i class="bi bi-arrow-up-right-circle me-2"></i> Set Status Dipinjam
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($peminjaman['status'] == 'Dipinjam' || $peminjaman['status'] == 'Terlambat'): ?>
                        <a href="<?php echo ROOT_URL; ?>/pengembalian/add/<?php echo $id; ?>" class="btn btn-success">
                            <i class="bi bi-arrow-down-left-circle me-2"></i> Proses Pengembalian
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!$pengembalian && in_array($peminjaman['status'], ['Dipinjam', 'Terlambat'])): ?>
                        <a href="<?php echo ROOT_URL; ?>/masalah/add/<?php echo $id; ?>" class="btn btn-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i> Laporkan Masalah
                        </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo ROOT_URL; ?>/sarpras/detail/<?php echo $peminjaman['sarpras_id']; ?>" class="btn btn-info text-white">
                            <i class="bi bi-box-seam me-2"></i> Detail Sarpras
                        </a>
                        
                        <button type="button" class="btn btn-secondary" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i> Cetak Peminjaman
                        </button>
                        
                        <?php if ($peminjaman['status'] == 'Menunggu' || $peminjaman['status'] == 'Ditolak'): ?>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $id; ?>, '<?php echo $peminjaman['kode_peminjaman']; ?>')">
                            <i class="bi bi-trash me-2"></i> Hapus Peminjaman
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Set Dipinjam -->
<div class="modal fade" id="setDipinjamModal" tabindex="-1" aria-labelledby="setDipinjamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="setDipinjamModalLabel">Konfirmasi Status Dipinjam</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin mengubah status peminjaman menjadi <strong>Dipinjam</strong>?</p>
                <p>Tindakan ini mengonfirmasi bahwa sarpras telah diambil oleh peminjam.</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="set_dipinjam" class="btn btn-primary">Konfirmasi</button>
                </form>
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
                <p>Apakah Anda yakin ingin menghapus peminjaman dengan kode <strong id="itemCode"></strong>?</p>
                <p class="text-danger mb-0">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="<?php echo ROOT_URL; ?>/peminjaman">
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
</script>

<!-- Custom CSS for Timeline -->
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}

.timeline-marker.active {
    background-color: #28a745;
}

.timeline-marker.rejected {
    background-color: #dc3545;
}

.timeline-content {
    padding-bottom: 10px;
}

.timeline-title {
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 0;
    font-size: 14px;
}
</style>

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