<?php
/**
 * Halaman untuk edit sarpras
 */

// Load model
require_once('models/Sarpras.php');
require_once('models/Kategori.php');

// Inisialisasi model
$sarprasModel = new Sarpras($conn);
$kategoriModel = new Kategori($conn);

// Get data kategori untuk dropdown
$kategoriDropdown = $kategoriModel->getKategoriDropdown();

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

// Proses form edit sarpras
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $nama = sanitize($_POST['nama']);
    $kategori_id = (int)$_POST['kategori_id'];
    $deskripsi = sanitize($_POST['deskripsi']);
    $stok = (int)$_POST['stok'];
    $tersedia = (int)$_POST['tersedia'];
    $kondisi = sanitize($_POST['kondisi']);
    $lokasi = sanitize($_POST['lokasi']);
    $tanggal_pengadaan = sanitize($_POST['tanggal_pengadaan']);
    
    // Validasi input
    $errors = [];
    
    if (empty($nama)) {
        $errors[] = "Nama sarpras tidak boleh kosong!";
    }
    
    if ($kategori_id <= 0) {
        $errors[] = "Kategori harus dipilih!";
    }
    
    if ($stok <= 0) {
        $errors[] = "Stok harus lebih dari 0!";
    }
    
    if ($tersedia < 0) {
        $errors[] = "Jumlah tersedia tidak boleh kurang dari 0!";
    }
    
    if ($tersedia > $stok) {
        $errors[] = "Jumlah tersedia tidak boleh lebih dari stok!";
    }
    
    if (empty($kondisi)) {
        $errors[] = "Kondisi harus dipilih!";
    }
    
    if (empty($tanggal_pengadaan)) {
        $errors[] = "Tanggal pengadaan tidak boleh kosong!";
    }
    
    // Upload foto jika ada
    $foto = $sarpras['foto']; // Menggunakan foto lama secara default
    if ($_FILES['foto']['size'] > 0) {
        // Directory untuk upload
        $targetDir = ROOT_PATH . '/uploads/';
        
        // Cek apakah direktori ada, jika tidak buat direktori
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Upload file
        $uploadResult = uploadFile($_FILES['foto'], $targetDir);
        
        if ($uploadResult['status']) {
            $foto = $uploadResult['file_name'];
            
            // Hapus foto lama jika ada
            if (!empty($sarpras['foto']) && file_exists($targetDir . $sarpras['foto'])) {
                unlink($targetDir . $sarpras['foto']);
            }
        } else {
            $errors[] = $uploadResult['message'];
        }
    }
    
    // Jika tidak ada error, update data
    if (empty($errors)) {
        // Siapkan data untuk diupdate
        $data = [
            'kode' => $sarpras['kode'], // Tetap menggunakan kode yang sama
            'nama' => $nama,
            'deskripsi' => $deskripsi,
            'kategori_id' => $kategori_id,
            'stok' => $stok,
            'tersedia' => $tersedia,
            'kondisi' => $kondisi,
            'foto' => $foto,
            'lokasi' => $lokasi,
            'tanggal_pengadaan' => $tanggal_pengadaan
        ];
        
        // Update data
        if ($sarprasModel->updateSarpras($id, $data)) {
            $_SESSION['message'] = "Data sarpras berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
            redirect(ROOT_URL . '/sarpras');
        } else {
            $_SESSION['message'] = "Gagal memperbarui data sarpras!";
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
        <h1 class="h3 mb-0 text-gray-800">Edit Sarpras</h1>
        <div>
            <a href="<?php echo ROOT_URL; ?>/sarpras/detail/<?php echo $id; ?>" class="btn btn-info text-white me-2">
                <i class="bi bi-eye me-2"></i> Detail
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
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Form Edit Sarpras</h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="<?php echo ROOT_URL; ?>/sarpras/edit/<?php echo $id; ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <label for="kode" class="col-sm-3 col-form-label">Kode Sarpras</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="kode" name="kode" value="<?php echo $sarpras['kode']; ?>" readonly>
                                <small class="text-muted">Kode sarpras tidak dapat diubah.</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="nama" class="col-sm-3 col-form-label">Nama Sarpras <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo isset($_POST['nama']) ? $_POST['nama'] : $sarpras['nama']; ?>" required>
                                <div class="invalid-feedback">
                                    Nama sarpras tidak boleh kosong!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="kategori_id" class="col-sm-3 col-form-label">Kategori <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($kategoriDropdown as $kategori): ?>
                                    <option value="<?php echo $kategori['id']; ?>" <?php echo (isset($_POST['kategori_id']) ? ($_POST['kategori_id'] == $kategori['id']) : ($sarpras['kategori_id'] == $kategori['id'])) ? 'selected' : ''; ?>>
                                        <?php echo $kategori['nama']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Kategori harus dipilih!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="deskripsi" class="col-sm-3 col-form-label">Deskripsi</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo isset($_POST['deskripsi']) ? $_POST['deskripsi'] : $sarpras['deskripsi']; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="stok" class="col-sm-3 col-form-label">Stok <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="stok" name="stok" min="1" value="<?php echo isset($_POST['stok']) ? $_POST['stok'] : $sarpras['stok']; ?>" required>
                                <div class="invalid-feedback">
                                    Stok harus lebih dari 0!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="tersedia" class="col-sm-3 col-form-label">Tersedia <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="tersedia" name="tersedia" min="0" value="<?php echo isset($_POST['tersedia']) ? $_POST['tersedia'] : $sarpras['tersedia']; ?>" required>
                                <small class="text-muted">Jumlah tersedia untuk dipinjam saat ini.</small>
                                <div class="invalid-feedback">
                                    Jumlah tersedia tidak boleh kurang dari 0!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="kondisi" class="col-sm-3 col-form-label">Kondisi <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-select" id="kondisi" name="kondisi" required>
                                    <option value="">-- Pilih Kondisi --</option>
                                    <option value="Baik" <?php echo (isset($_POST['kondisi']) ? ($_POST['kondisi'] == 'Baik') : ($sarpras['kondisi'] == 'Baik')) ? 'selected' : ''; ?>>Baik</option>
                                    <option value="Rusak Ringan" <?php echo (isset($_POST['kondisi']) ? ($_POST['kondisi'] == 'Rusak Ringan') : ($sarpras['kondisi'] == 'Rusak Ringan')) ? 'selected' : ''; ?>>Rusak Ringan</option>
                                    <option value="Rusak Berat" <?php echo (isset($_POST['kondisi']) ? ($_POST['kondisi'] == 'Rusak Berat') : ($sarpras['kondisi'] == 'Rusak Berat')) ? 'selected' : ''; ?>>Rusak Berat</option>
                                </select>
                                <div class="invalid-feedback">
                                    Kondisi harus dipilih!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="lokasi" class="col-sm-3 col-form-label">Lokasi Penyimpanan</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?php echo isset($_POST['lokasi']) ? $_POST['lokasi'] : $sarpras['lokasi']; ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="tanggal_pengadaan" class="col-sm-3 col-form-label">Tanggal Pengadaan <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="tanggal_pengadaan" name="tanggal_pengadaan" value="<?php echo isset($_POST['tanggal_pengadaan']) ? $_POST['tanggal_pengadaan'] : $sarpras['tanggal_pengadaan']; ?>" required>
                                <div class="invalid-feedback">
                                    Tanggal pengadaan tidak boleh kosong!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="foto" class="col-sm-3 col-form-label">Foto Sarpras</label>
                            <div class="col-sm-9">
                                <input type="file" class="form-control image-input" id="foto" name="foto" data-preview="imagePreview" accept="image/*">
                                <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Maks: 2MB. Kosongkan jika tidak ingin mengubah foto.</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="reset" class="btn btn-secondary me-md-2">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-image me-2"></i> Foto Sarpras</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($sarpras['foto'])): ?>
                    <img id="imagePreview" src="<?php echo ROOT_URL; ?>/uploads/<?php echo $sarpras['foto']; ?>" alt="<?php echo $sarpras['nama']; ?>" class="img-fluid img-thumbnail" style="max-height: 300px;">
                    <?php else: ?>
                    <img id="imagePreview" src="<?php echo ROOT_URL; ?>/assets/img/no-image.png" alt="No Image" class="img-fluid img-thumbnail" style="max-height: 300px;">
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi</h5>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Field dengan tanda <span class="text-danger">*</span> wajib diisi.</p>
                    <p><i class="bi bi-exclamation-circle-fill text-info me-2"></i> Pastikan informasi yang diinput sudah benar sebelum menyimpan data.</p>
                    <p><i class="bi bi-card-image text-success me-2"></i> Upload foto untuk mempermudah identifikasi sarpras.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview image before upload
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('foto');
    const imagePreview = document.getElementById('imagePreview');
    
    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Set max tersedia = stok
    const stokInput = document.getElementById('stok');
    const tersediaInput = document.getElementById('tersedia');
    
    stokInput.addEventListener('change', function() {
        tersediaInput.max = this.value;
        
        if (parseInt(tersediaInput.value) > parseInt(this.value)) {
            tersediaInput.value = this.value;
        }
    });
    
    // Set initial max tersedia = stok
    tersediaInput.max = stokInput.value;
});
</script>