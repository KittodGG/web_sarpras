<?php
/**
 * Halaman untuk detail masalah
 */

// Load model
require_once('models/Masalah.php');

// Inisialisasi model
$masalahModel = new Masalah($conn);

// Dapatkan ID masalah dari URL
$id = isset($param) ? (int)$param : 0;

// Jika ID tidak valid, redirect ke halaman masalah
if ($id <= 0) {
    $_SESSION['message'] = "ID Masalah tidak valid!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/masalah');
}

// Get data masalah berdasarkan ID
$masalah = $masalahModel->getMasalahById($id);

// Jika data tidak ditemukan, redirect ke halaman masalah
if (!$masalah) {
    $_SESSION['message'] = "Data Masalah tidak ditemukan!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/masalah');
}

// Proses update status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = sanitize($_POST['new_status']);
    
    if ($masalahModel->updateStatus($id, $new_status)) {
        $_SESSION['message'] = "Status masalah berhasil diperbarui menjadi {$new_status}!";
        $_SESSION['message_type'] = "success";
        
        // Refresh halaman
        redirect(ROOT_URL . '/masalah/detail/' . $id);
    } else {
        $_SESSION['message'] = "Gagal memperbarui status masalah!";
        $_SESSION['message_type'] = "danger";
    }
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Masalah</h1>
        <div>
            <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $masalah['peminjaman_id']; ?>" class="btn btn-info text-white me-2">
                <i class="bi bi-arrow-up-right-circle me-2"></i> Detail Peminjaman
            </a>
            <a href="<?php echo ROOT_URL; ?>/masalah" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Detail Masalah -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Informasi Masalah</h5>
                    <div>
                        <button class="btn btn-sm btn-light" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Cetak
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="border-start border-warning ps-2 mb-3">Status Masalah</h6>
                            <div class="mb-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge rounded-pill 
                                        <?php 
                                            if ($masalah['status'] == 'Dilaporkan') echo 'bg-warning';
                                            else if ($masalah['status'] == 'Diproses') echo 'bg-info';
                                            else if ($masalah['status'] == 'Selesai') echo 'bg-success';
                                        ?>"
                                        style="font-size: 1rem; padding: 0.5rem 1rem;">
                                        <?php echo $masalah['status']; ?>
                                    </span>
                                    <span class="ms-3 text-muted">Dilaporkan pada: <?php echo formatDateTime($masalah['created_at']); ?></span>
                                </div>
                                
                                <?php if ($masalah['status'] != 'Selesai'): ?>
                                <button type="button" class="btn btn-sm btn-success" onclick="showUpdateStatusModal()">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Update Status
                                </button>
                                <?php endif; ?>
                            </div>
                            
                            <h6 class="border-start border-warning ps-2 mb-3">Detail Peminjaman</h6>
                            <table class="table table-hover">
                                <tr>
                                    <th width="30%">Kode Peminjaman</th>
                                    <td width="70%"><?php echo $masalah['kode_peminjaman']; ?></td>
                                </tr>
                                <tr>
                                    <th>Nama Sarpras</th>
                                    <td><?php echo $masalah['nama_sarpras']; ?> (<?php echo $masalah['kode_sarpras']; ?>)</td>
                                </tr>
                                <tr>
                                    <th>Kategori</th>
                                    <td><?php echo $masalah['nama_kategori']; ?></td>
                                </tr>
                                <tr>
                                    <th>Peminjam</th>
                                    <td><?php echo $masalah['nama_pelapor']; ?></td>
                                </tr>
                                <tr>
                                    <th>Kontak Peminjam</th>
                                    <td>
                                        <i class="bi bi-envelope me-1"></i> <?php echo $masalah['email_pelapor']; ?><br>
                                        <?php if (!empty($masalah['telp_pelapor'])): ?>
                                        <i class="bi bi-phone me-1"></i> <?php echo $masalah['telp_pelapor']; ?>
                                        <?php else: ?>
                                        <i class="bi bi-phone me-1"></i> <em>Tidak ada</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status Peminjaman</th>
                                    <td>
                                        <span class="badge rounded-pill 
                                            <?php 
                                                if ($masalah['status_peminjaman'] == 'Dipinjam') echo 'bg-primary';
                                                else if ($masalah['status_peminjaman'] == 'Terlambat') echo 'bg-danger';
                                                else if ($masalah['status_peminjaman'] == 'Dikembalikan') echo 'bg-success';
                                            ?>">
                                            <?php echo $masalah['status_peminjaman']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tanggal Pinjam</th>
                                    <td><?php echo formatDate($masalah['tanggal_pinjam']); ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Kembali</th>
                                    <td><?php echo formatDate($masalah['tanggal_kembali']); ?></td>
                                </tr>
                            </table>
                            
                            <h6 class="border-start border-warning ps-2 mb-3 mt-4">Deskripsi Masalah</h6>
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <p class="card-text"><?php echo nl2br($masalah['deskripsi']); ?></p>
                                </div>
                            </div>
                            
                            <?php if ($masalah['status'] == 'Diproses' || $masalah['status'] == 'Selesai'): ?>
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="bi bi-tools me-2"></i> Informasi Penanganan</h6>
                                <p>Masalah ini sedang dalam proses penanganan oleh tim teknis.</p>
                                <?php if ($masalah['status'] == 'Selesai'): ?>
                                <p class="mb-0"><strong>Status: </strong> Masalah telah ditangani dan diselesaikan.</p>
                                <?php else: ?>
                                <p class="mb-0"><strong>Status: </strong> Masalah sedang dalam proses penanganan.</p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Foto Sarpras -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-image me-2"></i> Foto Sarpras</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($masalah['foto_sarpras'])): ?>
                    <img src="<?php echo ROOT_URL; ?>/uploads/<?php echo $masalah['foto_sarpras']; ?>" alt="<?php echo $masalah['nama_sarpras']; ?>" class="img-fluid img-thumbnail mb-2" style="max-height: 200px;">
                    <?php else: ?>
                    <img src="<?php echo ROOT_URL; ?>/assets/img/no-image.png" alt="No Image" class="img-fluid img-thumbnail mb-2" style="max-height: 200px;">
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Foto Kerusakan -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-image me-2"></i> Foto Kerusakan</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($masalah['foto'])): ?>
                    <img src="<?php echo ROOT_URL; ?>/uploads/<?php echo $masalah['foto']; ?>" alt="Foto Kerusakan" class="img-fluid img-thumbnail mb-2" style="max-height: 300px;">
                    <?php else: ?>
                    <div class="py-5">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-2 text-muted">Tidak ada foto kerusakan</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i> Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($masalah['status'] != 'Selesai'): ?>
                        <button type="button" class="btn btn-success" onclick="showUpdateStatusModal()">
                            <i class="bi bi-arrow-clockwise me-2"></i> Update Status
                        </button>
                        <?php endif; ?>
                        
                        <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $masalah['peminjaman_id']; ?>" class="btn btn-info text-white">
                            <i class="bi bi-arrow-up-right-circle me-2"></i> Detail Peminjaman
                        </a>
                        
                        <a href="<?php echo ROOT_URL; ?>/sarpras/detail/<?php echo $masalah['sarpras_id']; ?>" class="btn btn-primary">
                            <i class="bi bi-box-seam me-2"></i> Detail Sarpras
                        </a>
                        
                        <button type="button" class="btn btn-secondary" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i> Cetak Laporan
                        </button>
                    </div>
                </div>
            </div>
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
                    <input type="hidden" name="update_status" value="1">
                    
                    <div class="mb-3">
                        <label for="currentStatus" class="form-label">Status Saat Ini</label>
                        <input type="text" class="form-control" id="currentStatus" value="<?php echo $masalah['status']; ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_status" class="form-label">Status Baru</label>
                        <select class="form-select" id="new_status" name="new_status" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="Dilaporkan" <?php echo ($masalah['status'] == 'Dilaporkan') ? 'disabled' : ''; ?>>Dilaporkan</option>
                            <option value="Diproses" <?php echo ($masalah['status'] == 'Diproses') ? 'disabled' : ''; ?>>Diproses</option>
                            <option value="Selesai" <?php echo ($masalah['status'] == 'Selesai') ? 'disabled' : ''; ?>>Selesai</option>
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
function showUpdateStatusModal() {
    // Set default next status based on current status
    const currentStatus = '<?php echo $masalah['status']; ?>';
    const selectStatus = document.getElementById('new_status');
    
    if (currentStatus === 'Dilaporkan') {
        selectStatus.value = 'Diproses';
    } else if (currentStatus === 'Diproses') {
        selectStatus.value = 'Selesai';
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}
</script>

<!-- Print Style -->
<style media="print">
    .btn, .sidebar, .navbar, footer {
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