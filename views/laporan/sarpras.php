<?php
/**
 * Halaman laporan sarpras
 */

// Load model
require_once('models/Sarpras.php');
require_once('models/Kategori.php');

// Inisialisasi model
$sarprasModel = new Sarpras($conn);
$kategoriModel = new Kategori($conn);

// Parameter filter
$kategori_id = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : 0;
$kondisi = isset($_GET['kondisi']) ? mysqli_real_escape_string($conn, sanitize($_GET['kondisi'])) : '';
$ketersediaan = isset($_GET['ketersediaan']) ? mysqli_real_escape_string($conn, sanitize($_GET['ketersediaan'])) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, sanitize($_GET['search'])) : '';

// Query untuk mendapatkan data sarpras berdasarkan filter
$query = "SELECT s.*, k.nama as nama_kategori 
          FROM sarpras s
          JOIN kategori k ON s.kategori_id = k.id
          WHERE 1=1";

// Tambahkan filter kategori
if ($kategori_id > 0) {
    $query .= " AND s.kategori_id = {$kategori_id}";
}

// Tambahkan filter kondisi
if (!empty($kondisi)) {
    $query .= " AND s.kondisi = '{$kondisi}'";
}

// Tambahkan filter ketersediaan
if ($ketersediaan == 'tersedia') {
    $query .= " AND s.tersedia > 0";
} elseif ($ketersediaan == 'habis') {
    $query .= " AND s.tersedia = 0";
}

// Tambahkan filter pencarian
if (!empty($search)) {
    $query .= " AND (s.nama LIKE '%{$search}%' OR s.kode LIKE '%{$search}%' OR k.nama LIKE '%{$search}%')";
}

// Tambahkan sorting
$query .= " ORDER BY s.nama ASC";

// Eksekusi query
$result = mysqli_query($conn, $query);
$sarprasList = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sarprasList[] = $row;
    }
}

// Mendapatkan daftar kategori untuk dropdown filter
$kategoriDropdown = $kategoriModel->getKategoriDropdown();

// Statistik
$totalSarpras = count($sarprasList);
$totalTersedia = 0;
$totalStok = 0;
$kondisiCount = [
    'Baik' => 0,
    'Rusak Ringan' => 0,
    'Rusak Berat' => 0
];

foreach ($sarprasList as $item) {
    $totalStok += $item['stok'];
    $totalTersedia += $item['tersedia'];
    $kondisiCount[$item['kondisi']]++;
}

// Persentase ketersediaan
$persenTersedia = $totalStok > 0 ? round(($totalTersedia / $totalStok) * 100) : 0;

// Cek apakah ini request untuk cetak/export
$isPrint = isset($_GET['print']) && $_GET['print'] == 'true';
$isExport = isset($_GET['export']) && $_GET['export'] == 'true';

if ($isPrint) {
    // Tampilkan hanya konten untuk dicetak
    include('views/laporan/print/sarpras_print.php');
    exit;
}

if ($isExport) {
    // Logika export ke Excel/PDF di sini
    // ...
    exit;
}

// Data untuk grafik kategori
$queryKategori = "SELECT k.nama, COUNT(s.id) as jumlah 
                 FROM kategori k 
                 LEFT JOIN sarpras s ON k.id = s.kategori_id 
                 GROUP BY k.id 
                 ORDER BY jumlah DESC";
$resultKategori = mysqli_query($conn, $queryKategori);
$kategoriData = [
    'labels' => [],
    'data' => []
];

