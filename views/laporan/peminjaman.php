<?php
/**
 * Halaman laporan peminjaman
 */

// Load model
require_once('models/Peminjaman.php');
require_once('models/Sarpras.php');
require_once('models/User.php');

// Inisialisasi model
$peminjamanModel = new Peminjaman($conn);
$sarprasModel = new Sarpras($conn);
$userModel = new User($conn);

// Parameter filter
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? sanitize($_GET['tanggal_mulai']) : date('Y-m-01'); // Default: awal bulan ini
$tanggal_selesai = isset($_GET['tanggal_selesai']) ? sanitize($_GET['tanggal_selesai']) : date('Y-m-d'); // Default: hari ini
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$sarpras_id = isset($_GET['sarpras_id']) ? (int)$_GET['sarpras_id'] : 0;
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Query untuk mendapatkan data peminjaman berdasarkan filter
$query = "SELECT p.*, s.nama as nama_sarpras, s.kode as kode_sarpras, u.nama_lengkap as nama_peminjam, 
          a.nama_lengkap as nama_approval
          FROM peminjaman p
          JOIN sarpras s ON p.sarpras_id = s.id
          JOIN users u ON p.user_id = u.id
          LEFT JOIN users a ON p.approved_by = a.id
          WHERE 1=1";

// Tambahkan filter tanggal
if (!empty($tanggal_mulai) && !empty($tanggal_selesai)) {
    $query .= " AND ((p.tanggal_pinjam BETWEEN '{$tanggal_mulai}' AND '{$tanggal_selesai}') 
               OR (p.tanggal_kembali BETWEEN '{$tanggal_mulai}' AND '{$tanggal_selesai}')
               OR (p.tanggal_pinjam <= '{$tanggal_mulai}' AND p.tanggal_kembali >= '{$tanggal_selesai}'))";
}

// Tambahkan filter status
if (!empty($status)) {
    $query .= " AND p.status = '{$status}'";
}

// Tambahkan filter sarpras
if ($sarpras_id > 0) {
    $query .= " AND p.sarpras_id = {$sarpras_id}";
}

// Tambahkan filter user
if ($user_id > 0) {
    $query .= " AND p.user_id = {$user_id}";
}

// Tambahkan sorting
$query .= " ORDER BY p.created_at DESC";

// Eksekusi query
$result = mysqli_query($conn, $query);
$peminjaman = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $peminjaman[] = $row;
    }
}

// Mendapatkan daftar sarpras untuk dropdown filter
$sarprasDropdown = $sarprasModel->getAvailableSarpras();

// Mendapatkan daftar user untuk dropdown filter
$userQuery = "SELECT u.id, u.nama_lengkap, s.nis
              FROM users u
              JOIN siswa s ON u.id = s.user_id
              WHERE u.role = 'user'
              ORDER BY u.nama_lengkap ASC";
$userResult = mysqli_query($conn, $userQuery);
$userDropdown = [];

if (mysqli_num_rows($userResult) > 0) {
    while ($row = mysqli_fetch_assoc($userResult)) {
        $userDropdown[] = $row;
    }
}

// Statistik laporan
$totalPeminjaman = count($peminjaman);

// Status counts
$statusCounts = [
    'Menunggu' => 0,
    'Disetujui' => 0,
    'Ditolak' => 0,
    'Dipinjam' => 0,
    'Dikembalikan' => 0,
    'Terlambat' => 0
];

foreach ($peminjaman as $item) {
    $statusCounts[$item['status']]++;
}

// Cek apakah ini request untuk cetak/export
$isPrint = isset($_GET['print']) && $_GET['print'] == 'true';
$isExport = isset($_GET['export']) && $_GET['export'] == 'true';

if ($isPrint) {
    // Tampilkan hanya konten untuk dicetak
    include('views/laporan/print/peminjaman_print.php');
    exit;
}

