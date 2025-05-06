<?php
/**
 * Model untuk manajemen jadwal peminjaman
 * (Sebenarnya menggunakan tabel peminjaman, tapi perspektif berbeda)
 */
class Jadwal {
    private $conn;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Mendapatkan jadwal peminjaman berdasarkan tanggal
     * @param string $start Tanggal mulai (YYYY-MM-DD)
     * @param string $end Tanggal selesai (YYYY-MM-DD)
     * @param int $sarprasId ID sarpras (opsional)
     * @return array Data jadwal
     */
    public function getJadwalByDate($start, $end, $sarprasId = null) {
        $data = [];
        
        $sql = "SELECT p.*, s.nama as nama_sarpras, s.kode as kode_sarpras, 
                u.nama_lengkap as nama_peminjam
                FROM peminjaman p
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN users u ON p.user_id = u.id
                WHERE p.status IN ('Disetujui', 'Dipinjam') 
                AND (
                    (p.tanggal_pinjam BETWEEN '{$start}' AND '{$end}') 
                    OR 
                    (p.tanggal_kembali BETWEEN '{$start}' AND '{$end}')
                    OR
                    (p.tanggal_pinjam <= '{$start}' AND p.tanggal_kembali >= '{$end}')
                )";
        
        if ($sarprasId !== null) {
            $sql .= " AND p.sarpras_id = {$sarprasId}";
        }
        
        $sql .= " ORDER BY p.tanggal_pinjam ASC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Format data untuk frontend
                $data[] = [
                    'id' => $row['id'],
                    'title' => $row['nama_sarpras'] . ' - ' . $row['nama_peminjam'],
                    'start' => $row['tanggal_pinjam'],
                    'end' => $row['tanggal_kembali'],
                    'kode' => $row['kode_peminjaman'],
                    'status' => $row['status'],
                    'jumlah' => $row['jumlah'],
                    'peminjam' => $row['nama_peminjam'],
                    'sarpras_id' => $row['sarpras_id'],
                    'tujuan' => $row['tujuan_peminjaman']
                ];
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan jadwal peminjaman berdasarkan sarpras
     * @param int $sarprasId ID sarpras
     * @param string $start Tanggal mulai (YYYY-MM-DD, opsional)
     * @param string $end Tanggal selesai (YYYY-MM-DD, opsional)
     * @return array Data jadwal
     */
    public function getJadwalBySarpras($sarprasId, $start = null, $end = null) {
        $data = [];
        
        $sql = "SELECT p.*, u.nama_lengkap as nama_peminjam
                FROM peminjaman p
                JOIN users u ON p.user_id = u.id
                WHERE p.sarpras_id = {$sarprasId}
                AND p.status IN ('Disetujui', 'Dipinjam')";
        
        if ($start !== null && $end !== null) {
            $sql .= " AND (
                        (p.tanggal_pinjam BETWEEN '{$start}' AND '{$end}') 
                        OR 
                        (p.tanggal_kembali BETWEEN '{$start}' AND '{$end}')
                        OR
                        (p.tanggal_pinjam <= '{$start}' AND p.tanggal_kembali >= '{$end}')
                    )";
        }
        
        $sql .= " ORDER BY p.tanggal_pinjam ASC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan jadwal peminjaman berdasarkan user
     * @param int $userId ID user
     * @param string $start Tanggal mulai (YYYY-MM-DD, opsional)
     * @param string $end Tanggal selesai (YYYY-MM-DD, opsional)
     * @return array Data jadwal
     */
    public function getJadwalByUser($userId, $start = null, $end = null) {
        $data = [];
        
        $sql = "SELECT p.*, s.nama as nama_sarpras, s.kode as kode_sarpras
                FROM peminjaman p
                JOIN sarpras s ON p.sarpras_id = s.id
                WHERE p.user_id = {$userId}
                AND p.status IN ('Disetujui', 'Dipinjam')";
        
        if ($start !== null && $end !== null) {
            $sql .= " AND (
                        (p.tanggal_pinjam BETWEEN '{$start}' AND '{$end}') 
                        OR 
                        (p.tanggal_kembali BETWEEN '{$start}' AND '{$end}')
                        OR
                        (p.tanggal_pinjam <= '{$start}' AND p.tanggal_kembali >= '{$end}')
                    )";
        }
        
        $sql .= " ORDER BY p.tanggal_pinjam ASC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan ketersediaan sarpras pada rentang tanggal tertentu
     * @param int $sarprasId ID sarpras
     * @param string $start Tanggal mulai (YYYY-MM-DD)
     * @param string $end Tanggal selesai (YYYY-MM-DD)
     * @param int $excludePeminjaman ID peminjaman yang dikecualikan (opsional)
     * @return array|int Jumlah tersedia atau detail peminjaman jika sudah penuh
     */
    public function checkAvailability($sarprasId, $start, $end, $excludePeminjaman = null) {
        // Dapatkan total stok sarpras
        $sql = "SELECT stok FROM sarpras WHERE id = {$sarprasId}";
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) === 0) {
            return ['status' => false, 'message' => 'Sarpras tidak ditemukan'];
        }
        
