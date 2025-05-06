<?php
/**
 * Halaman untuk persetujuan peminjaman
 */

// Load model
require_once('models/Peminjaman.php');
require_once('models/Sarpras.php');
require_once('models/Jadwal.php');

// Inisialisasi model
$peminjamanModel = new Peminjaman($conn);
$sarprasModel = new Sarpras($conn);
$jadwalModel = new Jadwal($conn);

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

// Jika peminjaman tidak dalam status Menunggu, redirect ke halaman detail
if ($peminjaman['status'] != 'Menunggu') {
    $_SESSION['message'] = "Peminjaman sudah diproses!";
    $_SESSION['message_type'] = "warning";
    redirect(ROOT_URL . '/peminjaman/detail/' . $id);
}

// Cek ketersediaan sarpras pada rentang tanggal yang dipilih
$ketersediaan = $jadwalModel->checkAvailability(
    $peminjaman['sarpras_id'], 
    $peminjaman['tanggal_pinjam'], 
    $peminjaman['tanggal_kembali'],
    $id
);

// Proses form persetujuan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $status = sanitize($_POST['status']);
    $catatan = sanitize($_POST['catatan']);
    
    // Validasi status
    if (!in_array($status, ['Disetujui', 'Ditolak'])) {
        $_SESSION['message'] = "Status tidak valid!";
        $_SESSION['message_type'] = "danger";
    } else {
        // Jika status Disetujui, cek ketersediaan
        if ($status == 'Disetujui') {
            if (!$ketersediaan['status']) {
                $_SESSION['message'] = "Sarpras tidak tersedia pada rentang tanggal yang dipilih!";
                $_SESSION['message_type'] = "danger";
                redirect(ROOT_URL . '/peminjaman/approve/' . $id);
            } else if ($ketersediaan['tersedia'] < $peminjaman['jumlah']) {
                $_SESSION['message'] = "Jumlah yang tersedia ({$ketersediaan['tersedia']} unit) tidak mencukupi untuk peminjaman ini!";
                $_SESSION['message_type'] = "danger";
                redirect(ROOT_URL . '/peminjaman/approve/' . $id);
            }
        }
        
        // Proses persetujuan
        if ($peminjamanModel->approvePeminjaman($id, $status, $_SESSION['user_id'], $catatan)) {
            $_SESSION['message'] = "Peminjaman berhasil " . ($status == 'Disetujui' ? 'disetujui' : 'ditolak') . "!";
            $_SESSION['message_type'] = "success";
            redirect(ROOT_URL . '/peminjaman/detail/' . $id);
        } else {
            $_SESSION['message'] = "Gagal memproses persetujuan peminjaman!";
            $_SESSION['message_type'] = "danger";
        }
    }
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Persetujuan Peminjaman</h1>
        <div>
            <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $id; ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i> Form Persetujuan Peminjaman</h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="<?php echo ROOT_URL; ?>/peminjaman/approve/<?php echo $id; ?>" class="needs-validation" novalidate>
                        <!-- Informasi Peminjaman -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="border-start border-primary ps-2 mb-3">Informasi Peminjaman</h5>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Kode Peminjaman</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo $peminjaman['kode_peminjaman']; ?>" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Nama Sarpras</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo $peminjaman['nama_sarpras']; ?> (<?php echo $peminjaman['kode_sarpras']; ?>)" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Kategori</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo $peminjaman['nama_kategori']; ?>" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Jumlah</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo $peminjaman['jumlah']; ?> unit" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Peminjam</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo $peminjaman['nama_peminjam']; ?>" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Kontak Peminjam</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo $peminjaman['email_peminjam']; ?> | <?php echo !empty($peminjaman['telp_peminjam']) ? $peminjaman['telp_peminjam'] : 'Tidak ada'; ?>" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Tanggal Pinjam</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo formatDate($peminjaman['tanggal_pinjam']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Tanggal Kembali</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo formatDate($peminjaman['tanggal_kembali']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Tujuan Peminjaman</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control-plaintext" rows="3" readonly><?php echo $peminjaman['tujuan_peminjaman']; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Persetujuan -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="border-start border-primary ps-2 mb-3">Persetujuan</h5>
                                
                                <div class="row mb-3">
                                    <label for="status" class="col-sm-3 col-form-label">Status <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="statusApprove" value="Disetujui" required>
                                            <label class="form-check-label" for="statusApprove">Disetujui</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="statusReject" value="Ditolak" required>
                                            <label class="form-check-label" for="statusReject">Ditolak</label>
                                        </div>
                                        <div class="invalid-feedback">
                                            Status harus dipilih!
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="catatan" class="col-sm-3 col-form-label">Catatan</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Masukkan catatan (opsional)"><?php echo isset($_POST['catatan']) ? $_POST['catatan'] : ''; ?></textarea>
                                        <small class="text-muted">Berikan alasan jika ditolak atau catatan tambahan jika disetujui.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $id; ?>" class="btn btn-secondary me-md-2">
                                        <i class="bi bi-x-circle me-1"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Simpan Persetujuan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Ketersediaan -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i> Ketersediaan Sarpras</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-3">
                        <?php if ($ketersediaan['status']): ?>
                            <?php if ($ketersediaan['tersedia'] >= $peminjaman['jumlah']): ?>
                            <div class="mb-2"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i></div>
                            <h5>Tersedia</h5>
                            <p class="text-success">Sarpras tersedia untuk dipinjam pada rentang tanggal yang dipilih.</p>
                            <p>Stok tersedia: <?php echo $ketersediaan['tersedia']; ?> unit</p>
                            <p>Jumlah yang akan dipinjam: <?php echo $peminjaman['jumlah']; ?> unit</p>
                            <?php else: ?>
                            <div class="mb-2"><i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i></div>
                            <h5>Tidak Tersedia</h5>
                            <p class="text-danger">Jumlah yang ingin dipinjam (<?php echo $peminjaman['jumlah']; ?> unit) melebihi stok yang tersedia (<?php echo $ketersediaan['tersedia']; ?> unit).</p>
                            <?php endif; ?>
                        <?php else: ?>
                        <div class="mb-2"><i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i></div>
                        <h5>Tidak Tersedia</h5>
                        <p class="text-danger"><?php echo $ketersediaan['message']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid mt-3">
                        <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#jadwalModal">
                            <i class="bi bi-calendar-event me-2"></i> Lihat Jadwal Peminjaman
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Informasi -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi</h5>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Pastikan ketersediaan sarpras sebelum menyetujui peminjaman.</p>
                    <p><i class="bi bi-exclamation-circle-fill text-info me-2"></i> Jika Anda menolak peminjaman, berikan alasan yang jelas di catatan.</p>
                    <p><i class="bi bi-calendar-check text-success me-2"></i> Perhatikan tanggal pinjam dan kembali apakah tidak bentrok dengan peminjaman lain.</p>
                    <p><i class="bi bi-person-badge text-primary me-2"></i> Pastikan peminjam belum memiliki peminjaman aktif untuk sarpras yang sama.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Jadwal -->
<div class="modal fade" id="jadwalModal" tabindex="-1" aria-labelledby="jadwalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="jadwalModalLabel">Jadwal Peminjaman Sarpras</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-primary">
                    <i class="bi bi-info-circle-fill me-2"></i> Daftar peminjaman aktif pada rentang tanggal <?php echo formatDate($peminjaman['tanggal_pinjam']); ?> - <?php echo formatDate($peminjaman['tanggal_kembali']); ?>.
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Sarpras</th>
                                <th>Peminjam</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$ketersediaan['status'] && isset($ketersediaan['peminjaman'])): ?>
                                <?php foreach ($ketersediaan['peminjaman'] as $pinjam): ?>
                                <tr>
                                    <td><?php echo $pinjam['kode_peminjaman']; ?></td>
                                    <td><?php echo $pinjam['nama_sarpras']; ?></td>
                                    <td><?php echo $pinjam['nama_peminjam']; ?></td>
                                    <td><?php echo formatDate($pinjam['tanggal_pinjam']); ?></td>
                                    <td><?php echo formatDate($pinjam['tanggal_kembali']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $pinjam['status']; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada jadwal peminjaman aktif pada rentang tanggal yang dipilih.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validasi form
    var form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
});
</script>