if ($isExport) {
    // Logika export ke Excel/PDF di sini
    // ...
    exit;
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan Peminjaman</h1>
        <a href="<?php echo ROOT_URL; ?>/laporan" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Kembali
        </a>
    </div>
    
    <!-- Alert message -->
    <?php showAlert(); ?>
    
    <!-- Filter Laporan -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i> Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo ROOT_URL; ?>/laporan/peminjaman" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo $tanggal_mulai; ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo $tanggal_selesai; ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status Peminjaman</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Semua Status</option>
                            <option value="Menunggu" <?php echo ($status == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                            <option value="Disetujui" <?php echo ($status == 'Disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                            <option value="Ditolak" <?php echo ($status == 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                            <option value="Dipinjam" <?php echo ($status == 'Dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                            <option value="Dikembalikan" <?php echo ($status == 'Dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                            <option value="Terlambat" <?php echo ($status == 'Terlambat') ? 'selected' : ''; ?>>Terlambat</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="sarpras_id" class="form-label">Sarpras</label>
                        <select class="form-select" id="sarpras_id" name="sarpras_id">
                            <option value="0">Semua Sarpras</option>
                            <?php foreach ($sarprasDropdown as $sarpras): ?>
                            <option value="<?php echo $sarpras['id']; ?>" <?php echo ($sarpras_id == $sarpras['id']) ? 'selected' : ''; ?>>
                                <?php echo $sarpras['nama']; ?> (<?php echo $sarpras['kode']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">Peminjam</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="0">Semua Peminjam</option>
                            <?php foreach ($userDropdown as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo ($user_id == $user['id']) ? 'selected' : ''; ?>>
                                <?php echo $user['nama_lengkap']; ?> (<?php echo $user['nis']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-9">
                        <div class="d-flex mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search me-2"></i> Tampilkan Laporan
                            </button>
                            <button type="button" class="btn btn-info text-white me-2" onclick="printReport()">
                                <i class="bi bi-printer me-2"></i> Cetak Laporan
                            </button>
                            <button type="button" class="btn btn-success me-2" onclick="exportToExcel()">
                                <i class="bi bi-file-excel me-2"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-danger" onclick="exportToPDF()">
                                <i class="bi bi-file-pdf me-2"></i> Export PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Statistik Laporan -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Statistik Laporan</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-light">
                        <div class="card-body text-center">
                            <h2 class="mb-0 fw-bold"><?php echo $totalPeminjaman; ?></h2>
                            <p class="mb-0">Total Peminjaman</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <div class="card shadow-sm border-0 bg-light h-100">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 text-center">
                                    <div class="py-2 px-3 rounded bg-warning text-dark mb-2">
                                        <h5 class="mb-0"><?php echo $statusCounts['Menunggu']; ?></h5>
                                    </div>
                                    <small>Menunggu</small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="py-2 px-3 rounded bg-info text-white mb-2">
                                        <h5 class="mb-0"><?php echo $statusCounts['Disetujui']; ?></h5>
                                    </div>
                                    <small>Disetujui</small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="py-2 px-3 rounded bg-primary text-white mb-2">
                                        <h5 class="mb-0"><?php echo $statusCounts['Dipinjam']; ?></h5>
                                    </div>
                                    <small>Dipinjam</small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="py-2 px-3 rounded bg-success text-white mb-2">
                                        <h5 class="mb-0"><?php echo $statusCounts['Dikembalikan']; ?></h5>
                                    </div>
                                    <small>Dikembalikan</small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="py-2 px-3 rounded bg-danger text-white mb-2">
                                        <h5 class="mb-0"><?php echo $statusCounts['Ditolak']; ?></h5>
                                    </div>
                                    <small>Ditolak</small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="py-2 px-3 rounded bg-danger text-white mb-2">
                                        <h5 class="mb-0"><?php echo $statusCounts['Terlambat']; ?></h5>
                                    </div>
                                    <small>Terlambat</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabel Laporan -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i> Data Peminjaman</h5>
        </div>
        <div class="card-body table-responsive" id="reportData">
            <?php if (count($peminjaman) > 0): ?>
            <table class="table table-hover table-bordered" id="dataTable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="8%">Kode</th>
                        <th width="17%">Sarpras</th>
                        <th width="15%">Peminjam</th>
                        <th width="10%">Tgl Pinjam</th>
                        <th width="10%">Tgl Kembali</th>
                        <th width="15%">Tujuan</th>
                        <th width="10%">Status</th>
                        <th width="10%">Approval</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peminjaman as $index => $item): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $item['kode_peminjaman']; ?></td>
                        <td><?php echo $item['nama_sarpras']; ?> (<?php echo $item['kode_sarpras']; ?>)</td>
                        <td><?php echo $item['nama_peminjam']; ?></td>
                        <td><?php echo formatDate($item['tanggal_pinjam']); ?></td>
                        <td><?php echo formatDate($item['tanggal_kembali']); ?></td>
                        <td><?php echo substr($item['tujuan_peminjaman'], 0, 50) . (strlen($item['tujuan_peminjaman']) > 50 ? '...' : ''); ?></td>
                        <td>
                            <span class="badge rounded-pill 
                                <?php 
                                    if ($item['status'] == 'Menunggu') echo 'bg-warning';
                                    else if ($item['status'] == 'Disetujui') echo 'bg-info';
                                    else if ($item['status'] == 'Dipinjam') echo 'bg-primary';
                                    else if ($item['status'] == 'Dikembalikan') echo 'bg-success';
                                    else if ($item['status'] == 'Ditolak') echo 'bg-danger';
                                    else if ($item['status'] == 'Terlambat') echo 'bg-danger';
                                ?>">
                                <?php echo $item['status']; ?>
                            </span>
                        </td>
                        <td><?php echo !empty($item['nama_approval']) ? $item['nama_approval'] : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="text-center py-5">
                <img src="<?php echo ROOT_URL; ?>/assets/img/empty.svg" alt="Data tidak ditemukan" class="img-fluid mb-3" style="max-height: 200px;">
                <h5>Data Peminjaman Tidak Ditemukan</h5>
                <p class="text-muted">Tidak ada data peminjaman yang sesuai dengan filter yang dipilih.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Print Preview -->
<div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="printModalLabel">Preview Cetak Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="printFrame" style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printIframe()">
                    <i class="bi bi-printer me-2"></i> Cetak
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Inisialisasi DataTable
document.addEventListener('DOMContentLoaded', function() {
    new DataTable('#dataTable', {
        responsive: true,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            zeroRecords: "Tidak ada data yang cocok",
            emptyTable: "Tidak ada data tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });
    
    // Inisialisasi validasi tanggal
    document.getElementById('tanggal_mulai').addEventListener('change', function() {
        const tanggalMulai = new Date(this.value);
        const tanggalSelesai = new Date(document.getElementById('tanggal_selesai').value);
        
        if (tanggalMulai > tanggalSelesai) {
            document.getElementById('tanggal_selesai').value = this.value;
        }
    });
    
    document.getElementById('tanggal_selesai').addEventListener('change', function() {
        const tanggalMulai = new Date(document.getElementById('tanggal_mulai').value);
        const tanggalSelesai = new Date(this.value);
        
        if (tanggalSelesai < tanggalMulai) {
            document.getElementById('tanggal_mulai').value = this.value;
        }
    });
});

// Fungsi untuk cetak laporan
function printReport() {
    // Buat URL untuk versi cetak dengan menambahkan parameter print=true
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    let url = '<?php echo ROOT_URL; ?>/laporan/peminjaman?print=true';
    
    for (const [key, value] of formData.entries()) {
        if (value) {
            url += '&' + key + '=' + encodeURIComponent(value);
        }
    }
    
    // Tampilkan di iframe dalam modal
    document.getElementById('printFrame').src = url;
    const printModal = new bootstrap.Modal(document.getElementById('printModal'));
    printModal.show();
}

// Fungsi untuk cetak iframe
function printIframe() {
    const iframe = document.getElementById('printFrame');
    iframe.contentWindow.print();
}

// Fungsi untuk export ke Excel
function exportToExcel() {
    const table = document.getElementById('dataTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: 'Laporan Peminjaman' });
    const filename = 'Laporan_Peminjaman_' + formatDate(new Date()) + '.xlsx';
    XLSX.writeFile(wb, filename);
}

// Fungsi untuk export ke PDF
function exportToPDF() {
    const table = document.getElementById('dataTable');
    const filename = 'Laporan_Peminjaman_' + formatDate(new Date()) + '.pdf';
    
    html2canvas(table).then(canvas => {
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF('l', 'mm', 'a4');
        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
        
        pdf.addImage(imgData, 'PNG', 10, 10, pdfWidth - 20, pdfHeight);
        pdf.save(filename);
    });
}

// Format tanggal untuk nama file
function formatDate(date) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    return year + month + day;
}
</script>