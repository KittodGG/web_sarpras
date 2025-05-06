<?php
/**
 * Halaman untuk tambah pengembalian
 */

// Load model
require_once('models/Peminjaman.php');
require_once('models/Pengembalian.php');
require_once('models/Sarpras.php');

// Inisialisasi model
$peminjamanModel = new Peminjaman($conn);
$pengembalianModel = new Pengembalian($conn);
$sarprasModel = new Sarpras($conn);

// Dapatkan ID peminjaman dari URL
$id = isset($param) ? (int)$param : 0;

// Jika ID tidak valid, redirect ke halaman pengembalian
if ($id <= 0) {
    $_SESSION['message'] = "ID Peminjaman tidak valid!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/pengembalian');
}

// Get data peminjaman berdasarkan ID
$peminjaman = $peminjamanModel->getPeminjamanById($id);

// Jika data tidak ditemukan, redirect ke halaman pengembalian
if (!$peminjaman) {
    $_SESSION['message'] = "Data Peminjaman tidak ditemukan!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/pengembalian');
}

// Jika peminjaman tidak dalam status Dipinjam atau Terlambat, redirect ke halaman detail peminjaman
if (!in_array($peminjaman['status'], ['Dipinjam', 'Terlambat'])) {
    $_SESSION['message'] = "Peminjaman tidak dalam status Dipinjam atau Terlambat!";
    $_SESSION['message_type'] = "warning";
    redirect(ROOT_URL . '/peminjaman/detail/' . $id);
}

// Cek apakah peminjaman sudah ada data pengembalian
$cekPengembalian = $pengembalianModel->getPengembalianByPeminjamanId($id);
if ($cekPengembalian) {
    $_SESSION['message'] = "Peminjaman ini sudah memiliki data pengembalian!";
    $_SESSION['message_type'] = "warning";
    redirect(ROOT_URL . '/pengembalian/detail/' . $cekPengembalian['id']);
}

