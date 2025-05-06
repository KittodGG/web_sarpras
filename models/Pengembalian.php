<?php
/**
 * Model untuk manajemen data pengembalian
 */
class Pengembalian {
    private $conn;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Mendapatkan semua data pengembalian
     * @param int $offset Offset untuk pagination
     * @param int $limit Limit untuk pagination
     * @param string $search Kata kunci pencarian
     * @return array Data pengembalian
     */
    public function getAllPengembalian($offset = 0, $limit = 10, $search = '') {
        $data = [];
        
        $sql = "SELECT r.*, p.kode_peminjaman, p.tanggal_pinjam, p.tanggal_kembali as tanggal_seharusnya,
                s.nama as nama_sarpras, s.kode as kode_sarpras,
                u.nama_lengkap as nama_peminjam, 
                v.nama_lengkap as nama_verifikator
                FROM pengembalian r
                JOIN peminjaman p ON r.peminjaman_id = p.id
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN users u ON p.user_id = u.id
                LEFT JOIN users v ON r.verified_by = v.id";
        
        // Tambahkan kondisi pencarian jika ada
        if (!empty($search)) {
            $sql .= " WHERE p.kode_peminjaman LIKE '%{$search}%' 
                    OR s.nama LIKE '%{$search}%' 
                    OR u.nama_lengkap LIKE '%{$search}%'";
        }
        
        $sql .= " ORDER BY r.created_at DESC LIMIT {$offset}, {$limit}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan total data pengembalian
     * @param string $search Kata kunci pencarian
     * @return int Total data
     */
    public function getTotalPengembalian($search = '') {
        $sql = "SELECT COUNT(*) as total 
                FROM pengembalian r
                JOIN peminjaman p ON r.peminjaman_id = p.id
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN users u ON p.user_id = u.id";
        
        // Tambahkan kondisi pencarian jika ada
        if (!empty($search)) {
            $sql .= " WHERE p.kode_peminjaman LIKE '%{$search}%' 
                    OR s.nama LIKE '%{$search}%' 
                    OR u.nama_lengkap LIKE '%{$search}%'";
        }
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        return $row['total'];
    }
    
    /**
     * Mendapatkan data pengembalian berdasarkan ID
     * @param int $id ID pengembalian
     * @return array|bool Data pengembalian atau false jika tidak ditemukan
     */
    public function getPengembalianById($id) {
        $sql = "SELECT r.*, p.kode_peminjaman, p.tanggal_pinjam, p.tanggal_kembali as tanggal_seharusnya,
                p.jumlah, p.tujuan_peminjaman, p.catatan as catatan_peminjaman,
                s.id as sarpras_id, s.nama as nama_sarpras, s.kode as kode_sarpras, s.foto as foto_sarpras,
                k.nama as nama_kategori,
                u.id as user_id, u.nama_lengkap as nama_peminjam, u.email as email_peminjam, u.no_telp as telp_peminjam,
                v.nama_lengkap as nama_verifikator
                FROM pengembalian r
                JOIN peminjaman p ON r.peminjaman_id = p.id
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN kategori k ON s.kategori_id = k.id
                JOIN users u ON p.user_id = u.id
                LEFT JOIN users v ON r.verified_by = v.id
                WHERE r.id = {$id}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return false;
        }
    }
    
