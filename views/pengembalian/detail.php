<?php
/**
 * Halaman untuk detail pengembalian
 */

// Load model
require_once('models/Pengembalian.php');
require_once('models/Masalah.php');

// Inisialisasi model
$pengembalianModel = new Pengembalian($conn);
$masalahModel = new Masalah($conn);

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

// Dapatkan data masalah jika ada
$masalah = $masalahModel->getMasalahByPeminjaman($pengembalian['peminjaman_id']);

// Generate QR Code untuk pengembalian
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($pengembalian['kode_peminjaman'] . " - Pengembalian");
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Pengembalian</h1>
        <div>
            <?php if (!$pengembalian['verified_by']): ?>
            <a href="<?php echo ROOT_URL; ?>/pengembalian/verify/<?php echo $id; ?>" class="btn btn-success me-2">
                <i class="bi bi-check-circle me-2"></i> Verifikasi Pengembalian
            </a>
            <?php endif; ?>
            
            <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $pengembalian['peminjaman_id']; ?>" class="btn btn-info text-white me-2">
                <i class="bi bi-arrow-up-right-circle me-2"></i> Detail Peminjaman
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
            <!-- Detail Pengembalian -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Informasi Pengembalian</h5>
                    <div>
                        <button class="btn btn-sm btn-light" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Cetak
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <h6 class="border-start border-success ps-2 mb-3">Detail Peminjaman</h6>
                            <table class="table table-hover">
                                <tr>
                                    <th width="35%">Kode Peminjaman</th>
                                    <td width="65%"><strong><?php echo $pengembalian['kode_peminjaman']; ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Nama Sarpras</th>
                                    <td><?php echo $pengembalian['nama_sarpras']; ?> (<?php echo $pengembalian['kode_sarpras']; ?>)</td>
                                </tr>
                                <tr>
                                    <th>Kategori</th>
                                    <td><?php echo $pengembalian['nama_kategori']; ?></td>
                                </tr>
                                <tr>
                                    <th>Jumlah</th>
                                    <td><?php echo $pengembalian['jumlah']; ?> unit</td>
                                </tr>
                                <tr>
                                    <th>Peminjam</th>
                                    <td><?php echo $pengembalian['nama_peminjam']; ?></td>
                                </tr>
                                <tr>
                                    <th>Kontak Peminjam</th>
                                    <td>
                                        <i class="bi bi-envelope me-1"></i> <?php echo $pengembalian['email_peminjam']; ?><br>
                                        <?php if (!empty($pengembalian['telp_peminjam'])): ?>
                                        <i class="bi bi-phone me-1"></i> <?php echo $pengembalian['telp_peminjam']; ?>
                                        <?php else: ?>
                                        <i class="bi bi-phone me-1"></i> <em>Tidak ada</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tanggal Pinjam</th>
                                    <td><?php echo formatDate($pengembalian['tanggal_pinjam']); ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Kembali (Seharusnya)</th>
                                    <td><?php echo formatDate($pengembalian['tanggal_seharusnya']); ?></td>
                                </tr>
                            </table>
                            
                            <h6 class="border-start border-success ps-2 mb-3 mt-4">Detail Pengembalian</h6>
                            <table class="table table-hover">
                                <tr>
                                    <th width="35%">Tanggal Kembali Aktual</th>
                                    <td width="65%">
                                        <?php echo formatDate($pengembalian['tanggal_kembali_aktual']); ?>
                                        <?php 
                                        $terlambat = strtotime($pengembalian['tanggal_kembali_aktual']) > strtotime($pengembalian['tanggal_seharusnya']);
                                        if ($terlambat): 
                                        ?>
                                        <span class="badge rounded-pill bg-danger">Terlambat</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Kondisi Saat Kembali</th>
                                    <td>
                                        <span class="badge rounded-pill 
                                            <?php 
                                                if ($pengembalian['kondisi'] == 'Baik') echo 'bg-success';
                                                else if ($pengembalian['kondisi'] == 'Rusak Ringan') echo 'bg-warning';
                                                else if ($pengembalian['kondisi'] == 'Rusak Berat') echo 'bg-danger';
                                            ?>">
                                            <?php echo $pengembalian['kondisi']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status Verifikasi</th>
                                    <td>
                                        <?php if ($pengembalian['verified_by']): ?>
                                        <span class="badge rounded-pill bg-success">Terverifikasi</span>
                                        <?php else: ?>
                                        <span class="badge rounded-pill bg-warning">Belum Diverifikasi</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($pengembalian['verified_by']): ?>
                                <tr>
                                    <th>Diverifikasi Oleh</th>
                                    <td><?php echo $pengembalian['nama_verifikator']; ?></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Verifikasi</th>
                                    <td><?php echo formatDateTime($pengembalian['verified_at']); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Catatan</th>
                                    <td><?php echo !empty($pengembalian['catatan']) ? nl2br($pengembalian['catatan']) : '<em>Tidak ada catatan</em>'; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-5 text-center">
                            <p class="mb-2">QR Code Pengembalian</p>
                            <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code <?php echo $pengembalian['kode_peminjaman']; ?>" class="img-fluid img-thumbnail mb-2" style="max-width: 150px;">
                            <p><small>Scan untuk verifikasi</small></p>
                            
                            <?php if (!empty($pengembalian['foto_sarpras'])): ?>
                            <p class="mb-2 mt-4">Foto Sarpras</p>
                            <img src="<?php echo ROOT_URL; ?>/uploads/<?php echo $pengembalian['foto_sarpras']; ?>" alt="<?php echo $pengembalian['nama_sarpras']; ?>" class="img-fluid img-thumbnail" style="max-height: 150px;">
                            <?php endif; ?>
                            
                            <?php if ($terlambat): ?>
                            <div class="alert alert-danger mt-4">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Terlambat <?php echo floor((strtotime($pengembalian['tanggal_kembali_aktual']) - strtotime($pengembalian['tanggal_seharusnya'])) / (60 * 60 * 24)); ?> hari</strong> dari tanggal yang ditentukan.
                            </div>
                            <?php else: ?>
                            <div class="alert alert-success mt-4">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <strong>Tepat waktu</strong> sesuai dengan tanggal yang ditentukan.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Data Masalah -->
            <?php if (!empty($masalah)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Laporan Masalah</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Tanggal Laporan</th>
                                    <th width="55%">Deskripsi Masalah</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($masalah as $index => $item): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo formatDateTime($item['created_at']); ?></td>
                                    <td><?php echo substr($item['deskripsi'], 0, 100) . (strlen($item['deskripsi']) > 100 ? '...' : ''); ?></td>
                                    <td>
                                        <span class="badge rounded-pill 
                                            <?php 
                                                if ($item['status'] == 'Dilaporkan') echo 'bg-warning';
                                                else if ($item['status'] == 'Diproses') echo 'bg-info';
                                                else if ($item['status'] == 'Selesai') echo 'bg-success';
                                            ?>">
                                            <?php echo $item['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo ROOT_URL; ?>/masalah/detail/<?php echo $item['id']; ?>" class="btn btn-sm btn-info text-white" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
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
                    <div class="row mt-3">
                        <div class="col-6 text-end border-end">
                            <p class="mb-1 text-muted">Tanggal Seharusnya</p>
                            <h5><?php echo formatDate($pengembalian['tanggal_seharusnya']); ?></h5>
                        </div>
                        <div class="col-6 text-start">
                            <p class="mb-1 text-muted">Tanggal Aktual</p>
                            <h5><?php echo formatDate($pengembalian['tanggal_kembali_aktual']); ?></h5>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="mb-2"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i></div>
                    <h5 class="text-success">Tepat Waktu</h5>
                    <p>Pengembalian dilakukan tepat waktu sesuai dengan tanggal yang ditentukan.</p>
                    <div class="row mt-3">
                        <div class="col-6 text-end border-end">
                            <p class="mb-1 text-muted">Tanggal Seharusnya</p>
                            <h5><?php echo formatDate($pengembalian['tanggal_seharusnya']); ?></h5>
                        </div>
                        <div class="col-6 text-start">
                            <p class="mb-1 text-muted">Tanggal Aktual</p>
                            <h5><?php echo formatDate($pengembalian['tanggal_kembali_aktual']); ?></h5>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Status Kondisi -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-check2-circle me-2"></i> Status Kondisi</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($pengembalian['kondisi'] == 'Baik'): ?>
                    <div class="mb-2"><i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i></div>
                    <h5 class="text-success">Kondisi Baik</h5>
                    <p>Sarpras dikembalikan dalam kondisi baik tanpa kerusakan.</p>
                    <?php elseif ($pengembalian['kondisi'] == 'Rusak Ringan'): ?>
                    <div class="mb-2"><i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i></div>
                    <h5 class="text-warning">Rusak Ringan</h5>
                    <p>Sarpras dikembalikan dengan kerusakan ringan namun masih dapat digunakan.</p>
                    <?php elseif ($pengembalian['kondisi'] == 'Rusak Berat'): ?>
                    <div class="mb-2"><i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i></div>
                    <h5 class="text-danger">Rusak Berat</h5>
                    <p>Sarpras dikembalikan dengan kerusakan berat dan tidak dapat digunakan.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i> Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if (!$pengembalian['verified_by']): ?>
                        <a href="<?php echo ROOT_URL; ?>/pengembalian/verify/<?php echo $id; ?>" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i> Verifikasi Pengembalian
                        </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo ROOT_URL; ?>/peminjaman/detail/<?php echo $pengembalian['peminjaman_id']; ?>" class="btn btn-info text-white">
                            <i class="bi bi-arrow-up-right-circle me-2"></i> Detail Peminjaman
                        </a>
                        
                        <a href="<?php echo ROOT_URL; ?>/sarpras/detail/<?php echo $pengembalian['sarpras_id']; ?>" class="btn btn-primary">
                            <i class="bi bi-box-seam me-2"></i> Detail Sarpras
                        </a>
                        
                        <button type="button" class="btn btn-secondary" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i> Cetak Pengembalian
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Style -->
<style media="print">
    .btn, .sidebar, .navbar, footer, .card-header button {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
        border-bottom: 1px solid #ddd !important;
    }
    
    @page {
        margin: 0.5cm;
    }
    
    body {
        font-size: 12pt;
    }
</style>