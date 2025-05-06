<?php
/**
 * Model untuk manajemen data peminjaman
 */
class Peminjaman {
    private $conn;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Mendapatkan semua data peminjaman
     * @param int $offset Offset untuk pagination
     * @param int $limit Limit untuk pagination
     * @param string $search Kata kunci pencarian
     * @param string $status Filter status
     * @return array Data peminjaman
     */
    public function getAllPeminjaman($offset = 0, $limit = 10, $search = '', $status = '') {
        $data = [];
        
        $sql = "SELECT p.*, s.nama as nama_sarpras, u.nama_lengkap as nama_peminjam, 
                a.nama_lengkap as nama_approval 
                FROM peminjaman p
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN users u ON p.user_id = u.id
                LEFT JOIN users a ON p.approved_by = a.id";
        
        // Tambahkan kondisi pencarian dan filter
        $where = [];
        
        if (!empty($search)) {
            $where[] = "(s.nama LIKE '%{$search}%' 
                        OR u.nama_lengkap LIKE '%{$search}%' 
                        OR p.kode_peminjaman LIKE '%{$search}%')";
        }
        
        if (!empty($status)) {
            $where[] = "p.status = '{$status}'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT {$offset}, {$limit}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan total data peminjaman
     * @param string $search Kata kunci pencarian
     * @param string $status Filter status
     * @return int Total data
     */
    public function getTotalPeminjaman($search = '', $status = '') {
        $sql = "SELECT COUNT(*) as total 
                FROM peminjaman p
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN users u ON p.user_id = u.id";
        
        // Tambahkan kondisi pencarian dan filter
        $where = [];
        
        if (!empty($search)) {
            $where[] = "(s.nama LIKE '%{$search}%' 
                        OR u.nama_lengkap LIKE '%{$search}%' 
                        OR p.kode_peminjaman LIKE '%{$search}%')";
        }
        
        if (!empty($status)) {
            $where[] = "p.status = '{$status}'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        return $row['total'];
    }
    
    /**
     * Mendapatkan data peminjaman berdasarkan ID
     * @param int $id ID peminjaman
     * @return array|bool Data peminjaman atau false jika tidak ditemukan
     */
    public function getPeminjamanById($id) {
        $sql = "SELECT p.*, s.nama as nama_sarpras, s.kode as kode_sarpras, 
                s.kategori_id, k.nama as nama_kategori, 
                u.nama_lengkap as nama_peminjam, u.email as email_peminjam, 
                u.no_telp as telp_peminjam,
                a.nama_lengkap as nama_approval
                FROM peminjaman p
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN kategori k ON s.kategori_id = k.id
                JOIN users u ON p.user_id = u.id
                LEFT JOIN users a ON p.approved_by = a.id
                WHERE p.id = {$id}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return false;
        }
    }
    
