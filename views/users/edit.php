<?php
/**
 * Halaman untuk edit pengguna
 */

// Load model
require_once('models/User.php');

// Inisialisasi model
$userModel = new User($conn);

// Dapatkan ID pengguna dari URL
$id = isset($param) ? (int)$param : 0;

// Jika ID tidak valid, redirect ke halaman pengguna
if ($id <= 0) {
    $_SESSION['message'] = "ID Pengguna tidak valid!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/users');
}

// Get data pengguna berdasarkan ID
$user = $userModel->getUserById($id);

// Jika data tidak ditemukan, redirect ke halaman pengguna
if (!$user) {
    $_SESSION['message'] = "Data Pengguna tidak ditemukan!";
    $_SESSION['message_type'] = "danger";
    redirect(ROOT_URL . '/users');
}

// Get data jurusan
$query = "SELECT * FROM jurusan ORDER BY nama ASC";
$jurusan = mysqli_query($conn, $query);

// Get data kelas
$query = "SELECT k.id, k.tingkat, k.nama_kelas, j.nama as nama_jurusan 
          FROM kelas k 
          JOIN jurusan j ON k.jurusan_id = j.id 
          ORDER BY k.tingkat, k.nama_kelas ASC";
$kelas = mysqli_query($conn, $query);
          
