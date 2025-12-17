<?php
class Kelas {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find kelas by ID
     */
    public function findById($id) {
        $sql = "SELECT k.*, 
                ta.tahunajaran as tahunajaran_nama,
                j.namajurusan as jurusan_nama,
                mg.namaguru as guru_nama
                FROM kelas k
                LEFT JOIN tahunajaran ta ON k.idtahunajaran = ta.id
                LEFT JOIN jurusan j ON k.idjurusan = j.idjurusan
                LEFT JOIN masterguru mg ON k.idguru = mg.id
                WHERE k.idkelas = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Get all kelas with pagination and search
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'idkelas', $sortOrder = 'ASC', $filterTahunAjaran = null) {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (k.namakelas LIKE ? OR k.kelas LIKE ? OR ta.tahunajaran LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        // Filter by tahun ajaran
        if ($filterTahunAjaran !== null && $filterTahunAjaran > 0) {
            $where .= " AND k.idtahunajaran = ?";
            $params[] = (int)$filterTahunAjaran;
        }
        
        $validSortColumns = ['idkelas', 'namakelas', 'kelas', 'status', 'created_at', 'tahunajaran', 'jurusan'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'idkelas';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        // Handle sorting for joined columns
        $orderBy = '';
        if ($sortBy === 'tahunajaran') {
            $orderBy = "ta.tahunajaran {$sortOrder}";
        } elseif ($sortBy === 'jurusan') {
            $orderBy = "j.namajurusan {$sortOrder}";
        } else {
            $orderBy = "k.{$sortBy} {$sortOrder}";
        }
        
        $sql = "SELECT k.*, 
                ta.tahunajaran as tahunajaran_nama,
                j.namajurusan as jurusan_nama,
                mg.namaguru as guru_nama
                FROM kelas k
                LEFT JOIN tahunajaran ta ON k.idtahunajaran = ta.id
                LEFT JOIN jurusan j ON k.idjurusan = j.idjurusan
                LEFT JOIN masterguru mg ON k.idguru = mg.id
                WHERE {$where} 
                ORDER BY {$orderBy} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count total kelas with search
     */
    public function count($search = '', $filterTahunAjaran = null) {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (k.namakelas LIKE ? OR k.kelas LIKE ? OR ta.tahunajaran LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        // Filter by tahun ajaran
        if ($filterTahunAjaran !== null && $filterTahunAjaran > 0) {
            $where .= " AND k.idtahunajaran = ?";
            $params[] = (int)$filterTahunAjaran;
        }
        
        $sql = "SELECT COUNT(*) as total FROM kelas k
                LEFT JOIN tahunajaran ta ON k.idtahunajaran = ta.id
                WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Create kelas
     */
    public function create($data) {
        $sql = "INSERT INTO kelas (idtahunajaran, kelas, idjurusan, namakelas, idguru, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            !empty($data['idtahunajaran']) ? (int)$data['idtahunajaran'] : null,
            $data['kelas'] ?? '',
            !empty($data['idjurusan']) ? (int)$data['idjurusan'] : null,
            $data['namakelas'] ?? '',
            !empty($data['idguru']) ? (int)$data['idguru'] : null,
            $data['status'] ?? 'aktif'
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update kelas
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['idtahunajaran', 'kelas', 'idjurusan', 'namakelas', 'idguru', 'status'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                if (in_array($field, ['idtahunajaran', 'idjurusan', 'idguru']) && $data[$field] === null) {
                    $params[] = null;
                } elseif (in_array($field, ['idtahunajaran', 'idjurusan', 'idguru'])) {
                    $params[] = (int)$data[$field];
                } else {
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE kelas SET " . implode(', ', $fields) . " WHERE idkelas = ?";
        
        try {
            $result = $this->db->query($sql, $params);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Kelas update error: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    /**
     * Delete kelas
     */
    public function delete($id) {
        $sql = "DELETE FROM kelas WHERE idkelas = ?";
        return $this->db->query($sql, [$id]);
    }
}

