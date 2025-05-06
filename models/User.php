<?php
/**
 * Model untuk manajemen data pengguna
 */
class User {
    private $conn;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Mendapatkan semua data pengguna
     * @param int $offset Offset untuk pagination
     * @param int $limit Limit untuk pagination
     * @param string $search Kata kunci pencarian
     * @param string $role Filter berdasarkan role
     * @return array Data pengguna
     */
    public function getAllUsers($offset = 0, $limit = 10, $search = '', $role = '') {
        $data = [];
        
        $sql = "SELECT u.*, 
                CASE WHEN s.id IS NOT NULL THEN s.nis ELSE NULL END as nis,
                CASE WHEN s.id IS NOT NULL THEN k.nama_kelas ELSE NULL END as kelas,
                CASE WHEN s.id IS NOT NULL THEN j.nama ELSE NULL END as jurusan
                FROM users u
                LEFT JOIN siswa s ON u.id = s.user_id
                LEFT JOIN kelas k ON s.kelas_id = k.id
                LEFT JOIN jurusan j ON s.jurusan_id = j.id";
        
        // Tambahkan kondisi pencarian dan filter
        $where = [];
        
        if (!empty($search)) {
            $where[] = "(u.nama_lengkap LIKE '%{$search}%' 
                        OR u.username LIKE '%{$search}%' 
                        OR u.email LIKE '%{$search}%'
                        OR s.nis LIKE '%{$search}%')";
        }
        
        if (!empty($role)) {
            $where[] = "u.role = '{$role}'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $sql .= " ORDER BY u.created_at DESC LIMIT {$offset}, {$limit}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan total data pengguna
     * @param string $search Kata kunci pencarian
     * @param string $role Filter berdasarkan role
     * @return int Total data
     */
    public function getTotalUsers($search = '', $role = '') {
        $sql = "SELECT COUNT(*) as total 
                FROM users u
                LEFT JOIN siswa s ON u.id = s.user_id";
        
        // Tambahkan kondisi pencarian dan filter
        $where = [];
        
        if (!empty($search)) {
            $where[] = "(u.nama_lengkap LIKE '%{$search}%' 
                        OR u.username LIKE '%{$search}%' 
                        OR u.email LIKE '%{$search}%'
                        OR s.nis LIKE '%{$search}%')";
        }
        
        if (!empty($role)) {
            $where[] = "u.role = '{$role}'";
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        return $row['total'];
    }
    
    /**
     * Mendapatkan data pengguna berdasarkan ID
     * @param int $id ID pengguna
     * @return array|bool Data pengguna atau false jika tidak ditemukan
     */
    public function getUserById($id) {
        $sql = "SELECT u.*, 
                CASE WHEN s.id IS NOT NULL THEN s.id ELSE NULL END as siswa_id,
                CASE WHEN s.id IS NOT NULL THEN s.nis ELSE NULL END as nis,
                CASE WHEN s.id IS NOT NULL THEN s.kelas_id ELSE NULL END as kelas_id,
                CASE WHEN s.id IS NOT NULL THEN k.nama_kelas ELSE NULL END as kelas,
                CASE WHEN s.id IS NOT NULL THEN s.jurusan_id ELSE NULL END as jurusan_id,
                CASE WHEN s.id IS NOT NULL THEN j.nama ELSE NULL END as jurusan
                FROM users u
                LEFT JOIN siswa s ON u.id = s.user_id
                LEFT JOIN kelas k ON s.kelas_id = k.id
                LEFT JOIN jurusan j ON s.jurusan_id = j.id
                WHERE u.id = {$id}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return false;
        }
    }
    