// Proses form edit pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $username = sanitize($_POST['username']);
    $password = $_POST['password']; // Opsional, kosongkan jika tidak ingin mengubah password
    $confirm_password = $_POST['confirm_password'];
    $nama_lengkap = sanitize($_POST['nama_lengkap']);
    $email = sanitize($_POST['email']);
    $no_telp = sanitize($_POST['no_telp']);
    $role = sanitize($_POST['role']);
    
    // Data tambahan untuk siswa
    $nis = isset($_POST['nis']) ? sanitize($_POST['nis']) : '';
    $kelas_id = isset($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : 0;
    $jurusan_id = isset($_POST['jurusan_id']) ? (int)$_POST['jurusan_id'] : 0;
    
    // Validasi input
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username tidak boleh kosong!";
    }
    
    // Validasi password hanya jika diisi
    if (!empty($password) && $password != $confirm_password) {
        $errors[] = "Konfirmasi password tidak sesuai!";
    }
    
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap tidak boleh kosong!";
    }
    
    if (empty($email)) {
        $errors[] = "Email tidak boleh kosong!";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid!";
    }
    
    if (empty($role)) {
        $errors[] = "Role harus dipilih!";
    }
    
    // Validasi data siswa jika role = user
    if ($role == 'user') {
        if (empty($nis)) {
            $errors[] = "NIS tidak boleh kosong untuk siswa!";
        }
        
        if ($kelas_id <= 0) {
            $errors[] = "Kelas harus dipilih untuk siswa!";
        }
        
        if ($jurusan_id <= 0) {
            $errors[] = "Jurusan harus dipilih untuk siswa!";
        }
    }
    
    // Upload foto jika ada
    if ($_FILES['foto']['size'] > 0) {
        // Directory untuk upload
        $targetDir = ROOT_PATH . '/uploads/';
        
        // Cek apakah direktori ada, jika tidak buat direktori
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Upload file
        $uploadResult = uploadFile($_FILES['foto']);
        
        if ($uploadResult['status']) {
            $foto = $uploadResult['file_name'];
            
            // Hapus foto lama jika ada
            if (!empty($user['foto']) && file_exists($targetDir . $user['foto'])) {
                unlink($targetDir . $user['foto']);
            }
        } else {
            $errors[] = $uploadResult['message'];
        }
    }
    
    // Jika tidak ada error, update data
    if (empty($errors)) {
        // Siapkan data untuk diupdate
        $data = [
            'username' => $username,
            'nama_lengkap' => $nama_lengkap,
            'email' => $email,
            'no_telp' => $no_telp,
            'role' => $role
        ];
        
        // Tambahkan password jika diisi
        if (!empty($password)) {
            $data['password'] = $password;
        }
        
        // Tambahkan foto jika ada
        if (isset($foto)) {
            $data['foto'] = $foto;
        }
        
        // Tambahkan data siswa jika role = user
        if ($role == 'user') {
            $data['nis'] = $nis;
            $data['kelas_id'] = $kelas_id;
            $data['jurusan_id'] = $jurusan_id;
        }
        
        // Update data
        if ($userModel->updateUser($id, $data)) {
            $_SESSION['message'] = "Data pengguna berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
            
            // Update session data if the current user is being updated
            if ($_SESSION['user_id'] == $id) {
                $_SESSION['username'] = $username;
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                
                // Update foto in session if it was changed
                if (isset($foto)) {
                    $_SESSION['foto'] = $foto;
                }
            }
            
            redirect(ROOT_URL . '/users');
        } else {
            $_SESSION['message'] = "Gagal memperbarui data pengguna! Username mungkin sudah digunakan.";
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
        <h1 class="h3 mb-0 text-gray-800">Edit Pengguna</h1>
        <div>
            <a href="<?php echo ROOT_URL; ?>/users/detail/<?php echo $id; ?>" class="btn btn-info text-white me-2">
                <i class="bi bi-eye me-2"></i> Detail
            </a>
            <a href="<?php echo ROOT_URL; ?>/users" class="btn btn-secondary">
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
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Form Edit Pengguna</h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="<?php echo ROOT_URL; ?>/users/edit/<?php echo $id; ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <!-- Informasi Dasar -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5 class="border-start border-primary ps-2 mb-3">Informasi Dasar</h5>
                                
                                <div class="row mb-3">
                                    <label for="username" class="col-sm-3 col-form-label">Username <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($_POST['username']) ? $_POST['username'] : $user['username']; ?>" required>
                                        <div class="invalid-feedback">
                                            Username tidak boleh kosong!
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="password" class="col-sm-3 col-form-label">Password</label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control" id="password" name="password">
                                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah password.</small>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="confirm_password" class="col-sm-3 col-form-label">Konfirmasi Password</label>
                                    <div class="col-sm-9">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="nama_lengkap" class="col-sm-3 col-form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo isset($_POST['nama_lengkap']) ? $_POST['nama_lengkap'] : $user['nama_lengkap']; ?>" required>
                                        <div class="invalid-feedback">
                                            Nama lengkap tidak boleh kosong!
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="email" class="col-sm-3 col-form-label">Email <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : $user['email']; ?>" required>
                                        <div class="invalid-feedback">
                                            Email tidak boleh kosong dan harus valid!
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="no_telp" class="col-sm-3 col-form-label">No. Telepon</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?php echo isset($_POST['no_telp']) ? $_POST['no_telp'] : $user['no_telp']; ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="role" class="col-sm-3 col-form-label">Role <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <select class="form-select" id="role" name="role" required onchange="toggleSiswaFields()">
                                            <option value="">-- Pilih Role --</option>
                                            <option value="admin" <?php echo (isset($_POST['role']) ? ($_POST['role'] == 'admin') : ($user['role'] == 'admin')) ? 'selected' : ''; ?>>Administrator</option>
                                            <option value="user" <?php echo (isset($_POST['role']) ? ($_POST['role'] == 'user') : ($user['role'] == 'user')) ? 'selected' : ''; ?>>Siswa</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Role harus dipilih!
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="foto" class="col-sm-3 col-form-label">Foto Profil</label>
                                    <div class="col-sm-9">
                                        <input type="file" class="form-control image-input" id="foto" name="foto" data-preview="imagePreview" accept="image/*">
                                        <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Maks: 2MB. Kosongkan jika tidak ingin mengubah foto.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informasi Siswa (hanya ditampilkan jika role = user) -->
                        <div class="row mb-4" id="siswaFields" style="display: <?php echo $user['role'] == 'user' ? 'block' : 'none'; ?>;">
                            <div class="col-md-12">
                                <h5 class="border-start border-primary ps-2 mb-3">Informasi Siswa</h5>
                                
                                <div class="row mb-3">
                                    <label for="nis" class="col-sm-3 col-form-label">NIS <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="nis" name="nis" value="<?php echo isset($_POST['nis']) ? $_POST['nis'] : $user['nis']; ?>">
                                        <div class="invalid-feedback">
                                            NIS tidak boleh kosong untuk siswa!
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="jurusan_id" class="col-sm-3 col-form-label">Jurusan <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <select class="form-select" id="jurusan_id" name="jurusan_id" onchange="updateKelasOptions()">
                                            <option value="">-- Pilih Jurusan --</option>
                                            <?php foreach ($jurusan as $j): ?>
                                            <option value="<?php echo $j['id']; ?>" 
                                                    <?php echo (isset($_POST['jurusan_id']) ? ($_POST['jurusan_id'] == $j['id']) : ($user['jurusan_id'] == $j['id'])) ? 'selected' : ''; ?>>
                                                <?php echo $j['nama']; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Jurusan harus dipilih untuk siswa!
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <label for="kelas_id" class="col-sm-3 col-form-label">Kelas <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <select class="form-select" id="kelas_id" name="kelas_id">
                                            <option value="">-- Pilih Kelas --</option>
                                            <?php foreach ($kelas as $k): ?>
                                            <option value="<?php echo $k['id']; ?>" 
                                                    data-jurusan="<?php echo $k['nama_jurusan']; ?>"
                                                    <?php echo (isset($_POST['kelas_id']) ? ($_POST['kelas_id'] == $k['id']) : ($user['kelas_id'] == $k['id'])) ? 'selected' : ''; ?>>
                                                <?php echo $k['tingkat'] . ' - ' . $k['nama_kelas']; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Kelas harus dipilih untuk siswa!
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-12">
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
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-image me-2"></i> Foto Profil</h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($user['foto'])): ?>
                    <img id="imagePreview" src="<?php echo ROOT_URL; ?>/uploads/<?php echo $user['foto']; ?>" alt="<?php echo $user['nama_lengkap']; ?>" class="img-fluid img-thumbnail" style="max-height: 200px;">
                    <?php else: ?>
                    <img id="imagePreview" src="<?php echo ROOT_URL; ?>/assets/img/user.png" alt="No Image" class="img-fluid img-thumbnail" style="max-height: 200px;">
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
        
        // Validasi tambahan untuk password
        var password = document.getElementById('password').value;
        var confirm_password = document.getElementById('confirm_password').value;
        
        if (password !== '' && password !== confirm_password) {
            document.getElementById('confirm_password').setCustomValidity('Password tidak sama');
            event.preventDefault();
            event.stopPropagation();
        } else {
            document.getElementById('confirm_password').setCustomValidity('');
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
    
    // Trigger role change to show/hide siswa fields
    toggleSiswaFields();
    
    // Trigger updateKelasOptions to filter kelas by jurusan
    updateKelasOptions();
});

// Function to toggle siswa fields
function toggleSiswaFields() {
    const role = document.getElementById('role').value;
    const siswaFields = document.getElementById('siswaFields');
    const nisInput = document.getElementById('nis');
    const jurusanSelect = document.getElementById('jurusan_id');
    const kelasSelect = document.getElementById('kelas_id');
    
    if (role === 'user') {
        siswaFields.style.display = 'block';
        nisInput.setAttribute('required', '');
        jurusanSelect.setAttribute('required', '');
        kelasSelect.setAttribute('required', '');
    } else {
        siswaFields.style.display = 'none';
        nisInput.removeAttribute('required');
        jurusanSelect.removeAttribute('required');
        kelasSelect.removeAttribute('required');
    }
}

// Function to filter kelas options based on selected jurusan
function updateKelasOptions() {
    const jurusanId = document.getElementById('jurusan_id').value;
    const kelasSelect = document.getElementById('kelas_id');
    const kelasOptions = kelasSelect.options;
    
    // Show/hide kelas options based on jurusan
    for (let i = 1; i < kelasOptions.length; i++) {
        const option = kelasOptions[i];
        const jurusanName = option.getAttribute('data-jurusan');
        
        // If no jurusan selected, show all options
        if (!jurusanId) {
            option.style.display = '';
            continue;
        }
        
        // Get jurusan name from selected jurusan
        const selectedJurusanName = document.querySelector(`#jurusan_id option[value="${jurusanId}"]`).textContent.trim();
        
        // Show only options matching selected jurusan
        if (jurusanName === selectedJurusanName) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    }
}
</script>