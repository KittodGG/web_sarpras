<?php
/**
 * Model untuk manajemen data kategori
 */
class Kategori {
    private $conn;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Mendapatkan semua data kategori
     * @param int $offset Offset untuk pagination
     * @param int $limit Limit untuk pagination
     * @param string $search Kata kunci pencarian
     * @return array Data kategori
     */
    public function getAllKategori($offset = 0, $limit = 10, $search = '') {
        $data = [];
        
        $sql = "SELECT k.*, COUNT(s.id) as jumlah_item 
                FROM kategori k
                LEFT JOIN sarpras s ON k.id = s.kategori_id";
        
        // Tambahkan kondisi pencarian jika ada
        if (!empty($search)) {
            $sql .= " WHERE k.nama LIKE '%{$search}%' 
                    OR k.deskripsi LIKE '%{$search}%'";
        }
        
        $sql .= " GROUP BY k.id ORDER BY k.nama ASC LIMIT {$offset}, {$limit}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan total data kategori
     * @param string $search Kata kunci pencarian
     * @return int Total data
     */
    public function getTotalKategori($search = '') {
        $sql = "SELECT COUNT(*) as total FROM kategori";
        
        // Tambahkan kondisi pencarian jika ada
        if (!empty($search)) {
            $sql .= " WHERE nama LIKE '%{$search}%' 
                    OR deskripsi LIKE '%{$search}%'";
        }
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        return $row['total'];
    }
    
    /**
     * Mendapatkan data kategori berdasarkan ID
     * @param int $id ID kategori
     * @return array|bool Data kategori atau false jika tidak ditemukan
     */
    public function getKategoriById($id) {
        $sql = "SELECT * FROM kategori WHERE id = {$id}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return false;
        }
    }
    
    /**
     * Menambahkan data kategori baru
     * @param array $data Data kategori
     * @return bool Status penambahan data
     */
    public function addKategori($data) {
        $nama = $this->conn->real_escape_string($data['nama']);
        $deskripsi = isset($data['deskripsi']) ? $this->conn->real_escape_string($data['deskripsi']) : '';
        
        $sql = "INSERT INTO kategori (nama, deskripsi) 
                VALUES ('{$nama}', '{$deskripsi}')";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Mengupdate data kategori
     * @param int $id ID kategori
     * @param array $data Data kategori
     * @return bool Status update data
     */
    public function updateKategori($id, $data) {
        $id = (int)$id;
        $nama = $this->conn->real_escape_string($data['nama']);
        $deskripsi = isset($data['deskripsi']) ? $this->conn->real_escape_string($data['deskripsi']) : '';
        
        $sql = "UPDATE kategori SET 
                nama = '{$nama}',
                deskripsi = '{$deskripsi}'
                WHERE id = {$id}";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Menghapus data kategori
     * @param int $id ID kategori
     * @return bool Status penghapusan data
     */
    public function deleteKategori($id) {
        $id = (int)$id;
        
        // Cek apakah kategori memiliki item
        $sql = "SELECT COUNT(*) as total FROM sarpras WHERE kategori_id = {$id}";
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['total'] > 0) {
            return false;
        }
        
        // Hapus data kategori
        $sql = "DELETE FROM kategori WHERE id = {$id}";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Mendapatkan semua kategori untuk dropdown
     * @return array Data kategori
     */
    public function getKategoriDropdown() {
        $data = [];
        
        $sql = "SELECT id, nama FROM kategori ORDER BY nama ASC";
        
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