<?php
/**
 * Halaman untuk tambah peminjaman
 */

// Load model
require_once('models/Sarpras.php');
require_once('models/Peminjaman.php');
require_once('models/Jadwal.php');
require_once('models/User.php');

// Inisialisasi model
$sarprasModel = new Sarpras($conn);
$peminjamanModel = new Peminjaman($conn);
$jadwalModel = new Jadwal($conn);
$userModel = new User($conn);

// Get data sarpras tersedia
$sarprasDropdown = $sarprasModel->getAvailableSarpras();

// Jika tidak ada sarpras tersedia, redirect ke halaman peminjaman
if (empty($sarprasDropdown)) {
    $_SESSION['message'] = "Tidak ada sarpras yang tersedia untuk dipinjam!";
    $_SESSION['message_type'] = "warning";
    redirect(ROOT_URL . '/peminjaman');
}

// Get data user (siswa) untuk dropdown
$query = "SELECT u.id, u.nama_lengkap, s.nis
          FROM users u
          JOIN siswa s ON u.id = s.user_id
          WHERE u.role = 'user'
          ORDER BY u.nama_lengkap ASC";
$result = mysqli_query($conn, $query);
$userDropdown = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $userDropdown[] = $row;
    }
}

// Cek apakah ada parameter sarpras_id dari URL
$preSelectedSarpras = isset($_GET['sarpras_id']) ? (int)$_GET['sarpras_id'] : 0;

// Generate kode peminjaman unik
$kodePeminjaman = generateUniqueCode('PJM-', 8);

