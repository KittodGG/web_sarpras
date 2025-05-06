<?php
/**
 * Model untuk manajemen data sarpras
 */
class Sarpras {
    private $conn;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Mendapatkan semua data sarpras
     * @param int $offset Offset untuk pagination
     * @param int $limit Limit untuk pagination
     * @param string $search Kata kunci pencarian
     * @return array Data sarpras
     */
    public function getAllSarpras($offset = 0, $limit = 10, $search = '') {
        $data = [];
        
        $sql = "SELECT s.*, k.nama as nama_kategori 
                FROM sarpras s
                JOIN kategori k ON s.kategori_id = k.id";
        
        // Tambahkan kondisi pencarian jika ada
        if (!empty($search)) {
            $sql .= " WHERE s.nama LIKE '%{$search}%' 
                    OR s.kode LIKE '%{$search}%' 
                    OR k.nama LIKE '%{$search}%'";
        }
        
        $sql .= " ORDER BY s.created_at DESC LIMIT {$offset}, {$limit}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan total data sarpras
     * @param string $search Kata kunci pencarian
     * @return int Total data
     */
    public function getTotalSarpras($search = '') {
        $sql = "SELECT COUNT(*) as total FROM sarpras s
                JOIN kategori k ON s.kategori_id = k.id";
        
        // Tambahkan kondisi pencarian jika ada
        if (!empty($search)) {
            $sql .= " WHERE s.nama LIKE '%{$search}%' 
                    OR s.kode LIKE '%{$search}%' 
                    OR k.nama LIKE '%{$search}%'";
        }
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        return $row['total'];
    }
    
    /**
     * Mendapatkan data sarpras berdasarkan ID
     * @param int $id ID sarpras
     * @return array|bool Data sarpras atau false jika tidak ditemukan
     */
    public function getSarprasById($id) {
        $sql = "SELECT s.*, k.nama as nama_kategori 
                FROM sarpras s
                JOIN kategori k ON s.kategori_id = k.id
                WHERE s.id = {$id}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return false;
        }
    }
    