// Proses form tambah pengembalian
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $tanggal_kembali_aktual = sanitize($_POST['tanggal_kembali_aktual']);
    $kondisi = sanitize($_POST['kondisi']);
    $catatan = sanitize($_POST['catatan']);
    
    // Validasi input
    $errors = [];
    
    if (empty($tanggal_kembali_aktual)) {
        $errors[] = "Tanggal kembali aktual tidak boleh kosong!";
    }
    
    if (empty($kondisi)) {
        $errors[] = "Kondisi sarpras harus dipilih!";
    }
    
    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        // Siapkan data untuk disimpan
        $data = [
            'peminjaman_id' => $id,
            'tanggal_kembali_aktual' => $tanggal_kembali_aktual,
            'kondisi' => $kondisi,
            'catatan' => $catatan
        ];
        
        // Simpan data
        $result = $pengembalianModel->addPengembalian($data);
        if ($result) {
            $_SESSION['message'] = "Data pengembalian berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
            redirect(ROOT_URL . '/pengembalian/detail/' . $result);
        } else {
            $_SESSION['message'] = "Gagal menambahkan data pengembalian!";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        // Set error message
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = "danger";
    }
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Proses Pengembalian Sarpras</h1>
        <div>
            <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $id; ?>" class="btn btn-info text-white me-2">
                <i class="bi bi-eye me-2"></i> Detail Peminjaman
            </a>
            <a href="<?php echo ROOT_URL; ?>/pengembalian" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="bi bi-arrow-down-left-circle me-2"></i> Form Pengembalian Sarpras</h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="<?php echo ROOT_URL; ?>/pengembalian/add/<?php echo $id; ?>" class="needs-validation" novalidate>
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
                                    <label class="col-sm-3 col-form-label">Tanggal Pinjam</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo formatDate($peminjaman['tanggal_pinjam']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Tanggal Kembali</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control-plaintext" value="<?php echo formatDate($peminjaman['tanggal_kembali']); ?>" readonly>
                                        <?php if ($peminjaman['status'] == 'Terlambat'): ?>
                                        <span class="badge rounded-pill bg-danger">Terlambat</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label class="col-sm-3 col-form-label">Status</label>
                                    <div class="col-sm-9">
                                        <span class="badge rounded-pill 
                                            <?php 
                                                if ($peminjaman['status'] == 'Dipinjam') echo 'bg-primary';
                                                else if ($peminjaman['status'] == 'Terlambat') echo 'bg-danger';
                                            ?>">
                                            <?php echo $peminjaman['status']; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pengembalian -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="border-start border-primary ps-2 mb-3">Data Pengembalian</h5>
                                
                                <div class="row mb-3">
                                    <label for="tanggal_kembali_aktual" class="col-sm-3 col-form-label">Tanggal Kembali <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="date" class="form-control" id="tanggal_kembali_aktual" name="tanggal_kembali_aktual" value="<?php echo isset($_POST['tanggal_kembali_aktual']) ? $_POST['tanggal_kembali_aktual'] : date('Y-m-d'); ?>" required>
                                        <div class="invalid-feedback">
                                            Tanggal kembali aktual tidak boleh kosong!
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="kondisi" class="col-sm-3 col-form-label">Kondisi <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <select class="form-select" id="kondisi" name="kondisi" required>
                                            <option value="">-- Pilih Kondisi --</option>
                                            <option value="Baik" <?php echo (isset($_POST['kondisi']) && $_POST['kondisi'] == 'Baik') ? 'selected' : ''; ?>>Baik</option>
                                            <option value="Rusak Ringan" <?php echo (isset($_POST['kondisi']) && $_POST['kondisi'] == 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                                            <option value="Rusak Berat" <?php echo (isset($_POST['kondisi']) && $_POST['kondisi'] == 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Kondisi harus dipilih!
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="catatan" class="col-sm-3 col-form-label">Catatan</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Masukkan catatan (opsional)"><?php echo isset($_POST['catatan']) ? $_POST['catatan'] : ''; ?></textarea>
                                        <small class="text-muted">Berikan catatan jika ada kerusakan atau informasi penting lainnya.</small>
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
                                        <i class="bi bi-save me-1"></i> Simpan Pengembalian
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
                    <?php if (!empty($peminjaman['foto_sarpras'])): ?>
                    <img src="<?php echo ROOT_URL; ?>/uploads/<?php echo $peminjaman['foto_sarpras']; ?>" alt="<?php echo $peminjaman['nama_sarpras']; ?>" class="img-fluid img-thumbnail mb-2" style="max-height: 200px;">
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
                    $tglKembali = new DateTime($peminjaman['tanggal_kembali']);
                    $tglHariIni = new DateTime(date('Y-m-d'));
                    $selisih = $tglHariIni->diff($tglKembali);
                    $terlambat = $tglHariIni > $tglKembali;
                    ?>
                    
                    <?php if ($terlambat): ?>
                    <div class="mb-2"><i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i></div>
                    <h5 class="text-danger">Terlambat</h5>
                    <p>Pengembalian terlambat <?php echo $selisih->days; ?> hari dari tanggal yang ditentukan.</p>
                    <p>Tanggal seharusnya: <?php echo formatDate($peminjaman['tanggal_kembali']); ?></p>
                    <?php else: ?>
                    <div class="mb-2"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i></div>
                    <h5 class="text-success">Tepat Waktu</h5>
                    <p>Pengembalian dilakukan tepat waktu sesuai dengan tanggal yang ditentukan.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Informasi -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi</h5>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Pastikan kondisi sarpras diperiksa dengan teliti sebelum menyelesaikan proses pengembalian.</p>
                    <p><i class="bi bi-exclamation-circle-fill text-info me-2"></i> Jika ada kerusakan, berikan catatan detail tentang kerusakannya.</p>
                    <p><i class="bi bi-calendar-check text-success me-2"></i> Tanggal kembali aktual akan otomatis diisi dengan tanggal hari ini, namun dapat diubah jika diperlukan.</p>
                    <p><i class="bi bi-person-badge text-primary me-2"></i> Setelah data pengembalian disimpan, status peminjaman akan otomatis diubah menjadi "Dikembalikan".</p>
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
    
    // Update kondisi sarpras
    var kondisiSelect = document.getElementById('kondisi');
    kondisiSelect.addEventListener('change', function() {
        var selectedKondisi = this.value;
        var catatanTextarea = document.getElementById('catatan');
        
        // Jika kondisi rusak, berikan placeholder untuk detail kerusakan
        if (selectedKondisi === 'Rusak Ringan' || selectedKondisi === 'Rusak Berat') {
            catatanTextarea.placeholder = 'Silakan berikan detail kerusakan sarpras...';
        } else {
            catatanTextarea.placeholder = 'Masukkan catatan (opsional)';
        }
    });
});
</script>