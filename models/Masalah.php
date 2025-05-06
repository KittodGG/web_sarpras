<?php
/**
 * Model untuk manajemen data masalah
 */
class Masalah {
    private $conn;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Mendapatkan semua data masalah
     * @param int $offset Offset untuk pagination
     * @param int $limit Limit untuk pagination
     * @param string $search Kata kunci pencarian
     * @param string $status Filter status
     * @return array Data masalah
     */
    public function getAllMasalah($offset = 0, $limit = 10, $search = '', $status = '') {
        $data = [];
        
        $sql = "SELECT m.*, p.kode_peminjaman, s.nama as nama_sarpras, 
                u.nama_lengkap as nama_pelapor
                FROM masalah m
                JOIN peminjaman p ON m.peminjaman_id = p.id
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN users u ON p.user_id = u.id";
        
        // Tambahkan kondisi pencarian dan filter
        $where = [];
        
        if (!empty($search)) {
            $where[] = "(s.nama LIKE '%{$search}%' 
                        OR u.nama_lengkap LIKE '%{$search}%' 
                        OR p.kode_peminjaman LIKE '%{$search}%'
                        OR m.deskripsi LIKE '%{$search}%')";
        }
        
        if (!empty($status)) {
            $where[] = "m.status = '{$status}'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY m.created_at DESC LIMIT {$offset}, {$limit}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan total data masalah
     * @param string $search Kata kunci pencarian
     * @param string $status Filter status
     * @return int Total data
     */
    public function getTotalMasalah($search = '', $status = '') {
        $sql = "SELECT COUNT(*) as total 
                FROM masalah m
                JOIN peminjaman p ON m.peminjaman_id = p.id
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN users u ON p.user_id = u.id";
        
        // Tambahkan kondisi pencarian dan filter
        $where = [];
        
        if (!empty($search)) {
            $where[] = "(s.nama LIKE '%{$search}%' 
                        OR u.nama_lengkap LIKE '%{$search}%' 
                        OR p.kode_peminjaman LIKE '%{$search}%'
                        OR m.deskripsi LIKE '%{$search}%')";
        }
        
        if (!empty($status)) {
            $where[] = "m.status = '{$status}'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        return $row['total'];
    }
    
    /**
     * Mendapatkan data masalah berdasarkan ID
     * @param int $id ID masalah
     * @return array|bool Data masalah atau false jika tidak ditemukan
     */
    public function getMasalahById($id) {
        $sql = "SELECT m.*, p.kode_peminjaman, p.tanggal_pinjam, p.tanggal_kembali,
                p.jumlah, p.tujuan_peminjaman, p.status as status_peminjaman,
                s.id as sarpras_id, s.nama as nama_sarpras, s.kode as kode_sarpras, s.foto as foto_sarpras,
                s.kondisi as kondisi_sarpras, k.nama as nama_kategori,
                u.id as user_id, u.nama_lengkap as nama_pelapor, u.email as email_pelapor, u.no_telp as telp_pelapor
                FROM masalah m
                JOIN peminjaman p ON m.peminjaman_id = p.id
                JOIN sarpras s ON p.sarpras_id = s.id
                JOIN kategori k ON s.kategori_id = k.id
                JOIN users u ON p.user_id = u.id
                WHERE m.id = {$id}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return false;
        }
    }
    
    /**
     * Menambahkan data masalah baru
     * @param array $data Data masalah
     * @return bool|int Status penambahan data atau ID masalah
     */
    public function addMasalah($data) {
        $peminjaman_id = (int)$data['peminjaman_id'];
        $deskripsi = $this->conn->real_escape_string($data['deskripsi']);
        $status = $this->conn->real_escape_string($data['status']);
        $foto = isset($data['foto']) ? $this->conn->real_escape_string($data['foto']) : '';
        
        $sql = "INSERT INTO masalah (peminjaman_id, deskripsi, status, foto) 
                VALUES ({$peminjaman_id}, '{$deskripsi}', '{$status}', '{$foto}')";
        
        if (mysqli_query($this->conn, $sql)) {
            return mysqli_insert_id($this->conn);
        } else {
            return false;
        }
    }
    
    /**
     * Mengupdate status masalah
     * @param int $id ID masalah
     * @param string $status Status baru
     * @return bool Status update
     */
    public function updateStatus($id, $status) {
        $id = (int)$id;
        $status = $this->conn->real_escape_string($status);
        
        $sql = "UPDATE masalah SET 
                status = '{$status}',
                updated_at = NOW()
                WHERE id = {$id}";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Mengupdate data masalah
     * @param int $id ID masalah
     * @param array $data Data masalah
     * @return bool Status update
     */
    public function updateMasalah($id, $data) {
        $id = (int)$id;
        
        $fields = [];
        
        // Persiapkan field yang akan diupdate
        foreach ($data as $key => $value) {
            if ($key == 'peminjaman_id') {
                $fields[] = "{$key} = " . (int)$value;
            } else {
                $fields[] = "{$key} = '" . $this->conn->real_escape_string($value) . "'";
            }
        }
        
        $sql = "UPDATE masalah SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = {$id}";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Menghapus data masalah
     * @param int $id ID masalah
     * @return bool Status penghapusan data
     */
    public function deleteMasalah($id) {
        $id = (int)$id;
        
        $sql = "DELETE FROM masalah WHERE id = {$id}";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Mendapatkan data masalah berdasarkan peminjaman
     * @param int $peminjamanId ID peminjaman
     * @return array Data masalah
     */
    public function getMasalahByPeminjaman($peminjamanId) {
        $data = [];
        
        $sql = "SELECT * FROM masalah 
                WHERE peminjaman_id = {$peminjamanId}
                ORDER BY created_at DESC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan statistik masalah
     * @return array Data statistik
     */
    public function getStatistik() {
        $sql = "SELECT 
                SUM(CASE WHEN status = 'Dilaporkan' THEN 1 ELSE 0 END) as dilaporkan,
                SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as diproses,
                SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai,
                COUNT(*) as total
                FROM masalah";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return [
                'dilaporkan' => 0,
                'diproses' => 0,
                'selesai' => 0,
                'total' => 0
            ];
        }
    }
}
?>