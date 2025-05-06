<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman - <?php echo $app_name; ?></title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #000;
            background: #fff;
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .report-header img {
            max-height: 70px;
        }
        
        .report-header h2, .report-header h3 {
            margin: 5px 0;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
            text-align: center;
        }
        
        .report-info {
            margin-bottom: 15px;
        }
        
        .report-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .report-info table td {
            padding: 3px 5px;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .report-table th, .report-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            font-size: 12px;
        }
        
        .report-table th {
            background-color: #f2f2f2;
        }
        
        .report-footer {
            margin-top: 30px;
            text-align: right;
        }
        
        .report-footer p {
            margin: 5px 0;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            color: #fff;
        }
        
        .bg-warning {
            background-color: #ffc107;
            color: #000;
        }
        
        .bg-info {
            background-color: #17a2b8;
            color: #fff;
        }
        
        .bg-primary {
            background-color: #4E382B;
            color: #fff;
        }
        
        .bg-success {
            background-color: #28a745;
            color: #fff;
        }
        
        .bg-danger {
            background-color: #dc3545;
            color: #fff;
        }
        
        .report-summary {
            margin: 20px 0;
        }
        
        .report-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .report-summary th, .report-summary td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 12px;
        }
        
        .report-summary th {
            background-color: #f2f2f2;
        }
        
        @media print {
            body {
                padding: 0;
                font-size: 12px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="report-header">
        <img src="<?php echo ROOT_URL; ?>/assets/img/logo.png" alt="Logo SMKN 1 Cimahi">
        <h2>SMKN 1 CIMAHI</h2>
        <h3>SISTEM PEMINJAMAN SARANA PRASARANA</h3>
        <p>Jl. Mahar Martanegara No.48, Utama, Kec. Cimahi Sel., Kota Cimahi, Jawa Barat 40521</p>
    </div>
    
    <div class="report-title">
        LAPORAN PEMINJAMAN SARANA PRASARANA
    </div>
    
    <div class="report-info">
        <table>
            <tr>
                <td width="15%">Periode</td>
                <td width="3%">:</td>
                <td><?php echo formatDate($tanggal_mulai); ?> s/d <?php echo formatDate($tanggal_selesai); ?></td>
            </tr>
            <tr>
                <td>Status</td>
                <td>:</td>
                <td><?php echo empty($status) ? 'Semua Status' : $status; ?></td>
            </tr>
            <tr>
                <td>Sarpras</td>
                <td>:</td>
                <td>
                    <?php 
                    if ($sarpras_id > 0) {
                        foreach ($sarprasDropdown as $sarpras) {
                            if ($sarpras['id'] == $sarpras_id) {
                                echo $sarpras['nama'] . ' (' . $sarpras['kode'] . ')';
                                break;
                            }
                        }
                    } else {
                        echo 'Semua Sarpras';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Peminjam</td>
                <td>:</td>
                <td>
                    <?php 
                    if ($user_id > 0) {
                        foreach ($userDropdown as $user) {
                            if ($user['id'] == $user_id) {
                                echo $user['nama_lengkap'] . ' (' . $user['nis'] . ')';
                                break;
                            }
                        }
                    } else {
                        echo 'Semua Peminjam';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Tanggal Cetak</td>
                <td>:</td>
                <td><?php echo formatDate(date('Y-m-d')); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="report-summary">
        <table>
            <tr>
                <th>Total Peminjaman</th>
                <th>Menunggu</th>
                <th>Disetujui</th>
                <th>Dipinjam</th>
                <th>Dikembalikan</th>
                <th>Ditolak</th>
                <th>Terlambat</th>
            </tr>
            <tr>
                <td><?php echo $totalPeminjaman; ?></td>
                <td><?php echo $statusCounts['Menunggu']; ?></td>
                <td><?php echo $statusCounts['Disetujui']; ?></td>
                <td><?php echo $statusCounts['Dipinjam']; ?></td>
                <td><?php echo $statusCounts['Dikembalikan']; ?></td>
                <td><?php echo $statusCounts['Ditolak']; ?></td>
                <td><?php echo $statusCounts['Terlambat']; ?></td>
            </tr>
        </table>
    </div>
    
    <table class="report-table">
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
            <?php if (count($peminjaman) > 0): ?>
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
                        <span class="badge 
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
            <?php else: ?>
                <tr>
                    <td colspan="9" align="center">Tidak ada data peminjaman yang sesuai dengan filter yang dipilih.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="report-footer">
        <p>Cimahi, <?php echo getTanggalIndonesia(date('Y-m-d')); ?></p>
        <p>Mengetahui,</p>
        <br><br><br>
        <p>____________________________</p>
        <p>Kepala Sarana Prasarana</p>
    </div>
    
    <script>
    // Auto print saat halaman dimuat
    window.onload = function() {
        // Uncomment line di bawah ini untuk autoprint
        // window.print();
    };
    </script>
</body>
</html>