    /**
     * Mendapatkan data pengguna berdasarkan username
     * @param string $username Username pengguna
     * @return array|bool Data pengguna atau false jika tidak ditemukan
     */
    public function getUserByUsername($username) {
        $username = $this->conn->real_escape_string($username);
        
        $sql = "SELECT u.*, 
                CASE WHEN s.id IS NOT NULL THEN s.id ELSE NULL END as siswa_id,
                CASE WHEN s.id IS NOT NULL THEN s.nis ELSE NULL END as nis,
                CASE WHEN s.id IS NOT NULL THEN s.kelas_id ELSE NULL END as kelas_id,
                CASE WHEN s.id IS NOT NULL THEN k.nama_kelas ELSE NULL END as kelas,
                CASE WHEN s.id IS NOT NULL THEN s.jurusan_id ELSE NULL END as jurusan_id,
                CASE WHEN s.id IS NOT NULL THEN j.nama ELSE NULL END as jurusan
                FROM users u
                LEFT JOIN siswa s ON u.id = s.user_id
                LEFT JOIN kelas k ON s.kelas_id = k.id
                LEFT JOIN jurusan j ON s.jurusan_id = j.id
                WHERE u.username = '{$username}'";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return false;
        }
    }
    
    /**
     * Menambahkan data pengguna baru
     * @param array $data Data pengguna
     * @return bool|int Status penambahan data atau ID pengguna
     */
    public function addUser($data) {
        $username = $this->conn->real_escape_string($data['username']);
        $password = $this->conn->real_escape_string($data['password']);
        $nama_lengkap = $this->conn->real_escape_string($data['nama_lengkap']);
        $email = $this->conn->real_escape_string($data['email']);
        $no_telp = isset($data['no_telp']) ? $this->conn->real_escape_string($data['no_telp']) : '';
        $role = $this->conn->real_escape_string($data['role']);
        $foto = isset($data['foto']) ? $this->conn->real_escape_string($data['foto']) : '';
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Cek apakah username sudah ada
        $sql = "SELECT * FROM users WHERE username = '{$username}'";
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return false;
        }
        
        // Tambahkan data pengguna
        $sql = "INSERT INTO users (username, password, nama_lengkap, email, no_telp, role, foto) 
                VALUES ('{$username}', '{$hashed_password}', '{$nama_lengkap}', '{$email}', '{$no_telp}', '{$role}', '{$foto}')";
        
        if (mysqli_query($this->conn, $sql)) {
            // Jika user adalah siswa, tambahkan data siswa
            $user_id = mysqli_insert_id($this->conn);
            
            if ($role == 'user' && isset($data['nis'])) {
                $nis = $this->conn->real_escape_string($data['nis']);
                $kelas_id = (int)$data['kelas_id'];
                $jurusan_id = (int)$data['jurusan_id'];
                
                $sql = "INSERT INTO siswa (user_id, nis, kelas_id, jurusan_id) 
                        VALUES ({$user_id}, '{$nis}', {$kelas_id}, {$jurusan_id})";
                
                mysqli_query($this->conn, $sql);
            }
            
            return $user_id;
        } else {
            return false;
        }
    }
    
    /**
     * Mengupdate data pengguna
     * @param int $id ID pengguna
     * @param array $data Data pengguna
     * @return bool Status update data
     */
    public function updateUser($id, $data) {
        $id = (int)$id;
        
        $fields = [];
        
        // Persiapkan field yang akan diupdate
        foreach ($data as $key => $value) {
            // Skip password jika kosong
            if ($key == 'password' && empty($value)) {
                continue;
            }
            
            // Hash password
            if ($key == 'password') {
                $value = password_hash($value, PASSWORD_DEFAULT);
            }
            
            // Skip username jika ada dan sama dengan username saat ini
            if ($key == 'username') {
                $sql = "SELECT username FROM users WHERE id = {$id}";
                $result = mysqli_query($this->conn, $sql);
                $row = mysqli_fetch_assoc($result);
                
                if ($row['username'] == $value) {
                    continue;
                }
                
                // Cek apakah username sudah ada
                $sql = "SELECT * FROM users WHERE username = '{$value}' AND id != {$id}";
                $result = mysqli_query($this->conn, $sql);
                
                if (mysqli_num_rows($result) > 0) {
                    return false;
                }
            }
            
            $fields[] = "{$key} = '" . $this->conn->real_escape_string($value) . "'";
        }
        
        if (empty($fields)) {
            return true;
        }
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = {$id}";
        
        if (mysqli_query($this->conn, $sql)) {
            // Jika data siswa ada, update atau tambahkan
            if (isset($data['nis'])) {
                $nis = $this->conn->real_escape_string($data['nis']);
                $kelas_id = (int)$data['kelas_id'];
                $jurusan_id = (int)$data['jurusan_id'];
                
                // Cek apakah data siswa sudah ada
                $sql = "SELECT * FROM siswa WHERE user_id = {$id}";
                $result = mysqli_query($this->conn, $sql);
                
                if (mysqli_num_rows($result) > 0) {
                    // Update data siswa
                    $sql = "UPDATE siswa SET 
                            nis = '{$nis}',
                            kelas_id = {$kelas_id},
                            jurusan_id = {$jurusan_id}
                            WHERE user_id = {$id}";
                } else {
                    // Tambahkan data siswa
                    $sql = "INSERT INTO siswa (user_id, nis, kelas_id, jurusan_id) 
                            VALUES ({$id}, '{$nis}', {$kelas_id}, {$jurusan_id})";
                }
                
                mysqli_query($this->conn, $sql);
            }
            
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Menghapus data pengguna
     * @param int $id ID pengguna
     * @return bool Status penghapusan data
     */
    public function deleteUser($id) {
        $id = (int)$id;
        
        // Cek apakah pengguna memiliki peminjaman aktif
        $sql = "SELECT COUNT(*) as total FROM peminjaman 
                WHERE user_id = {$id} 
                AND status IN ('Menunggu', 'Disetujui', 'Dipinjam')";
        
        $result = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($result);
        
        if ($row['total'] > 0) {
            return false;
        }
        
        // Hapus data siswa jika ada
        $sql = "DELETE FROM siswa WHERE user_id = {$id}";
        mysqli_query($this->conn, $sql);
        
        // Hapus data pengguna
        $sql = "DELETE FROM users WHERE id = {$id}";
        
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Mendapatkan statistik pengguna
     * @return array Data statistik
     */
    public function getStatistik() {
        $sql = "SELECT 
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin,
                SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as user,
                COUNT(*) as total
                FROM users";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        } else {
            return [
                'admin' => 0,
                'user' => 0,
                'total' => 0
            ];
        }
    }
    
    /**
     * Mendapatkan statistik penggunaan oleh pengguna
     * @param int $limit Limit data
     * @return array Data statistik
     */
    public function getTopUsers($limit = 5) {
        $data = [];
        
        $sql = "SELECT u.id, u.nama_lengkap, COUNT(p.id) as total_peminjaman
                FROM users u
                JOIN peminjaman p ON u.id = p.user_id
                GROUP BY u.id
                ORDER BY total_peminjaman DESC
                LIMIT {$limit}";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan daftar siswa berdasarkan kelas
     * @param int $kelasId ID kelas
     * @return array Data siswa
     */
    public function getSiswaByKelas($kelasId) {
        $data = [];
        
        $sql = "SELECT u.id, u.nama_lengkap, s.nis, j.nama as jurusan
                FROM siswa s
                JOIN users u ON s.user_id = u.id
                JOIN kelas k ON s.kelas_id = k.id
                JOIN jurusan j ON s.jurusan_id = j.id
                WHERE s.kelas_id = {$kelasId}
                ORDER BY u.nama_lengkap ASC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan daftar siswa berdasarkan jurusan
     * @param int $jurusanId ID jurusan
     * @return array Data siswa
     */
    public function getSiswaByJurusan($jurusanId) {
        $data = [];
        
        $sql = "SELECT u.id, u.nama_lengkap, s.nis, k.nama_kelas as kelas
                FROM siswa s
                JOIN users u ON s.user_id = u.id
                JOIN kelas k ON s.kelas_id = k.id
                WHERE s.jurusan_id = {$jurusanId}
                ORDER BY k.nama_kelas ASC, u.nama_lengkap ASC";
        
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        
        return $data;
    }
    
    /**
     * Mengubah password pengguna
     * @param int $id ID pengguna
     * @param string $oldPassword Password lama
     * @param string $newPassword Password baru
     * @return bool|string Status perubahan password atau pesan error
     */
    public function changePassword($id, $oldPassword, $newPassword) {
        $id = (int)$id;
        
        // Dapatkan data pengguna
        $sql = "SELECT password FROM users WHERE id = {$id}";
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            // Verifikasi password lama
            if (!password_verify($oldPassword, $row['password'])) {
                return 'Password lama salah';
            }
            
            // Hash password baru
            $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $sql = "UPDATE users SET password = '{$hashed_password}' WHERE id = {$id}";
            
            return mysqli_query($this->conn, $sql);
        } else {
            return 'Pengguna tidak ditemukan';
        }
    }
}
?>