    /**
     * Mendapatkan data pengembalian berdasarkan ID peminjaman
     * @param int $peminjamanId ID peminjaman
     * @return array|bool Data pengembalian atau false jika tidak ditemukan
     */
    public function getPengembalianByPeminjamanId($peminjamanId) {
        $sql = "SELECT r.*, p.kode_peminjaman, p.tanggal_pinjam, p.tanggal_kembali as tanggal_seharusnya,
                p.jumlah, p.tujuan_peminjaman, p.catatan as catatan_peminjaman,
                s.id as sarpras_id, s.nama as nama_sarpras, s.kode as kode_sarpras, s.foto as foto_sarpras,
                k.nama as nama_kategori,
                u.id as user_id, u.nama_lengkap as nama_peminjam, u.email as email_peminjam, u.no_telp as telp_peminjam,
                v.nama_lengkap as nama_verifikator
                FROM pengembalian r
                JOIN peminjaman p ON r.peminjaman_id = p.id
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN kategori k ON s.kategori_id = k.id
                JOIN users u ON p.user_id = u.id
                LEFT JOIN users v ON r.verified_by = v.id
                WHERE r.peminjaman_id = {$peminjamanId}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return false;
        }
    }
    
    /**
     * Menambahkan data pengembalian baru
     * @param array $data Data pengembalian
     * @return bool|int Status penambahan data atau ID pengembalian
     */
    public function addPengembalian($data) {
        $peminjaman_id = (int)$data['peminjaman_id'];
        $tanggal_kembali_aktual = $this->conn->real_escape_string($data['tanggal_kembali_aktual']);
        $kondisi = $this->conn->real_escape_string($data['kondisi']);
        $catatan = isset($data['catatan']) ? $this->conn->real_escape_string($data['catatan']) : '';
        
        // Cek apakah peminjaman valid
        $sql = "SELECT * FROM peminjaman WHERE id = {$peminjaman_id} AND status IN ('Dipinjam', 'Terlambat')";
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) == 0) {
            return false;
        }
        
        // Tambahkan data pengembalian
        $sql = "INSERT INTO pengembalian (peminjaman_id, tanggal_kembali_aktual, kondisi, catatan) 
                VALUES ({$peminjaman_id}, '{$tanggal_kembali_aktual}', '{$kondisi}', '{$catatan}')";
        
        if (mysqli_query($this->conn, $sql)) {
            $pengembalian_id = mysqli_insert_id($this->conn);
            
            // Update status peminjaman
            $sql = "UPDATE peminjaman SET status = 'Dikembalikan' WHERE id = {$peminjaman_id}";
            mysqli_query($this->conn, $sql);
            
            return $pengembalian_id;
        } else {
            return false;
        }
    }
    
    /**
     * Verifikasi pengembalian
     * @param int $id ID pengembalian
     * @param int $verifiedBy ID user yang memverifikasi
     * @param string $kondisiBaru Kondisi sarpras setelah verifikasi
     * @return bool Status verifikasi
     */
    public function verifyPengembalian($id, $verifiedBy, $kondisiBaru = null) {
        $id = (int)$id;
        $verifiedBy = (int)$verifiedBy;
        
        // Dapatkan data pengembalian
        $pengembalian = $this->getPengembalianById($id);
        
        if (!$pengembalian) {
            return false;
        }
        
        // Update data pengembalian
        $sql = "UPDATE pengembalian SET 
                verified_by = {$verifiedBy},
                verified_at = NOW()
                WHERE id = {$id}";
        
        if (mysqli_query($this->conn, $sql)) {
            // Buat instance Sarpras model
            $sarprasModel = new Sarpras($this->conn);
            
            // Update ketersediaan sarpras
            $sarprasModel->updateAvailability(
                $pengembalian['sarpras_id'], 
                $pengembalian['jumlah'], 
                'kembali'
            );
            
            // Update kondisi sarpras jika ada perubahan
            if ($kondisiBaru !== null) {
                $sarprasModel->updateKondisi(
                    $pengembalian['sarpras_id'], 
                    $kondisiBaru
                );
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Mendapatkan statistik pengembalian
     * @return array Data statistik
     */
    public function getStatistik() {
        $sql = "SELECT 
                SUM(CASE WHEN verified_by IS NULL THEN 1 ELSE 0 END) as belum_verifikasi,
                SUM(CASE WHEN verified_by IS NOT NULL THEN 1 ELSE 0 END) as sudah_verifikasi,
                SUM(CASE WHEN kondisi = 'Baik' THEN 1 ELSE 0 END) as kondisi_baik,
                SUM(CASE WHEN kondisi = 'Rusak Ringan' THEN 1 ELSE 0 END) as rusak_ringan,
                SUM(CASE WHEN kondisi = 'Rusak Berat' THEN 1 ELSE 0 END) as rusak_berat,
                COUNT(*) as total
                FROM pengembalian";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return [
                'belum_verifikasi' => 0,
                'sudah_verifikasi' => 0,
                'kondisi_baik' => 0,
                'rusak_ringan' => 0,
                'rusak_berat' => 0,
                'total' => 0
            ];
        }
    }
    
    /**
     * Mendapatkan daftar peminjaman yang belum dikembalikan
     * @return array Data peminjaman
     */
    public function getPeminjamanBelumKembali() {
        $data = [];
        
        $sql = "SELECT p.*, s.nama as nama_sarpras, u.nama_lengkap as nama_peminjam
                FROM peminjaman p
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN users u ON p.user_id = u.id
                WHERE p.status IN ('Dipinjam', 'Terlambat')
                ORDER BY p.tanggal_kembali ASC";
        
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