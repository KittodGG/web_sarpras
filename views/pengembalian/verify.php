<?php
/**
 * Halaman untuk verifikasi pengembalian
 */

// Load model
require_once('models/Pengembalian.php');
require_once('models/Sarpras.php');

// Inisialisasi model
$pengembalianModel = new Pengembalian($conn);
$sarprasModel = new Sarpras($conn);

// Dapatkan ID pengembalian dari URL
$id = isset($param) ? (int)$param : 0;

// Jika ID tidak valid, redirect ke halaman pengembalian
if ($id <= 0) {
    $_SESSION['message'] = "ID Pengembalian tidak valid!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/pengembalian');
}

// Get data pengembalian berdasarkan ID
$pengembalian = $pengembalianModel->getPengembalianById($id);

// Jika data tidak ditemukan, redirect ke halaman pengembalian
if (!$pengembalian) {
    $_SESSION['message'] = "Data Pengembalian tidak ditemukan!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/pengembalian');
}

// Jika pengembalian sudah diverifikasi, redirect ke halaman detail pengembalian
if ($pengembalian['verified_by']) {
    $_SESSION['message'] = "Pengembalian sudah diverifikasi!";
    $_SESSION['message_type'] = "warning";
    redirect(ROOT_URL . '/pengembalian/detail/' . $id);
}

