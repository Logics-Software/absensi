<?php
class WaTemplate {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all templates
     */
    public function getAll($kategori = null) {
        $sql = "SELECT * FROM wa_templates WHERE 1=1";
        $params = [];
        
        if ($kategori !== null) {
            $sql .= " AND kategori = ?";
            $params[] = $kategori;
        }
        
        $sql .= " ORDER BY kategori, nama ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get active templates
     */
    public function getActive($kategori = null) {
        $sql = "SELECT * FROM wa_templates WHERE is_active = 1";
        $params = [];
        
        if ($kategori !== null) {
            $sql .= " AND kategori = ?";
            $params[] = $kategori;
        }
        
        $sql .= " ORDER BY kategori, nama ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get template by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM wa_templates WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Create template
     */
    public function create($data) {
        $sql = "INSERT INTO wa_templates (nama, kategori, pesan, variabel, is_active) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['nama'] ?? '',
            $data['kategori'] ?? null,
            $data['pesan'] ?? '',
            !empty($data['variabel']) ? json_encode($data['variabel']) : null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update template
     */
    public function update($id, $data) {
        $sql = "UPDATE wa_templates SET 
                nama = ?,
                kategori = ?,
                pesan = ?,
                variabel = ?,
                is_active = ?
                WHERE id = ?";
        
        $params = [
            $data['nama'] ?? '',
            $data['kategori'] ?? null,
            $data['pesan'] ?? '',
            !empty($data['variabel']) ? json_encode($data['variabel']) : null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $id
        ];
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Delete template
     */
    public function delete($id) {
        $sql = "DELETE FROM wa_templates WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}

