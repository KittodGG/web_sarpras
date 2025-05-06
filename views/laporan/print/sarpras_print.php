<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sarpras - <?php echo $app_name; ?></title>
    
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
        
        .bg-success {
            background-color: #28a745;
            color: #fff;
        }
        
        .bg-warning {
            background-color: #ffc107;
            color: #000;
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
        LAPORAN SARANA DAN PRASARANA
    </div>
    
    <div class="report-info">
        <table>
            <tr>
                <td width="15%">Kategori</td>
                <td width="3%">:</td>
                <td>
                    <?php 
                    if ($kategori_id > 0) {
                        foreach ($kategoriDropdown as $kategori) {
                            if ($kategori['id'] == $kategori_id) {
                                echo $kategori['nama'];
                                break;
                            }
                        }
                    } else {
                        echo 'Semua Kategori';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Kondisi</td>
                <td>:</td>
                <td><?php echo empty($kondisi) ? 'Semua Kondisi' : $kondisi; ?></td>
            </tr>
            <tr>
                <td>Ketersediaan</td>
                <td>:</td>
                <td>
                    <?php 
                    if ($ketersediaan == 'tersedia') {
                        echo 'Tersedia';
                    } elseif ($ketersediaan == 'habis') {
                        echo 'Tidak Tersedia';
                    } else {
                        echo 'Semua Status';
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
                <th>Total Sarpras</th>
                <th>Total Stok</th>
                <th>Total Tersedia</th>
                <th>Kondisi Baik</th>
                <th>Rusak Ringan</th>
                <th>Rusak Berat</th>
            </tr>
            <tr>
                <td><?php echo $totalSarpras; ?></td>
                <td><?php echo $totalStok; ?></td>
                <td><?php echo $totalTersedia; ?></td>
                <td><?php echo $kondisiCount['Baik']; ?></td>
                <td><?php echo $kondisiCount['Rusak Ringan']; ?></td>
                <td><?php echo $kondisiCount['Rusak Berat']; ?></td>
            </tr>
        </table>
    </div>
    
    <table class="report-table">
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
            <?php if (count($sarprasList) > 0): ?>
                <?php foreach ($sarprasList as $index => $item): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo $item['kode']; ?></td>
                    <td><?php echo $item['nama']; ?></td>
                    <td><?php echo $item['nama_kategori']; ?></td>
                    <td align="center"><?php echo $item['stok']; ?></td>
                    <td align="center"><?php echo $item['tersedia']; ?></td>
                    <td align="center">
                        <span class="badge 
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
            <?php else: ?>
                <tr>
                    <td colspan="8" align="center">Tidak ada data sarpras yang sesuai dengan filter yang dipilih.</td>
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