    /**
     * Menambahkan data sarpras baru
     * @param array $data Data sarpras
     * @return bool Status penambahan data
     */
    public function addSarpras($data) {
        $kode = $this->conn->real_escape_string($data['kode']);
        $nama = $this->conn->real_escape_string($data['nama']);
        $deskripsi = $this->conn->real_escape_string($data['deskripsi']);
        $kategori_id = (int)$data['kategori_id'];
        $stok = (int)$data['stok'];
        $tersedia = (int)$data['tersedia'];
        $kondisi = $this->conn->real_escape_string($data['kondisi']);
        $foto = isset($data['foto']) ? $this->conn->real_escape_string($data['foto']) : '';
        $lokasi = $this->conn->real_escape_string($data['lokasi']);
        $tanggal_pengadaan = $this->conn->real_escape_string($data['tanggal_pengadaan']);
        
        $sql = "INSERT INTO sarpras (kode, nama, deskripsi, kategori_id, stok, tersedia, kondisi, foto, lokasi, tanggal_pengadaan) 
                VALUES ('{$kode}', '{$nama}', '{$deskripsi}', {$kategori_id}, {$stok}, {$tersedia}, '{$kondisi}', '{$foto}', '{$lokasi}', '{$tanggal_pengadaan}')";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Mengupdate data sarpras
     * @param int $id ID sarpras
     * @param array $data Data sarpras
     * @return bool Status update data
     */
    public function updateSarpras($id, $data) {
        $id = (int)$id;
        $kode = $this->conn->real_escape_string($data['kode']);
        $nama = $this->conn->real_escape_string($data['nama']);
        $deskripsi = $this->conn->real_escape_string($data['deskripsi']);
        $kategori_id = (int)$data['kategori_id'];
        $stok = (int)$data['stok'];
        $tersedia = (int)$data['tersedia'];
        $kondisi = $this->conn->real_escape_string($data['kondisi']);
        $lokasi = $this->conn->real_escape_string($data['lokasi']);
        $tanggal_pengadaan = $this->conn->real_escape_string($data['tanggal_pengadaan']);
        
        $sql = "UPDATE sarpras SET 
                kode = '{$kode}',
                nama = '{$nama}',
                deskripsi = '{$deskripsi}',
                kategori_id = {$kategori_id},
                stok = {$stok},
                tersedia = {$tersedia},
                kondisi = '{$kondisi}',
                lokasi = '{$lokasi}',
                tanggal_pengadaan = '{$tanggal_pengadaan}'";
        
        // Update foto jika ada
        if (isset($data['foto']) && !empty($data['foto'])) {
            $foto = $this->conn->real_escape_string($data['foto']);
            $sql .= ", foto = '{$foto}'";
        }
        
        $sql .= " WHERE id = {$id}";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Menghapus data sarpras
     * @param int $id ID sarpras
     * @return bool Status penghapusan data
     */
    public function deleteSarpras($id) {
        $id = (int)$id;
        
        // Cek apakah sarpras sedang dipinjam
        $sql = "SELECT COUNT(*) as total FROM peminjaman 
                WHERE sarpras_id = {$id} 
                AND status IN ('Menunggu', 'Disetujui', 'Dipinjam')";
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['total'] > 0) {
            return false;
        }
        
        // Hapus data sarpras
        $sql = "DELETE FROM sarpras WHERE id = {$id}";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Mendapatkan data sarpras tersedia
     * @return array Data sarpras tersedia
     */
    public function getAvailableSarpras() {
        $data = [];
        
        $sql = "SELECT s.*, k.nama as nama_kategori 
                FROM sarpras s
                JOIN kategori k ON s.kategori_id = k.id
                WHERE s.tersedia > 0 AND s.kondisi != 'Rusak Berat'
                ORDER BY s.nama ASC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Memperbarui ketersediaan sarpras
     * @param int $id ID sarpras
     * @param int $jumlah Jumlah yang dipinjam/dikembalikan
     * @param string $type Tipe operasi ('pinjam' atau 'kembali')
     * @return bool Status update
     */
    public function updateAvailability($id, $jumlah, $type = 'pinjam') {
        $id = (int)$id;
        $jumlah = (int)$jumlah;
        
        // Dapatkan data sarpras
        $sql = "SELECT tersedia FROM sarpras WHERE id = {$id}";
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $tersedia = (int)$row['tersedia'];
            
            // Update tersedia
            if ($type == 'pinjam') {
                $tersedia -= $jumlah;
            } else {
                $tersedia += $jumlah;
            }
            
            // Pastikan tidak negatif
            if ($tersedia < 0) {
                $tersedia = 0;
            }
            
            // Update data
            $sql = "UPDATE sarpras SET tersedia = {$tersedia} WHERE id = {$id}";
            
            return mysqli_query($this->conn, $sql);
        }
        
        return false;
    }
    
    /**
     * Update kondisi sarpras
     * @param int $id ID sarpras
     * @param string $kondisi Kondisi baru
     * @return bool Status update
     */
    public function updateKondisi($id, $kondisi) {
        $id = (int)$id;
        $kondisi = $this->conn->real_escape_string($kondisi);
        
        $sql = "UPDATE sarpras SET kondisi = '{$kondisi}' WHERE id = {$id}";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Mendapatkan data statistik sarpras
     * @return array Data statistik
     */
    public function getStatistik() {
        $sql = "SELECT 
                SUM(CASE WHEN tersedia = stok THEN 1 ELSE 0 END) as tersedia_penuh,
                SUM(CASE WHEN tersedia < stok AND tersedia > 0 THEN 1 ELSE 0 END) as tersedia_sebagian,
                SUM(CASE WHEN tersedia = 0 THEN 1 ELSE 0 END) as tidak_tersedia,
                SUM(CASE WHEN kondisi = 'Baik' THEN 1 ELSE 0 END) as kondisi_baik,
                SUM(CASE WHEN kondisi = 'Rusak Ringan' THEN 1 ELSE 0 END) as rusak_ringan,
                SUM(CASE WHEN kondisi = 'Rusak Berat' THEN 1 ELSE 0 END) as rusak_berat,
                COUNT(*) as total
                FROM sarpras";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return [
                'tersedia_penuh' => 0,
                'tersedia_sebagian' => 0,
                'tidak_tersedia' => 0,
                'kondisi_baik' => 0,
                'rusak_ringan' => 0,
                'rusak_berat' => 0,
                'total' => 0
            ];
        }
    }
}
?>