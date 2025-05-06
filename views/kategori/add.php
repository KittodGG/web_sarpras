<?php
/**
 * Halaman untuk tambah kategori
 */

// Load model
require_once('models/Kategori.php');

// Inisialisasi model
$kategoriModel = new Kategori($conn);

// Proses form tambah kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $nama = sanitize($_POST['nama']);
    $deskripsi = sanitize($_POST['deskripsi']);
    
    // Validasi input
    $errors = [];
    
    if (empty($nama)) {
        $errors[] = "Nama kategori tidak boleh kosong!";
    }
    
    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        // Siapkan data untuk disimpan
        $data = [
            'nama' => $nama,
            'deskripsi' => $deskripsi
        ];
        
        // Simpan data
        if ($kategoriModel->addKategori($data)) {
            $_SESSION['message'] = "Data kategori berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
            redirect(ROOT_URL . '/kategori');
        } else {
            $_SESSION['message'] = "Gagal menambahkan data kategori!";
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
        <h1 class="h3 mb-0 text-gray-800">Tambah Kategori Baru</h1>
        <a href="<?php echo ROOT_URL; ?>/kategori" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Kembali
        </a>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i> Form Tambah Kategori</h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="<?php echo ROOT_URL; ?>/kategori/add" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <label for="nama" class="col-sm-3 col-form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo isset($_POST['nama']) ? $_POST['nama'] : ''; ?>" required>
                                <div class="invalid-feedback">
                                    Nama kategori tidak boleh kosong!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="deskripsi" class="col-sm-3 col-form-label">Deskripsi</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"><?php echo isset($_POST['deskripsi']) ? $_POST['deskripsi'] : ''; ?></textarea>
                                <small class="text-muted">Deskripsi singkat tentang kategori (opsional).</small>
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
                                        <i class="bi bi-save me-1"></i> Simpan
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
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi</h5>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Field dengan tanda <span class="text-danger">*</span> wajib diisi.</p>
                    <p><i class="bi bi-exclamation-circle-fill text-info me-2"></i> Kategori digunakan untuk mengelompokkan sarpras agar lebih mudah dalam pengelolaan dan pencarian.</p>
                    <p><i class="bi bi-lightbulb-fill text-success me-2"></i> Buat nama kategori yang jelas dan deskriptif untuk memudahkan identifikasi jenis sarpras.</p>
                    <p><i class="bi bi-box-seam text-primary me-2"></i> Setelah membuat kategori, Anda dapat mulai menambahkan sarpras dengan kategori tersebut.</p>
                </div>
            </div>
            
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-tags me-2"></i> Kategori Populer</h5>
                </div>
                <div class="card-body">
                    <p>Beberapa contoh kategori yang sering digunakan:</p>
                    <ul>
                        <li>Elektronik</li>
                        <li>Furniture</li>
                        <li>Alat Laboratorium</li>
                        <li>Peralatan Olahraga</li>
                        <li>Alat Peraga</li>
                        <li>Peralatan Musik</li>
                        <li>Perlengkapan Kelas</li>
                    </ul>
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