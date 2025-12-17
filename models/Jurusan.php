<?php
class Jurusan {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find jurusan by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM jurusan WHERE idjurusan = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Get all jurusan with pagination and search
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'idjurusan', $sortOrder = 'ASC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (namajurusan LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam];
        }
        
        $validSortColumns = ['idjurusan', 'namajurusan', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'idjurusan';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT * FROM jurusan 
                WHERE {$where} 
                ORDER BY {$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count total jurusan with search
     */
    public function count($search = '') {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (namajurusan LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam];
        }
        
        $sql = "SELECT COUNT(*) as total FROM jurusan WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Create jurusan
     */
    public function create($data) {
        $sql = "INSERT INTO jurusan (namajurusan, status) 
                VALUES (?, ?)";
        
        $params = [
            $data['namajurusan'] ?? '',
            $data['status'] ?? 'aktif'
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update jurusan
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['namajurusan', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE jurusan SET " . implode(', ', $fields) . " WHERE idjurusan = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Delete jurusan
     */
    public function delete($id) {
        $sql = "DELETE FROM jurusan WHERE idjurusan = ?";
        return $this->db->query($sql, [$id]);
    }
}