// Proses form tambah peminjaman
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $user_id = (int)$_POST['user_id'];
    $sarpras_id = (int)$_POST['sarpras_id'];
    $jumlah = (int)$_POST['jumlah'];
    $tanggal_pinjam = sanitize($_POST['tanggal_pinjam']);
    $tanggal_kembali = sanitize($_POST['tanggal_kembali']);
    $tujuan_peminjaman = sanitize($_POST['tujuan_peminjaman']);
    $catatan = sanitize($_POST['catatan']);
    $status = 'Menunggu'; // Default status saat peminjaman baru
    
    // Validasi input
    $errors = [];
    
    if ($user_id <= 0) {
        $errors[] = "Peminjam harus dipilih!";
    }
    
    if ($sarpras_id <= 0) {
        $errors[] = "Sarpras harus dipilih!";
    }
    
    if ($jumlah <= 0) {
        $errors[] = "Jumlah peminjaman harus lebih dari 0!";
    }
    
    if (empty($tanggal_pinjam)) {
        $errors[] = "Tanggal pinjam tidak boleh kosong!";
    }
    
    if (empty($tanggal_kembali)) {
        $errors[] = "Tanggal kembali tidak boleh kosong!";
    }
    
    if (strtotime($tanggal_pinjam) > strtotime($tanggal_kembali)) {
        $errors[] = "Tanggal kembali harus setelah tanggal pinjam!";
    }
    
    if (empty($tujuan_peminjaman)) {
        $errors[] = "Tujuan peminjaman tidak boleh kosong!";
    }
    
    // Cek ketersediaan sarpras pada rentang tanggal yang dipilih
    if ($sarpras_id > 0 && !empty($tanggal_pinjam) && !empty($tanggal_kembali)) {
        $ketersediaan = $jadwalModel->checkAvailability($sarpras_id, $tanggal_pinjam, $tanggal_kembali);
        
        if (!$ketersediaan['status']) {
            $errors[] = "Sarpras tidak tersedia pada rentang tanggal yang dipilih!";
        } else if ($ketersediaan['tersedia'] < $jumlah) {
            $errors[] = "Jumlah yang tersedia ({$ketersediaan['tersedia']} unit) tidak mencukupi untuk peminjaman ini!";
        }
    }
    
    // Cek apakah pengguna sudah meminjam sarpras yang sama dan belum dikembalikan
    if ($user_id > 0 && $sarpras_id > 0) {
        if ($peminjamanModel->hasActiveLoan($user_id, $sarpras_id)) {
            $errors[] = "Pengguna ini sudah memiliki peminjaman aktif untuk sarpras yang sama!";
        }
    }
    
    // Jika tidak ada error, simpan data
    if (empty($errors)) {
        // Siapkan data untuk disimpan
        $data = [
            'kode_peminjaman' => $kodePeminjaman,
            'user_id' => $user_id,
            'sarpras_id' => $sarpras_id,
            'jumlah' => $jumlah,
            'tanggal_pinjam' => $tanggal_pinjam,
            'tanggal_kembali' => $tanggal_kembali,
            'tujuan_peminjaman' => $tujuan_peminjaman,
            'catatan' => $catatan,
            'status' => $status
        ];
        
        // Simpan data
        $result = $peminjamanModel->addPeminjaman($data);
        if ($result) {
            $_SESSION['message'] = "Data peminjaman berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
            redirect(ROOT_URL . '/peminjaman');
        } else {
            $_SESSION['message'] = "Gagal menambahkan data peminjaman!";
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
        <h1 class="h3 mb-0 text-gray-800">Tambah Peminjaman Baru</h1>
        <a href="<?php echo ROOT_URL; ?>/peminjaman" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Kembali
        </a>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i> Form Tambah Peminjaman</h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="<?php echo ROOT_URL; ?>/peminjaman/add" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <label for="kode_peminjaman" class="col-sm-3 col-form-label">Kode Peminjaman</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="kode_peminjaman" name="kode_peminjaman" value="<?php echo $kodePeminjaman; ?>" readonly>
                                <small class="text-muted">Kode peminjaman otomatis dibuat oleh sistem.</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="user_id" class="col-sm-3 col-form-label">Peminjam <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">-- Pilih Peminjam --</option>
                                    <?php foreach ($userDropdown as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo (isset($_POST['user_id']) && $_POST['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                        <?php echo $user['nama_lengkap']; ?> (<?php echo $user['nis']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Peminjam harus dipilih!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="sarpras_id" class="col-sm-3 col-form-label">Sarpras <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-select" id="sarpras_id" name="sarpras_id" required>
                                    <option value="">-- Pilih Sarpras --</option>
                                    <?php foreach ($sarprasDropdown as $sarpras): ?>
                                    <option value="<?php echo $sarpras['id']; ?>" 
                                            data-stok="<?php echo $sarpras['tersedia']; ?>" 
                                            <?php echo (isset($_POST['sarpras_id']) && $_POST['sarpras_id'] == $sarpras['id']) || $preSelectedSarpras == $sarpras['id'] ? 'selected' : ''; ?>>
                                        <?php echo $sarpras['nama']; ?> (<?php echo $sarpras['kode']; ?>) - Tersedia: <?php echo $sarpras['tersedia']; ?> unit
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Sarpras harus dipilih!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="jumlah" class="col-sm-3 col-form-label">Jumlah <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" value="<?php echo isset($_POST['jumlah']) ? $_POST['jumlah'] : '1'; ?>" required>
                                <div class="invalid-feedback">
                                    Jumlah harus lebih dari 0!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="tanggal_pinjam" class="col-sm-3 col-form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam" min="<?php echo date('Y-m-d'); ?>" value="<?php echo isset($_POST['tanggal_pinjam']) ? $_POST['tanggal_pinjam'] : date('Y-m-d'); ?>" required>
                                <div class="invalid-feedback">
                                    Tanggal pinjam tidak boleh kosong!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="tanggal_kembali" class="col-sm-3 col-form-label">Tanggal Kembali <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo isset($_POST['tanggal_kembali']) ? $_POST['tanggal_kembali'] : date('Y-m-d', strtotime('+1 day')); ?>" required>
                                <div class="invalid-feedback">
                                    Tanggal kembali tidak boleh kosong!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="tujuan_peminjaman" class="col-sm-3 col-form-label">Tujuan Peminjaman <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="tujuan_peminjaman" name="tujuan_peminjaman" rows="3" required><?php echo isset($_POST['tujuan_peminjaman']) ? $_POST['tujuan_peminjaman'] : ''; ?></textarea>
                                <div class="invalid-feedback">
                                    Tujuan peminjaman tidak boleh kosong!
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <label for="catatan" class="col-sm-3 col-form-label">Catatan</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="catatan" name="catatan" rows="2"><?php echo isset($_POST['catatan']) ? $_POST['catatan'] : ''; ?></textarea>
                                <small class="text-muted">Catatan tambahan jika diperlukan.</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="reset" class="btn btn-secondary me-md-2">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                                    </button>
                                    <button type="button" class="btn btn-success me-md-2" id="btnCheckAvailability">
                                        <i class="bi bi-calendar-check me-1"></i> Cek Ketersediaan
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
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i> Ketersediaan</h5>
                </div>
                <div class="card-body text-center" id="availabilityInfo">
                    <p class="text-muted mb-0">Pilih sarpras dan tanggal peminjaman, lalu klik tombol "Cek Ketersediaan" untuk melihat status ketersediaan sarpras.</p>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi</h5>
                </div>
                <div class="card-body">
                    <p><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Field dengan tanda <span class="text-danger">*</span> wajib diisi.</p>
                    <p><i class="bi bi-exclamation-circle-fill text-info me-2"></i> Pastikan informasi yang diinput sudah benar sebelum menyimpan data.</p>
                    <p><i class="bi bi-calendar-check text-success me-2"></i> Cek ketersediaan sarpras terlebih dahulu untuk memastikan sarpras tersedia pada rentang tanggal yang dipilih.</p>
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
                <div class="text-center" id="loadingJadwal">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat jadwal peminjaman...</p>
                </div>
                <div id="jadwalList" class="d-none">
                    <div class="alert alert-primary">
                        <i class="bi bi-info-circle-fill me-2"></i> Daftar peminjaman aktif pada rentang tanggal yang dipilih.
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
                            <tbody id="jadwalTable">
                                <!-- Data jadwal akan diisi di sini -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="noJadwal" class="text-center py-4 d-none">
                    <i class="bi bi-calendar-check text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Tidak ada peminjaman</h5>
                    <p class="text-muted">Tidak ada peminjaman aktif pada rentang tanggal yang dipilih.</p>
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
    const sarprasSelect = document.getElementById('sarpras_id');
    const jumlahInput = document.getElementById('jumlah');
    const tanggalPinjamInput = document.getElementById('tanggal_pinjam');
    const tanggalKembaliInput = document.getElementById('tanggal_kembali');
    const btnCheckAvailability = document.getElementById('btnCheckAvailability');
    const availabilityInfo = document.getElementById('availabilityInfo');
    
    // Set max jumlah berdasarkan stok tersedia
    sarprasSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const stokTersedia = selectedOption.dataset.stok;
        
        if (stokTersedia) {
            jumlahInput.max = stokTersedia;
            
            if (parseInt(jumlahInput.value) > parseInt(stokTersedia)) {
                jumlahInput.value = stokTersedia;
            }
        } else {
            jumlahInput.max = '';
        }
    });
    
    // Trigger change event untuk set max jumlah pada load
    sarprasSelect.dispatchEvent(new Event('change'));
    
    // Set validasi tanggal kembali harus setelah tanggal pinjam
    tanggalPinjamInput.addEventListener('change', function() {
        const minKembali = new Date(this.value);
        minKembali.setDate(minKembali.getDate() + 1);
        
        const formattedMinKembali = minKembali.toISOString().split('T')[0];
        tanggalKembaliInput.min = formattedMinKembali;
        
        if (tanggalKembaliInput.value && new Date(tanggalKembaliInput.value) < minKembali) {
            tanggalKembaliInput.value = formattedMinKembali;
        }
    });
    
    // Button cek ketersediaan
    btnCheckAvailability.addEventListener('click', function() {
        const sarprasId = sarprasSelect.value;
        const sarprasNama = sarprasSelect.options[sarprasSelect.selectedIndex].text.split(' (')[0];
        const tanggalPinjam = tanggalPinjamInput.value;
        const tanggalKembali = tanggalKembaliInput.value;
        
        if (!sarprasId) {
            alert('Silakan pilih sarpras terlebih dahulu!');
            return;
        }
        
        if (!tanggalPinjam || !tanggalKembali) {
            alert('Silakan pilih tanggal pinjam dan tanggal kembali terlebih dahulu!');
            return;
        }
        
        if (new Date(tanggalPinjam) > new Date(tanggalKembali)) {
            alert('Tanggal kembali harus setelah tanggal pinjam!');
            return;
        }
        
        // Show loading
        availabilityInfo.innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memeriksa ketersediaan...</p>
            </div>
        `;
        
        // Simulasi AJAX request untuk cek ketersediaan
        setTimeout(() => {
            // Cek peminjaman yang ada pada rentang tanggal
            checkSchedule(sarprasId, sarprasNama, tanggalPinjam, tanggalKembali);
            
            // Cek ketersediaan
            const stokTersedia = sarprasSelect.options[sarprasSelect.selectedIndex].dataset.stok;
            const jumlahPinjam = jumlahInput.value;
            
            if (parseInt(jumlahPinjam) > parseInt(stokTersedia)) {
                availabilityInfo.innerHTML = `
                    <div class="text-center py-3">
                        <div class="mb-2"><i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i></div>
                        <h5>Tidak Tersedia</h5>
                        <p class="text-danger">Jumlah yang ingin dipinjam (${jumlahPinjam} unit) melebihi stok yang tersedia (${stokTersedia} unit).</p>
                    </div>
                `;
            } else {
                availabilityInfo.innerHTML = `
                    <div class="text-center py-3">
                        <div class="mb-2"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i></div>
                        <h5>Tersedia</h5>
                        <p class="text-success">Sarpras tersedia untuk dipinjam pada rentang tanggal yang dipilih.</p>
                        <p>Stok tersedia: ${stokTersedia} unit</p>
                        <p>Jumlah yang akan dipinjam: ${jumlahPinjam} unit</p>
                    </div>
                `;
            }
        }, 1000);
    });
    
    // Fungsi untuk cek jadwal
    function checkSchedule(sarprasId, sarprasNama, tanggalPinjam, tanggalKembali) {
        // Simulasi data jadwal
        const jadwalData = [
            {
                kode: 'PJM-12345678',
                sarpras: sarprasNama,
                peminjam: 'Budi Santoso',
                tanggal_pinjam: '2025-05-10',
                tanggal_kembali: '2025-05-15',
                status: 'Disetujui'
            }
        ];
        
        // Show modal
        const jadwalModal = new bootstrap.Modal(document.getElementById('jadwalModal'));
        jadwalModal.show();
        
        // Show loading
        document.getElementById('loadingJadwal').classList.remove('d-none');
        document.getElementById('jadwalList').classList.add('d-none');
        document.getElementById('noJadwal').classList.add('d-none');
        
        // Simulasi loading
        setTimeout(() => {
            document.getElementById('loadingJadwal').classList.add('d-none');
            
            if (jadwalData.length > 0) {
                // Populate jadwal
                const jadwalTable = document.getElementById('jadwalTable');
                jadwalTable.innerHTML = '';
                
                jadwalData.forEach(item => {
                    jadwalTable.innerHTML += `
                        <tr>
                            <td>${item.kode}</td>
                            <td>${item.sarpras}</td>
                            <td>${item.peminjam}</td>
                            <td>${formatDate(item.tanggal_pinjam)}</td>
                            <td>${formatDate(item.tanggal_kembali)}</td>
                            <td><span class="badge bg-info">${item.status}</span></td>
                        </tr>
                    `;
                });
                
                document.getElementById('jadwalList').classList.remove('d-none');
            } else {
                document.getElementById('noJadwal').classList.remove('d-none');
            }
        }, 1500);
    }
    
    // Format tanggal
    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const year = date.getFullYear();
        
        return `${day}/${month}/${year}`;
    }
});
</script>