if (mysqli_num_rows($resultKategori) > 0) {
    while ($row = mysqli_fetch_assoc($resultKategori)) {
        $kategoriData['labels'][] = $row['nama'];
        $kategoriData['data'][] = (int)$row['jumlah'];
    }
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laporan Sarpras</h1>
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
            <form method="GET" action="<?php echo ROOT_URL; ?>/laporan/sarpras" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori_id" name="kategori_id">
                            <option value="0">Semua Kategori</option>
                            <?php foreach ($kategoriDropdown as $kategori): ?>
                            <option value="<?php echo $kategori['id']; ?>" <?php echo ($kategori_id == $kategori['id']) ? 'selected' : ''; ?>>
                                <?php echo $kategori['nama']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="kondisi" class="form-label">Kondisi</label>
                        <select class="form-select" id="kondisi" name="kondisi">
                            <option value="">Semua Kondisi</option>
                            <option value="Baik" <?php echo ($kondisi == 'Baik') ? 'selected' : ''; ?>>Baik</option>
                            <option value="Rusak Ringan" <?php echo ($kondisi == 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                            <option value="Rusak Berat" <?php echo ($kondisi == 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="ketersediaan" class="form-label">Ketersediaan</label>
                        <select class="form-select" id="ketersediaan" name="ketersediaan">
                            <option value="">Semua Status</option>
                            <option value="tersedia" <?php echo ($ketersediaan == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="habis" <?php echo ($ketersediaan == 'habis') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="search" class="form-label">Pencarian</label>
                        <input type="text" class="form-control" id="search" name="search" placeholder="Cari nama, kode, kategori..." value="<?php echo $search; ?>">
                    </div>
                    
                    <div class="col-md-12">
                        <div class="d-flex mt-1">
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
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Statistik Sarpras</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Ketersediaan Sarpras</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Stok:</span>
                                        <span class="fw-bold"><?php echo $totalStok; ?> unit</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Tersedia:</span>
                                        <span class="fw-bold"><?php echo $totalTersedia; ?> unit</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Persentase Ketersediaan:</span>
                                        <span class="fw-bold"><?php echo $persenTersedia; ?>%</span>
                                    </div>
                                    <div class="progress mt-2">
                                        <div class="progress-bar 
                                            <?php 
                                                if ($persenTersedia >= 70) echo 'bg-success';
                                                else if ($persenTersedia >= 30) echo 'bg-warning';
                                                else echo 'bg-danger';
                                            ?>" 
                                            role="progressbar" 
                                            style="width: <?php echo $persenTersedia; ?>%" 
                                            aria-valuenow="<?php echo $persenTersedia; ?>" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                            <?php echo $persenTersedia; ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card shadow-sm border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Kondisi Sarpras</h6>
                                    <div class="row mt-3">
                                        <div class="col-md-4 text-center">
                                            <div class="py-2 px-3 rounded bg-success text-white mb-2">
                                                <h5 class="mb-0"><?php echo $kondisiCount['Baik']; ?></h5>
                                            </div>
                                            <small>Baik</small>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="py-2 px-3 rounded bg-warning text-dark mb-2">
                                                <h5 class="mb-0"><?php echo $kondisiCount['Rusak Ringan']; ?></h5>
                                            </div>
                                            <small>Rusak Ringan</small>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="py-2 px-3 rounded bg-danger text-white mb-2">
                                                <h5 class="mb-0"><?php echo $kondisiCount['Rusak Berat']; ?></h5>
                                            </div>
                                            <small>Rusak Berat</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Distribusi Kategori</h6>
                                    <canvas id="kategoriChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-data me-2"></i> Ringkasan Sarpras</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0 fw-bold"><?php echo $totalSarpras; ?></h2>
                        <span class="badge bg-primary rounded-pill p-2">Total Sarpras</span>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Kondisi Baik:</span>
                            <span class="fw-bold text-success"><?php echo $kondisiCount['Baik']; ?> item</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                style="width: <?php echo $totalSarpras > 0 ? ($kondisiCount['Baik'] / $totalSarpras) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Rusak Ringan:</span>
                            <span class="fw-bold text-warning"><?php echo $kondisiCount['Rusak Ringan']; ?> item</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                style="width: <?php echo $totalSarpras > 0 ? ($kondisiCount['Rusak Ringan'] / $totalSarpras) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Rusak Berat:</span>
                            <span class="fw-bold text-danger"><?php echo $kondisiCount['Rusak Berat']; ?> item</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" role="progressbar" 
                                style="width: <?php echo $totalSarpras > 0 ? ($kondisiCount['Rusak Berat'] / $totalSarpras) * 100 : 0; ?>%"></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Ketersediaan:</span>
                            <span class="fw-bold"><?php echo $totalTersedia; ?> dari <?php echo $totalStok; ?> unit</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar 
                                <?php 
                                    if ($persenTersedia >= 70) echo 'bg-success';
                                    else if ($persenTersedia >= 30) echo 'bg-warning';
                                    else echo 'bg-danger';
                                ?>" 
                                role="progressbar" 
                                style="width: <?php echo $persenTersedia; ?>%"></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid gap-2 mt-4">
                        <a href="<?php echo ROOT_URL; ?>/sarpras" class="btn btn-primary">
                            <i class="bi bi-box-seam me-2"></i> Manajemen Sarpras
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabel Laporan -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i> Data Sarpras</h5>
        </div>
        <div class="card-body table-responsive" id="reportData">
            <?php if (count($sarprasList) > 0): ?>
            <table class="table table-hover table-bordered" id="dataTable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Kode</th>
                        <th width="20%">Nama Sarpras</th>
                        <th width="15%">Kategori</th>
                        <th width="10%">Stok</th>
                        <th width="10%">Tersedia</th>
                        <th width="10%">Kondisi</th>
                        <th width="20%">Lokasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sarprasList as $index => $item): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $item['kode']; ?></td>
                        <td><?php echo $item['nama']; ?></td>
                        <td><?php echo $item['nama_kategori']; ?></td>
                        <td class="text-center"><?php echo $item['stok']; ?></td>
                        <td class="text-center">
                            <?php if ($item['tersedia'] > 0): ?>
                            <span class="badge bg-success"><?php echo $item['tersedia']; ?></span>
                            <?php else: ?>
                            <span class="badge bg-danger">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge rounded-pill 
                                <?php 
                                    if ($item['kondisi'] == 'Baik') echo 'bg-success';
                                    else if ($item['kondisi'] == 'Rusak Ringan') echo 'bg-warning';
                                    else if ($item['kondisi'] == 'Rusak Berat') echo 'bg-danger';
                                ?>">
                                <?php echo $item['kondisi']; ?>
                            </span>
                        </td>
                        <td><?php echo !empty($item['lokasi']) ? $item['lokasi'] : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="text-center py-5">
                <img src="<?php echo ROOT_URL; ?>/assets/img/empty.svg" alt="Data tidak ditemukan" class="img-fluid mb-3" style="max-height: 200px;">
                <h5>Data Sarpras Tidak Ditemukan</h5>
                <p class="text-muted">Tidak ada data sarpras yang sesuai dengan filter yang dipilih.</p>
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
    const dataTable = document.getElementById('dataTable');
    if (dataTable) {
        try {
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
        } catch (e) {
            console.error('Error initializing DataTable:', e);
            // Fallback untuk kasus library DataTable tidak tersedia
            if (typeof DataTable === 'undefined') {
                console.warn('DataTable library tidak tersedia');
            }
        }
    }
    
    // Inisialisasi chart kategori jika elemen canvas ada
    const kategoriCanvas = document.getElementById('kategoriChart');
    if (kategoriCanvas) {
        const kategoriCtx = kategoriCanvas.getContext('2d');
        const labels = <?php echo json_encode($kategoriData['labels']); ?>;
        const data = <?php echo json_encode($kategoriData['data']); ?>;
        
        if (labels && labels.length > 0) {
            const kategoriChart = new Chart(kategoriCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            'rgba(78, 56, 43, 0.7)',
                            'rgba(147, 122, 102, 0.7)',
                            'rgba(84, 40, 39, 0.7)',
                            'rgba(58, 16, 28, 0.7)',
                            'rgba(116, 112, 113, 0.7)',
                            'rgba(211, 206, 200, 0.7)',
                        ],
                        borderColor: [
                            'rgba(78, 56, 43, 1)',
                            'rgba(147, 122, 102, 1)',
                            'rgba(84, 40, 39, 1)',
                            'rgba(58, 16, 28, 1)',
                            'rgba(116, 112, 113, 1)',
                            'rgba(211, 206, 200, 1)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }
    }
});

// Fungsi untuk cetak laporan
function printReport() {
    try {
        // Buat URL untuk versi cetak dengan menambahkan parameter print=true
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        
        let url = '<?php echo ROOT_URL; ?>/laporan/sarpras?print=true';
        
        for (const [key, value] of formData.entries()) {
            if (value) {
                url += '&' + key + '=' + encodeURIComponent(value);
            }
        }
        
        // Tampilkan di iframe dalam modal
        document.getElementById('printFrame').src = url;
        const printModal = new bootstrap.Modal(document.getElementById('printModal'));
        printModal.show();
    } catch (e) {
        console.error('Error saat mempersiapkan cetak:', e);
        alert('Terjadi kesalahan saat mempersiapkan cetak laporan');
    }
}

// Fungsi untuk cetak iframe
function printIframe() {
    try {
        const iframe = document.getElementById('printFrame');
        iframe.contentWindow.print();
    } catch (e) {
        console.error('Error saat mencetak:', e);
        alert('Terjadi kesalahan saat mencetak laporan');
    }
}

// Fungsi untuk export ke Excel
function exportToExcel() {
    try {
        // Periksa apakah library XLSX tersedia
        if (typeof XLSX === 'undefined') {
            alert('Library XLSX tidak tersedia. Pastikan Anda terhubung ke internet atau hubungi administrator.');
            return;
        }
        
        const table = document.getElementById('dataTable');
        const wb = XLSX.utils.table_to_book(table, { sheet: 'Laporan Sarpras' });
        const filename = 'Laporan_Sarpras_' + formatDate(new Date()) + '.xlsx';
        XLSX.writeFile(wb, filename);
    } catch (e) {
        console.error('Error saat export ke Excel:', e);
        alert('Terjadi kesalahan saat export ke Excel');
    }
}

// Fungsi untuk export ke PDF
function exportToPDF() {
    try {
        // Periksa apakah library html2canvas dan jsPDF tersedia
        if (typeof html2canvas === 'undefined' || typeof jsPDF === 'undefined') {
            alert('Library html2canvas atau jsPDF tidak tersedia. Pastikan Anda terhubung ke internet atau hubungi administrator.');
            return;
        }
        
        const table = document.getElementById('dataTable');
        const filename = 'Laporan_Sarpras_' + formatDate(new Date()) + '.pdf';
        
        html2canvas(table).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('l', 'mm', 'a4');
            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
            
            pdf.addImage(imgData, 'PNG', 10, 10, pdfWidth - 20, pdfHeight);
            pdf.save(filename);
        }).catch(error => {
            console.error('Error saat membuat PDF:', error);
            alert('Terjadi kesalahan saat pembuatan PDF');
        });
    } catch (e) {
        console.error('Error saat export ke PDF:', e);
        alert('Terjadi kesalahan saat export ke PDF');
    }
}

// Format tanggal untuk nama file
function formatDate(date) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    return year + month + day;
}
</script>