        $row = mysqli_fetch_assoc($result);
        $totalStok = (int)$row['stok'];
        
        // Hitung jumlah yang sedang dipinjam pada rentang waktu tersebut
        $sql = "SELECT SUM(jumlah) as total_dipinjam 
                FROM peminjaman 
                WHERE sarpras_id = {$sarprasId}
                AND status IN ('Disetujui', 'Dipinjam')
                AND (
                    (tanggal_pinjam BETWEEN '{$start}' AND '{$end}') 
                    OR 
                    (tanggal_kembali BETWEEN '{$start}' AND '{$end}')
                    OR
                    (tanggal_pinjam <= '{$start}' AND tanggal_kembali >= '{$end}')
                )";
        
        if ($excludePeminjaman !== null) {
            $sql .= " AND id != {$excludePeminjaman}";
        }
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $totalDipinjam = (int)$row['total_dipinjam'];
        
        $tersedia = $totalStok - $totalDipinjam;
        
        if ($tersedia <= 0) {
            // Dapatkan detail peminjaman yang sudah ada
            $sql = "SELECT p.*, s.nama as nama_sarpras, u.nama_lengkap as nama_peminjam
                    FROM peminjaman p
                    JOIN sarpras s ON p.sarpras_id = s.id
                    JOIN users u ON p.user_id = u.id
                    WHERE p.sarpras_id = {$sarprasId}
                    AND p.status IN ('Disetujui', 'Dipinjam')
                    AND (
                        (p.tanggal_pinjam BETWEEN '{$start}' AND '{$end}') 
                        OR 
                        (p.tanggal_kembali BETWEEN '{$start}' AND '{$end}')
                        OR
                        (p.tanggal_pinjam <= '{$start}' AND p.tanggal_kembali >= '{$end}')
                    )";
            
            if ($excludePeminjaman !== null) {
                $sql .= " AND p.id != {$excludePeminjaman}";
            }
            
            $sql .= " ORDER BY p.tanggal_pinjam ASC";
            
            $result = mysqli_query($this->conn, $sql);
            $peminjaman = [];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $peminjaman[] = $row;
            }
            
            return [
                'status' => false, 
                'message' => 'Semua stok sudah dipinjam pada rentang waktu tersebut',
                'peminjaman' => $peminjaman
            ];
        }
        
        return ['status' => true, 'tersedia' => $tersedia];
    }
    
    /**
     * Mendapatkan jadwal hari ini
     * @return array Data jadwal
     */
    public function getJadwalHariIni() {
        $today = date('Y-m-d');
        
        $data = [];
        
        $sql = "SELECT p.*, s.nama as nama_sarpras, s.kode as kode_sarpras, 
                u.nama_lengkap as nama_peminjam
                FROM peminjaman p
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN users u ON p.user_id = u.id
                WHERE p.status IN ('Disetujui', 'Dipinjam')
                AND (
                    ('{$today}' BETWEEN p.tanggal_pinjam AND p.tanggal_kembali)
                    OR
                    (p.tanggal_pinjam = '{$today}')
                    OR
                    (p.tanggal_kembali = '{$today}')
                )
                ORDER BY p.tanggal_pinjam ASC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan jadwal mendatang
     * @param int $days Jumlah hari ke depan
     * @return array Data jadwal
     */
    public function getJadwalMendatang($days = 7) {
        $today = date('Y-m-d');
        $future = date('Y-m-d', strtotime("+{$days} days"));
        
        $data = [];
        
        $sql = "SELECT p.*, s.nama as nama_sarpras, s.kode as kode_sarpras, 
                u.nama_lengkap as nama_peminjam
                FROM peminjaman p
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN users u ON p.user_id = u.id
                WHERE p.status IN ('Disetujui', 'Dipinjam')
                AND (
                    (p.tanggal_pinjam BETWEEN '{$today}' AND '{$future}')
                    OR
                    (p.tanggal_kembali BETWEEN '{$today}' AND '{$future}')
                    OR
                    (p.tanggal_pinjam <= '{$today}' AND p.tanggal_kembali >= '{$future}')
                )
                ORDER BY p.tanggal_pinjam ASC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Dapatkan riwayat jadwal
     * @param int $days Jumlah hari ke belakang
     * @return array Data jadwal
     */
    public function getJadwalRiwayat($days = 7) {
        $today = date('Y-m-d');
        $past = date('Y-m-d', strtotime("-{$days} days"));
        
        $data = [];
        
        $sql = "SELECT p.*, s.nama as nama_sarpras, s.kode as kode_sarpras, 
                u.nama_lengkap as nama_peminjam
                FROM peminjaman p
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN users u ON p.user_id = u.id
                WHERE p.status IN ('Disetujui', 'Dipinjam', 'Dikembalikan')
                AND (
                    (p.tanggal_pinjam BETWEEN '{$past}' AND '{$today}')
                    OR
                    (p.tanggal_kembali BETWEEN '{$past}' AND '{$today}')
                    OR
                    (p.tanggal_pinjam <= '{$past}' AND p.tanggal_kembali >= '{$today}')
                )
                ORDER BY p.tanggal_pinjam DESC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
}
?>