// Proses form verifikasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $kondisi_baru = isset($_POST['kondisi_baru']) ? sanitize($_POST['kondisi_baru']) : null;
    
    // Verifikasi pengembalian
    if ($pengembalianModel->verifyPengembalian($id, $_SESSION['user_id'], $kondisi_baru)) {
        $_SESSION['message'] = "Pengembalian berhasil diverifikasi!";
        $_SESSION['message_type'] = "success";
        redirect(ROOT_URL . '/pengembalian/detail/' . $id);
    } else {
        $_SESSION['message'] = "Gagal verifikasi pengembalian!";
        $_SESSION['message_type'] = "danger";
    }
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Verifikasi Pengembalian</h1>
        <div>
            <a href="<?php echo ROOT_URL; ?>/pengembalian/detail/<?php echo $id; ?>" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i> Form Verifikasi Pengembalian</h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="<?php echo ROOT_URL; ?>/pengembalian/verify/<?php echo $id; ?>" class="needs-validation" novalidate>
                        <!-- Informasi Peminjaman -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="border-start border-primary ps-2 mb-3">Informasi Peminjaman</h5>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Kode Peminjaman</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo $pengembalian['kode_peminjaman']; ?>" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Nama Sarpras</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo $pengembalian['nama_sarpras']; ?> (<?php echo $pengembalian['kode_sarpras']; ?>)" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Jumlah</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo $pengembalian['jumlah']; ?> unit" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Peminjam</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo $pengembalian['nama_peminjam']; ?>" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Tanggal Pinjam</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo formatDate($pengembalian['tanggal_pinjam']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Tanggal Kembali</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo formatDate($pengembalian['tanggal_seharusnya']); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informasi Pengembalian -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="border-start border-primary ps-2 mb-3">Informasi Pengembalian</h5>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Tanggal Kembali Aktual</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo formatDate($pengembalian['tanggal_kembali_aktual']); ?>" readonly>
                                        <?php 
                                        $terlambat = strtotime($pengembalian['tanggal_kembali_aktual']) > strtotime($pengembalian['tanggal_seharusnya']);
                                        if ($terlambat): 
                                        ?>
                                        <span class="badge rounded-pill bg-danger">Terlambat</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Kondisi Saat Dikembalikan</label>
                                    <div class="col-sm-9">
                                        <span class="badge rounded-pill 
                                            <?php 
                                                if ($pengembalian['kondisi'] == 'Baik') echo 'bg-success';
                                                else if ($pengembalian['kondisi'] == 'Rusak Ringan') echo 'bg-warning';
                                                else if ($pengembalian['kondisi'] == 'Rusak Berat') echo 'bg-danger';
                                            ?>">
                                            <?php echo $pengembalian['kondisi']; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if (!empty($pengembalian['catatan'])): ?>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Catatan Pengembalian</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control-plaintext" rows="3" readonly><?php echo $pengembalian['catatan']; ?></textarea>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Verifikasi -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="border-start border-primary ps-2 mb-3">Verifikasi</h5>
                                
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle-fill me-2"></i> Verifikasi pengembalian ini akan mengubah status peminjaman menjadi "Dikembalikan" dan memperbarui stok sarpras yang tersedia.
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="kondisi_baru" class="col-sm-3 col-form-label">Update Kondisi Sarpras</label>
                                    <div class="col-sm-9">
                                        <select class="form-select" id="kondisi_baru" name="kondisi_baru">
                                            <option value="">-- Tidak Perlu Update Kondisi --</option>
                                            <option value="Baik" <?php echo ($pengembalian['kondisi'] == 'Baik') ? 'selected' : ''; ?>>Baik</option>
                                            <option value="Rusak Ringan" <?php echo ($pengembalian['kondisi'] == 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                                            <option value="Rusak Berat" <?php echo ($pengembalian['kondisi'] == 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                                        </select>
                                        <small class="text-muted">Pilih kondisi ini jika Anda ingin memperbarui kondisi sarpras di database sesuai dengan kondisi saat pengembalian.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="<?php echo ROOT_URL; ?>/pengembalian/detail/<?php echo $id; ?>" class="btn btn-secondary me-md-2">
                                        <i class="bi bi-x-circle me-1"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-1"></i> Verifikasi Pengembalian
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
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
                    <?php if (!empty($pengembalian['foto_sarpras'])): ?>
                    <img src="<?php echo ROOT_URL; ?>/uploads/<?php echo $pengembalian['foto_sarpras']; ?>" alt="<?php echo $pengembalian['nama_sarpras']; ?>" class="img-fluid img-thumbnail mb-2" style="max-height: 200px;">
                    <?php else: ?>
                    <img src="<?php echo ROOT_URL; ?>/assets/img/no-image.png" alt="No Image" class="img-fluid img-thumbnail mb-2" style="max-height: 200px;">
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Status Keterlambatan -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Status Keterlambatan</h5>
                </div>
                <div class="card-body text-center">
                    <?php 
                    $tglSeharusnya = new DateTime($pengembalian['tanggal_seharusnya']);
                    $tglAktual = new DateTime($pengembalian['tanggal_kembali_aktual']);
                    $selisih = $tglAktual->diff($tglSeharusnya);
                    $terlambat = $tglAktual > $tglSeharusnya;
                    ?>
                    
                    <?php if ($terlambat): ?>
                    <div class="mb-2"><i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i></div>
                    <h5 class="text-danger">Terlambat</h5>
                    <p>Pengembalian terlambat <?php echo $selisih->days; ?> hari dari tanggal yang ditentukan.</p>
                    <p>Tanggal seharusnya: <?php echo formatDate($pengembalian['tanggal_seharusnya']); ?></p>
                    <p>Tanggal aktual: <?php echo formatDate($pengembalian['tanggal_kembali_aktual']); ?></p>
                    <?php else: ?>
                    <div class="mb-2"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i></div>
                    <h5 class="text-success">Tepat Waktu</h5>
                    <p>Pengembalian dilakukan tepat waktu sesuai dengan tanggal yang ditentukan.</p>
                    <p>Tanggal seharusnya: <?php echo formatDate($pengembalian['tanggal_seharusnya']); ?></p>
                    <p>Tanggal aktual: <?php echo formatDate($pengembalian['tanggal_kembali_aktual']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Informasi -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi</h5>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Verifikasi akan menyelesaikan proses pengembalian dan memperbarui stok sarpras.</p>
                    <p><i class="bi bi-exclamation-circle-fill text-info me-2"></i> Anda dapat memperbarui kondisi sarpras di database sesuai dengan kondisi saat pengembalian.</p>
                    <p><i class="bi bi-check-circle-fill text-success me-2"></i> Setelah diverifikasi, data tidak dapat diubah lagi.</p>
                </div>
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