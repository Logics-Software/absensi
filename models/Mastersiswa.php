<?php
class Mastersiswa {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find mastersiswa by ID
     */
    public function findById($id) {
        $sql = "SELECT ms.*, 
                ta.tahunajaran as tahunajaran_nama,
                k.namakelas as kelas_nama,
                k.kelas as kelas_level,
                p.nama as provinsi_nama,
                kk.nama as kabupaten_nama,
                kc.nama as kecamatan_nama,
                kl.nama as kelurahan_nama,
                pw.nama as provinsi_wali_nama,
                kkw.nama as kabupaten_wali_nama,
                kcw.nama as kecamatan_wali_nama,
                klw.nama as kelurahan_wali_nama
                FROM mastersiswa ms
                LEFT JOIN tahunajaran ta ON ms.idtahunajaran = ta.id
                LEFT JOIN kelas k ON ms.idkelas = k.idkelas
                LEFT JOIN provinsi p ON ms.idprovinsi = p.id
                LEFT JOIN kabupaten_kota kk ON ms.idkabupaten = kk.id
                LEFT JOIN kecamatan kc ON ms.idkecamatan = kc.id
                LEFT JOIN kelurahan kl ON ms.idkelurahan = kl.id
                LEFT JOIN provinsi pw ON ms.idprovinsiwali = pw.id
                LEFT JOIN kabupaten_kota kkw ON ms.idkabupatenwali = kkw.id
                LEFT JOIN kecamatan kcw ON ms.idkecamatanwali = kcw.id
                LEFT JOIN kelurahan klw ON ms.idkelurahanwali = klw.id
                WHERE ms.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Find mastersiswa by NISN
     */
    public function findByNisn($nisn) {
        $sql = "SELECT * FROM mastersiswa WHERE nisn = ?";
        return $this->db->fetchOne($sql, [$nisn]);
    }
    
    /**
     * Get all mastersiswa with pagination and search
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'ASC', $filterTahunAjaran = null, $filterKelas = null) {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (ms.nisn LIKE ? OR ms.nik LIKE ? OR ms.namasiswa LIKE ? OR ms.email LIKE ? OR ms.nomorhp LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        // Filter by tahun ajaran
        if ($filterTahunAjaran !== null && $filterTahunAjaran > 0) {
            $where .= " AND ms.idtahunajaran = ?";
            $params[] = (int)$filterTahunAjaran;
        }
        
        // Filter by kelas
        if ($filterKelas !== null && $filterKelas > 0) {
            $where .= " AND ms.idkelas = ?";
            $params[] = (int)$filterKelas;
        }
        
        $validSortColumns = ['id', 'nisn', 'namasiswa', 'jeniskelamin', 'email', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT ms.*, 
                ta.tahunajaran as tahunajaran_nama,
                k.namakelas as kelas_nama,
                k.kelas as kelas_level
                FROM mastersiswa ms
                LEFT JOIN tahunajaran ta ON ms.idtahunajaran = ta.id
                LEFT JOIN kelas k ON ms.idkelas = k.idkelas
                WHERE {$where} 
                ORDER BY ms.{$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count total mastersiswa with search
     */
    public function count($search = '', $filterTahunAjaran = null, $filterKelas = null) {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (ms.nisn LIKE ? OR ms.nik LIKE ? OR ms.namasiswa LIKE ? OR ms.email LIKE ? OR ms.nomorhp LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        // Filter by tahun ajaran
        if ($filterTahunAjaran !== null && $filterTahunAjaran > 0) {
            $where .= " AND ms.idtahunajaran = ?";
            $params[] = (int)$filterTahunAjaran;
        }
        
        // Filter by kelas
        if ($filterKelas !== null && $filterKelas > 0) {
            $where .= " AND ms.idkelas = ?";
            $params[] = (int)$filterKelas;
        }
        
        $sql = "SELECT COUNT(*) as total FROM mastersiswa ms WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Create mastersiswa
     */
    public function create($data) {
        $sql = "INSERT INTO mastersiswa (nisn, nik, noabsensi, namasiswa, jeniskelamin, tempatlahir, tanggallahir, email, nomorhp, 
                idprovinsi, idkabupaten, idkecamatan, idkelurahan, alamatsiswa, idtahunajaran, idkelas, 
                namawali, hubungan, nomorhpwali, idprovinsiwali, idkabupatenwali, idkecamatanwali, idkelurahanwali, alamatwali, foto, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['nisn'] ?? null,
            $data['nik'] ?? null,
            $data['noabsensi'] ?? null,
            $data['namasiswa'] ?? null,
            $data['jeniskelamin'] ?? null,
            $data['tempatlahir'] ?? null,
            !empty($data['tanggallahir']) ? $data['tanggallahir'] : null,
            $data['email'] ?? null,
            $data['nomorhp'] ?? null,
            !empty($data['idprovinsi']) ? (int)$data['idprovinsi'] : null,
            !empty($data['idkabupaten']) ? (int)$data['idkabupaten'] : null,
            !empty($data['idkecamatan']) ? (int)$data['idkecamatan'] : null,
            !empty($data['idkelurahan']) ? (int)$data['idkelurahan'] : null,
            $data['alamatsiswa'] ?? null,
            !empty($data['idtahunajaran']) ? (int)$data['idtahunajaran'] : null,
            !empty($data['idkelas']) ? (int)$data['idkelas'] : null,
            $data['namawali'] ?? null,
            $data['hubungan'] ?? null,
            $data['nomorhpwali'] ?? null,
            !empty($data['idprovinsiwali']) ? (int)$data['idprovinsiwali'] : null,
            !empty($data['idkabupatenwali']) ? (int)$data['idkabupatenwali'] : null,
            !empty($data['idkecamatanwali']) ? (int)$data['idkecamatanwali'] : null,
            !empty($data['idkelurahanwali']) ? (int)$data['idkelurahanwali'] : null,
            $data['alamatwali'] ?? null,
            $data['foto'] ?? null,
            $data['status'] ?? 'aktif'
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update mastersiswa
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['nisn', 'nik', 'noabsensi', 'namasiswa', 'jeniskelamin', 'tempatlahir', 'tanggallahir', 'email', 'nomorhp',
                          'idprovinsi', 'idkabupaten', 'idkecamatan', 'idkelurahan', 'alamatsiswa', 'idtahunajaran', 'idkelas',
                          'namawali', 'hubungan', 'nomorhpwali', 'idprovinsiwali', 'idkabupatenwali', 'idkecamatanwali', 'idkelurahanwali', 'alamatwali', 'foto', 'status'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                if (in_array($field, ['idprovinsi', 'idkabupaten', 'idkecamatan', 'idkelurahan', 'idtahunajaran', 'idkelas',
                                      'idprovinsiwali', 'idkabupatenwali', 'idkecamatanwali', 'idkelurahanwali']) && $data[$field] === null) {
                    $params[] = null;
                } elseif (in_array($field, ['idprovinsi', 'idkabupaten', 'idkecamatan', 'idkelurahan', 'idtahunajaran', 'idkelas',
                                             'idprovinsiwali', 'idkabupatenwali', 'idkecamatanwali', 'idkelurahanwali'])) {
                    $params[] = (int)$data[$field];
                } elseif ($field === 'tanggallahir') {
                    $params[] = !empty($data[$field]) ? $data[$field] : null;
                } else {
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE mastersiswa SET " . implode(', ', $fields) . " WHERE id = ?";
        
        try {
            $result = $this->db->query($sql, $params);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Mastersiswa update error: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    /**
     * Delete mastersiswa
     */
    public function delete($id) {
        $sql = "DELETE FROM mastersiswa WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Get kelas by tahun ajaran
     */
    public function getKelasByTahunAjaran($idtahunajaran) {
        $sql = "SELECT k.*, j.namajurusan as jurusan_nama
                FROM kelas k
                LEFT JOIN jurusan j ON k.idjurusan = j.idjurusan
                WHERE k.idtahunajaran = ? AND k.status = 'aktif'
                ORDER BY k.kelas, k.namakelas";
        return $this->db->fetchAll($sql, [$idtahunajaran]);
    }
}

