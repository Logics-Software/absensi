<?php
class Wilayah {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // ============================================
    // PROVINSI
    // ============================================
    
    public function getAllProvinsi($page = 1, $perPage = 50, $search = '', $sortBy = 'kode', $sortOrder = 'ASC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (kode LIKE ? OR nama LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam];
        }
        
        $validSortColumns = ['id', 'kode', 'nama', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'kode';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT * FROM provinsi WHERE {$where} ORDER BY {$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function countProvinsi($search = '') {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (kode LIKE ? OR nama LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam];
        }
        
        $sql = "SELECT COUNT(*) as total FROM provinsi WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    public function getProvinsiById($id) {
        $sql = "SELECT * FROM provinsi WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getProvinsiByKode($kode) {
        $sql = "SELECT * FROM provinsi WHERE kode = ?";
        return $this->db->fetchOne($sql, [$kode]);
    }
    
    public function createProvinsi($data) {
        $sql = "INSERT INTO provinsi (kode, nama) VALUES (?, ?)";
        $this->db->query($sql, [$data['kode'], $data['nama']]);
        return $this->db->lastInsertId();
    }
    
    public function updateProvinsi($id, $data) {
        $sql = "UPDATE provinsi SET kode = ?, nama = ? WHERE id = ?";
        return $this->db->query($sql, [$data['kode'], $data['nama'], $id]);
    }
    
    public function deleteProvinsi($id) {
        $sql = "DELETE FROM provinsi WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    // ============================================
    // KABUPATEN/KOTA
    // ============================================
    
    public function getAllKabupatenKota($page = 1, $perPage = 50, $search = '', $provinsiId = null, $sortBy = 'kode', $sortOrder = 'ASC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (kk.kode LIKE ? OR kk.nama LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if ($provinsiId) {
            $where .= " AND kk.provinsi_id = ?";
            $params[] = $provinsiId;
        }
        
        $validSortColumns = ['id', 'kode', 'nama', 'tipe', 'provinsi_id', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'kode';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT kk.*, p.nama as provinsi_nama 
                FROM kabupaten_kota kk 
                LEFT JOIN provinsi p ON kk.provinsi_id = p.id 
                WHERE {$where} 
                ORDER BY {$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function countKabupatenKota($search = '', $provinsiId = null) {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (kode LIKE ? OR nama LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam];
        }
        
        if ($provinsiId) {
            $where .= " AND provinsi_id = ?";
            $params[] = $provinsiId;
        }
        
        $sql = "SELECT COUNT(*) as total FROM kabupaten_kota WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    public function getKabupatenKotaById($id) {
        $sql = "SELECT kk.*, p.nama as provinsi_nama 
                FROM kabupaten_kota kk 
                LEFT JOIN provinsi p ON kk.provinsi_id = p.id 
                WHERE kk.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getKabupatenKotaByKode($kode) {
        $sql = "SELECT * FROM kabupaten_kota WHERE kode = ?";
        return $this->db->fetchOne($sql, [$kode]);
    }
    
    public function getKabupatenKotaByProvinsi($provinsiId) {
        $sql = "SELECT * FROM kabupaten_kota WHERE provinsi_id = ? ORDER BY nama";
        return $this->db->fetchAll($sql, [$provinsiId]);
    }
    
    public function createKabupatenKota($data) {
        $sql = "INSERT INTO kabupaten_kota (kode, provinsi_id, nama, tipe) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$data['kode'], $data['provinsi_id'], $data['nama'], $data['tipe']]);
        return $this->db->lastInsertId();
    }
    
    public function updateKabupatenKota($id, $data) {
        $sql = "UPDATE kabupaten_kota SET kode = ?, provinsi_id = ?, nama = ?, tipe = ? WHERE id = ?";
        return $this->db->query($sql, [$data['kode'], $data['provinsi_id'], $data['nama'], $data['tipe'], $id]);
    }
    
    public function deleteKabupatenKota($id) {
        $sql = "DELETE FROM kabupaten_kota WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    // ============================================
    // KECAMATAN
    // ============================================
    
    public function getAllKecamatan($page = 1, $perPage = 50, $search = '', $kabupatenId = null, $sortBy = 'kode', $sortOrder = 'ASC', $provinsiId = null) {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (k.kode LIKE ? OR k.nama LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if ($provinsiId) {
            $where .= " AND kk.provinsi_id = ?";
            $params[] = $provinsiId;
        }
        
        if ($kabupatenId) {
            $where .= " AND k.kabupaten_kota_id = ?";
            $params[] = $kabupatenId;
        }
        
        $validSortColumns = ['id', 'kode', 'nama', 'kabupaten_kota_id', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'kode';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT k.*, kk.nama as kabupaten_nama, p.nama as provinsi_nama 
                FROM kecamatan k 
                LEFT JOIN kabupaten_kota kk ON k.kabupaten_kota_id = kk.id 
                LEFT JOIN provinsi p ON kk.provinsi_id = p.id 
                WHERE {$where} 
                ORDER BY {$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function countKecamatan($search = '', $kabupatenId = null, $provinsiId = null) {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (k.kode LIKE ? OR k.nama LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if ($provinsiId) {
            $where .= " AND kk.provinsi_id = ?";
            $params[] = $provinsiId;
        }
        
        if ($kabupatenId) {
            $where .= " AND k.kabupaten_kota_id = ?";
            $params[] = $kabupatenId;
        }
        
        $sql = "SELECT COUNT(*) as total 
                FROM kecamatan k 
                LEFT JOIN kabupaten_kota kk ON k.kabupaten_kota_id = kk.id 
                WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    public function getKecamatanById($id) {
        $sql = "SELECT k.*, kk.nama as kabupaten_nama, kk.provinsi_id, p.nama as provinsi_nama 
                FROM kecamatan k 
                LEFT JOIN kabupaten_kota kk ON k.kabupaten_kota_id = kk.id 
                LEFT JOIN provinsi p ON kk.provinsi_id = p.id 
                WHERE k.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getKecamatanByKode($kode) {
        $sql = "SELECT * FROM kecamatan WHERE kode = ?";
        return $this->db->fetchOne($sql, [$kode]);
    }
    
    public function getKecamatanByKabupaten($kabupatenId) {
        $sql = "SELECT * FROM kecamatan WHERE kabupaten_kota_id = ? ORDER BY nama";
        return $this->db->fetchAll($sql, [$kabupatenId]);
    }
    
    public function createKecamatan($data) {
        $sql = "INSERT INTO kecamatan (kode, kabupaten_kota_id, nama) VALUES (?, ?, ?)";
        $this->db->query($sql, [$data['kode'], $data['kabupaten_kota_id'], $data['nama']]);
        return $this->db->lastInsertId();
    }
    
    public function updateKecamatan($id, $data) {
        $sql = "UPDATE kecamatan SET kode = ?, kabupaten_kota_id = ?, nama = ? WHERE id = ?";
        return $this->db->query($sql, [$data['kode'], $data['kabupaten_kota_id'], $data['nama'], $id]);
    }
    
    public function deleteKecamatan($id) {
        $sql = "DELETE FROM kecamatan WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    // ============================================
    // KELURAHAN
    // ============================================
    
    public function getAllKelurahan($page = 1, $perPage = 50, $search = '', $kecamatanId = null, $sortBy = 'kode', $sortOrder = 'ASC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (kl.kode LIKE ? OR kl.nama LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if ($kecamatanId) {
            $where .= " AND kl.kecamatan_id = ?";
            $params[] = $kecamatanId;
        }
        
        $validSortColumns = ['id', 'kode', 'nama', 'tipe', 'kecamatan_id', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'kode';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT kl.*, k.nama as kecamatan_nama, kk.nama as kabupaten_nama, p.nama as provinsi_nama 
                FROM kelurahan kl 
                LEFT JOIN kecamatan k ON kl.kecamatan_id = k.id 
                LEFT JOIN kabupaten_kota kk ON k.kabupaten_kota_id = kk.id 
                LEFT JOIN provinsi p ON kk.provinsi_id = p.id 
                WHERE {$where} 
                ORDER BY {$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function countKelurahan($search = '', $kecamatanId = null) {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (kode LIKE ? OR nama LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam];
        }
        
        if ($kecamatanId) {
            $where .= " AND kecamatan_id = ?";
            $params[] = $kecamatanId;
        }
        
        $sql = "SELECT COUNT(*) as total FROM kelurahan WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    public function getKelurahanById($id) {
        $sql = "SELECT kl.*, k.nama as kecamatan_nama, k.kabupaten_kota_id, kk.nama as kabupaten_nama, kk.provinsi_id, p.nama as provinsi_nama 
                FROM kelurahan kl 
                LEFT JOIN kecamatan k ON kl.kecamatan_id = k.id 
                LEFT JOIN kabupaten_kota kk ON k.kabupaten_kota_id = kk.id 
                LEFT JOIN provinsi p ON kk.provinsi_id = p.id 
                WHERE kl.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getKelurahanByKode($kode) {
        $sql = "SELECT * FROM kelurahan WHERE kode = ?";
        return $this->db->fetchOne($sql, [$kode]);
    }
    
    public function getKelurahanByKecamatan($kecamatanId) {
        $sql = "SELECT * FROM kelurahan WHERE kecamatan_id = ? ORDER BY nama";
        return $this->db->fetchAll($sql, [$kecamatanId]);
    }
    
    public function createKelurahan($data) {
        $sql = "INSERT INTO kelurahan (kode, kecamatan_id, nama, tipe) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$data['kode'], $data['kecamatan_id'], $data['nama'], $data['tipe']]);
        return $this->db->lastInsertId();
    }
    
    public function updateKelurahan($id, $data) {
        $sql = "UPDATE kelurahan SET kode = ?, kecamatan_id = ?, nama = ?, tipe = ? WHERE id = ?";
        return $this->db->query($sql, [$data['kode'], $data['kecamatan_id'], $data['nama'], $data['tipe'], $id]);
    }
    
    public function deleteKelurahan($id) {
        $sql = "DELETE FROM kelurahan WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}