    /**
     * Mendapatkan data peminjaman berdasarkan kode
     * @param string $kode Kode peminjaman
     * @return array|bool Data peminjaman atau false jika tidak ditemukan
     */
    public function getPeminjamanByKode($kode) {
        $kode = $this->conn->real_escape_string($kode);
        
        $sql = "SELECT p.*, s.nama as nama_sarpras, s.kode as kode_sarpras, 
                s.kategori_id, k.nama as nama_kategori, 
                u.nama_lengkap as nama_peminjam, u.email as email_peminjam, 
                u.no_telp as telp_peminjam,
                a.nama_lengkap as nama_approval
                FROM peminjaman p
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN kategori k ON s.kategori_id = k.id
                JOIN users u ON p.user_id = u.id
                LEFT JOIN users a ON p.approved_by = a.id
                WHERE p.kode_peminjaman = '{$kode}'";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return false;
        }
    }
    
    /**
     * Menambahkan data peminjaman baru
     * @param array $data Data peminjaman
     * @return bool|int Status penambahan data atau ID peminjaman
     */
    public function addPeminjaman($data) {
        $kode_peminjaman = $this->conn->real_escape_string($data['kode_peminjaman']);
        $user_id = (int)$data['user_id'];
        $sarpras_id = (int)$data['sarpras_id'];
        $jumlah = (int)$data['jumlah'];
        $tanggal_pinjam = $this->conn->real_escape_string($data['tanggal_pinjam']);
        $tanggal_kembali = $this->conn->real_escape_string($data['tanggal_kembali']);
        $tujuan_peminjaman = $this->conn->real_escape_string($data['tujuan_peminjaman']);
        $status = $this->conn->real_escape_string($data['status']);
        $catatan = isset($data['catatan']) ? $this->conn->real_escape_string($data['catatan']) : '';
        
        $sql = "INSERT INTO peminjaman (kode_peminjaman, user_id, sarpras_id, jumlah, tanggal_pinjam, tanggal_kembali, tujuan_peminjaman, status, catatan) 
                VALUES ('{$kode_peminjaman}', {$user_id}, {$sarpras_id}, {$jumlah}, '{$tanggal_pinjam}', '{$tanggal_kembali}', '{$tujuan_peminjaman}', '{$status}', '{$catatan}')";
        
        if (mysqli_query($this->conn, $sql)) {
            return mysqli_insert_id($this->conn);
        } else {
            return false;
        }
    }
    
    /**
     * Mengupdate data peminjaman
     * @param int $id ID peminjaman
     * @param array $data Data peminjaman
     * @return bool Status update data
     */
    public function updatePeminjaman($id, $data) {
        $id = (int)$id;
        
        $fields = [];
        
        // Persiapkan field yang akan diupdate
        foreach ($data as $key => $value) {
            if (in_array($key, ['jumlah', 'sarpras_id', 'user_id', 'approved_by'])) {
                $fields[] = "{$key} = " . (int)$value;
            } else {
                $fields[] = "{$key} = '" . $this->conn->real_escape_string($value) . "'";
            }
        }
        
        $sql = "UPDATE peminjaman SET " . implode(', ', $fields) . " WHERE id = {$id}";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Menghapus data peminjaman
     * @param int $id ID peminjaman
     * @return bool Status penghapusan data
     */
    public function deletePeminjaman($id) {
        $id = (int)$id;
        
        // Cek status peminjaman
        $sql = "SELECT status FROM peminjaman WHERE id = {$id}";
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            // Hanya peminjaman dengan status Menunggu atau Ditolak yang bisa dihapus
            if (!in_array($row['status'], ['Menunggu', 'Ditolak'])) {
                return false;
            }
            
            // Hapus data peminjaman
            $sql = "DELETE FROM peminjaman WHERE id = {$id}";
            
            return mysqli_query($this->conn, $sql);
        }
        
        return false;
    }
    
    /**
     * Proses persetujuan peminjaman
     * @param int $id ID peminjaman
     * @param string $status Status baru
     * @param int $approved_by ID user yang menyetujui
     * @param string $catatan Catatan tambahan
     * @return bool Status update
     */
    public function approvePeminjaman($id, $status, $approved_by, $catatan = '') {
        $id = (int)$id;
        $status = $this->conn->real_escape_string($status);
        $approved_by = (int)$approved_by;
        $catatan = $this->conn->real_escape_string($catatan);
        
        // Dapatkan data peminjaman
        $peminjaman = $this->getPeminjamanById($id);
        
        if (!$peminjaman) {
            return false;
        }
        
        // Update status peminjaman
        $sql = "UPDATE peminjaman SET 
                status = '{$status}',
                approved_by = {$approved_by},
                approved_at = NOW(),
                catatan = '{$catatan}'
                WHERE id = {$id}";
        
        // Jika disetujui, update ketersediaan sarpras
        if ($status == 'Disetujui') {
            // Buat instance Sarpras model
            $sarprasModel = new Sarpras($this->conn);
            
            // Update ketersediaan
            $sarprasModel->updateAvailability(
                $peminjaman['sarpras_id'], 
                $peminjaman['jumlah'], 
                'pinjam'
            );
        }
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Update status peminjaman ke Dipinjam
     * @param int $id ID peminjaman
     * @return bool Status update
     */
    public function setDipinjam($id) {
        $id = (int)$id;
        
        $sql = "UPDATE peminjaman SET 
                status = 'Dipinjam'
                WHERE id = {$id} AND status = 'Disetujui'";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Mendapatkan data peminjaman berdasarkan user
     * @param int $userId ID user
     * @param int $offset Offset untuk pagination
     * @param int $limit Limit untuk pagination
     * @param string $status Filter status
     * @return array Data peminjaman
     */
    public function getPeminjamanByUser($userId, $offset = 0, $limit = 10, $status = '') {
        $data = [];
        
        $sql = "SELECT p.*, s.nama as nama_sarpras, s.foto as foto_sarpras
                FROM peminjaman p
                JOIN sarpras s ON p.sarpras_id = s.id
                WHERE p.user_id = {$userId}";
        
        if (!empty($status)) {
            $sql .= " AND p.status = '{$status}'";
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT {$offset}, {$limit}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan statistik peminjaman
     * @return array Data statistik
     */
    public function getStatistik() {
        $sql = "SELECT 
                SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END) as disetujui,
                SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as ditolak,
                SUM(CASE WHEN status = 'Dipinjam' THEN 1 ELSE 0 END) as dipinjam,
                SUM(CASE WHEN status = 'Dikembalikan' THEN 1 ELSE 0 END) as dikembalikan,
                SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as terlambat,
                COUNT(*) as total
                FROM peminjaman";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return [
                'menunggu' => 0,
                'disetujui' => 0,
                'ditolak' => 0,
                'dipinjam' => 0,
                'dikembalikan' => 0,
                'terlambat' => 0,
                'total' => 0
            ];
        }
    }
    
    /**
     * Mendapatkan statistik peminjaman bulanan
     * @param int $year Tahun
     * @return array Data statistik
     */
    public function getMonthlyStats($year = null) {
        if ($year === null) {
            $year = date('Y');
        }
        
        $data = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            'data' => array_fill(0, 12, 0)
        ];
        
        $sql = "SELECT MONTH(created_at) as bulan, COUNT(*) as jumlah
                FROM peminjaman
                WHERE YEAR(created_at) = {$year}
                GROUP BY MONTH(created_at)";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data['data'][$row['bulan'] - 1] = (int)$row['jumlah'];
            }
        }
        
        return $data;
    }
    
    /**
     * Cek apakah pengguna memiliki peminjaman aktif untuk sarpras tertentu
     * @param int $userId ID pengguna
     * @param int $sarprasId ID sarpras
     * @return bool Status peminjaman
     */
    public function hasActiveLoan($userId, $sarprasId) {
        $userId = (int)$userId;
        $sarprasId = (int)$sarprasId;
        
        $sql = "SELECT COUNT(*) as total FROM peminjaman
                WHERE user_id = {$userId}
                AND sarpras_id = {$sarprasId}
                AND status IN ('Menunggu', 'Disetujui', 'Dipinjam')";
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        return $row['total'] > 0;
    }
    
    /**
     * Update status peminjaman yang terlambat
     * @return int Jumlah peminjaman yang diupdate
     */
    public function updateLateLoans() {
        $sql = "UPDATE peminjaman 
                SET status = 'Terlambat' 
                WHERE status = 'Dipinjam' 
                AND tanggal_kembali < CURDATE()";
        
        mysqli_query($this->conn, $sql);
        
        return mysqli_affected_rows($this->conn);
    }
}
?>