<?php
/**
 * Halaman untuk tambah masalah
 */

// Load model
require_once('models/Peminjaman.php');
require_once('models/Masalah.php');

// Inisialisasi model
$peminjamanModel = new Peminjaman($conn);
$masalahModel = new Masalah($conn);

// Dapatkan ID peminjaman dari URL
$id = isset($param) ? (int)$param : 0;

// Jika ID tidak valid, redirect ke halaman masalah
if ($id <= 0) {
    $_SESSION['message'] = "ID Peminjaman tidak valid!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/masalah');
}

// Get data peminjaman berdasarkan ID
$peminjaman = $peminjamanModel->getPeminjamanById($id);

// Jika data tidak ditemukan, redirect ke halaman masalah
if (!$peminjaman) {
    $_SESSION['message'] = "Data Peminjaman tidak ditemukan!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/masalah');
}

// Jika peminjaman tidak dalam status Dipinjam atau Terlambat, redirect ke halaman detail peminjaman
if (!in_array($peminjaman['status'], ['Dipinjam', 'Terlambat'])) {
    $_SESSION['message'] = "Masalah hanya dapat dilaporkan untuk peminjaman dengan status Dipinjam atau Terlambat!";
    $_SESSION['message_type'] = "warning";
    redirect(ROOT_URL . '/peminjaman/detail/' . $id);
}

// Proses form tambah masalah
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $deskripsi = sanitize($_POST['deskripsi']);
    $status = 'Dilaporkan'; // Default status saat masalah baru dilaporkan
    
    // Validasi input
    $errors = [];
    
    if (empty($deskripsi)) {
        $errors[] = "Deskripsi masalah tidak boleh kosong!";
    }
    
    // Upload foto jika ada
    $foto = '';
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
        } else {
            $errors[] = $uploadResult['message'];
        }
    }
    
    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        // Siapkan data untuk disimpan
        $data = [
            'peminjaman_id' => $id,
            'deskripsi' => $deskripsi,
            'status' => $status,
            'foto' => $foto
        ];
        
        // Simpan data
        $result = $masalahModel->addMasalah($data);
        if ($result) {
            $_SESSION['message'] = "Masalah berhasil dilaporkan!";
            $_SESSION['message_type'] = "success";
            redirect(ROOT_URL . '/masalah/detail/' . $result);
        } else {
            $_SESSION['message'] = "Gagal melaporkan masalah!";
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
        <h1 class="h3 mb-0 text-gray-800">Laporkan Masalah</h1>
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
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Form Laporan Masalah</h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="<?php echo ROOT_URL; ?>/masalah/add/<?php echo $id; ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
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
                            </div>
                        </div>
                        
                        <!-- Data Masalah -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="border-start border-primary ps-2 mb-3">Detail Masalah</h5>
                                
                                <div class="row mb-3">
                                    <label for="deskripsi" class="col-sm-3 col-form-label">Deskripsi Masalah <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required><?php echo isset($_POST['deskripsi']) ? $_POST['deskripsi'] : ''; ?></textarea>
                                        <div class="invalid-feedback">
                                            Deskripsi masalah tidak boleh kosong!
                                        </div>
                                        <small class="text-muted">Jelaskan masalah atau kerusakan dengan detail.</small>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="foto" class="col-sm-3 col-form-label">Foto Kerusakan</label>
                                    <div class="col-sm-9">
                                        <input type="file" class="form-control image-input" id="foto" name="foto" data-preview="imagePreview" accept="image/*">
                                        <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Maks: 2MB</small>
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
                                        <i class="bi bi-save me-1"></i> Laporkan Masalah
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
                    <?php if (!empty($peminjaman['sarpras_foto'])): ?>
                    <img src="<?php echo ROOT_URL; ?>/uploads/<?php echo $peminjaman['sarpras_foto']; ?>" alt="<?php echo $peminjaman['nama_sarpras']; ?>" class="img-fluid img-thumbnail mb-2" style="max-height: 200px;">
                    <?php else: ?>
                    <img src="<?php echo ROOT_URL; ?>/assets/img/no-image.png" alt="No Image" class="img-fluid img-thumbnail mb-2" style="max-height: 200px;">
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Preview Foto Kerusakan -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-image me-2"></i> Preview Foto Kerusakan</h5>
                </div>
                <div class="card-body text-center">
                    <img id="imagePreview" src="<?php echo ROOT_URL; ?>/assets/img/no-image.png" alt="Preview" class="img-fluid img-thumbnail" style="max-height: 200px;">
                </div>
            </div>
            
            <!-- Informasi -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi</h5>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Pastikan deskripsi masalah jelas dan detail untuk mempermudah proses penanganan.</p>
                    <p><i class="bi bi-camera-fill text-info me-2"></i> Unggah foto kerusakan untuk memberikan gambaran visual tentang masalah yang terjadi.</p>
                    <p><i class="bi bi-check-circle-fill text-success me-2"></i> Setelah laporan masalah dikirim, petugas akan memproses laporan Anda.</p>
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
    
    // Preview image before upload
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
});
</script>