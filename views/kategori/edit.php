<?php
/**
 * Halaman untuk edit kategori
 */

// Load model
require_once('models/Kategori.php');

// Inisialisasi model
$kategoriModel = new Kategori($conn);

// Dapatkan ID kategori dari URL
$id = isset($param) ? (int)$param : 0;

// Jika ID tidak valid, redirect ke halaman kategori
if ($id <= 0) {
    $_SESSION['message'] = "ID Kategori tidak valid!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/kategori');
}

// Get data kategori berdasarkan ID
$kategori = $kategoriModel->getKategoriById($id);

// Jika data tidak ditemukan, redirect ke halaman kategori
if (!$kategori) {
    $_SESSION['message'] = "Data Kategori tidak ditemukan!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/kategori');
}

// Proses form edit kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $nama = sanitize($_POST['nama']);
    $deskripsi = sanitize($_POST['deskripsi']);
    
    // Validasi input
    $errors = [];
    
    if (empty($nama)) {
        $errors[] = "Nama kategori tidak boleh kosong!";
    }
    
    // Jika tidak ada error, update data
    if (empty($errors)) {
        // Siapkan data untuk diupdate
        $data = [
            'nama' => $nama,
            'deskripsi' => $deskripsi
        ];
        
        // Update data
        if ($kategoriModel->updateKategori($id, $data)) {
            $_SESSION['message'] = "Data kategori berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
            redirect(ROOT_URL . '/kategori');
        } else {
            $_SESSION['message'] = "Gagal memperbarui data kategori!";
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
        <h1 class="h3 mb-0 text-gray-800">Edit Kategori</h1>
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
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Form Edit Kategori</h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="<?php echo ROOT_URL; ?>/kategori/edit/<?php echo $id; ?>" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <label for="nama" class="col-sm-3 col-form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo isset($_POST['nama']) ? $_POST['nama'] : $kategori['nama']; ?>" required>
                                <div class="invalid-feedback">
                                    Nama kategori tidak boleh kosong!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="deskripsi" class="col-sm-3 col-form-label">Deskripsi</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"><?php echo isset($_POST['deskripsi']) ? $_POST['deskripsi'] : $kategori['deskripsi']; ?></textarea>
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
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi</h5>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Field dengan tanda <span class="text-danger">*</span> wajib diisi.</p>
                    <p><i class="bi bi-exclamation-circle-fill text-info me-2"></i> Perubahan nama kategori akan berpengaruh pada semua sarpras yang terkait dengan kategori ini.</p>
                    <p><i class="bi bi-lightbulb-fill text-success me-2"></i> Pastikan perubahan nama kategori tidak membingungkan pengguna dalam mencari sarpras.</p>
                </div>
            </div>
            
            <!-- Sarpras terkait -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i> Sarpras Terkait</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Dapatkan data sarpras yang terkait dengan kategori ini
                    $query = "SELECT COUNT(*) as total FROM sarpras WHERE kategori_id = {$id}";
                    $result = mysqli_query($conn, $query);
                    $row = mysqli_fetch_assoc($result);
                    $total_sarpras = $row['total'];
                    ?>
                    
                    <?php if ($total_sarpras > 0): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i> Kategori ini memiliki <strong><?php echo $total_sarpras; ?></strong> sarpras terkait.
                    </div>
                    <div class="mt-3">
                        <a href="<?php echo ROOT_URL; ?>/sarpras?kategori=<?php echo $id; ?>" class="btn btn-info text-white w-100">
                            <i class="bi bi-box-seam me-2"></i> Lihat Sarpras Terkait
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Kategori ini belum memiliki sarpras.
                    </div>
                    <div class="mt-3">
                        <a href="<?php echo ROOT_URL; ?>/sarpras/add" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i> Tambah Sarpras Baru
                        </a>
                    </div>
                    <?php endif; ?>
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