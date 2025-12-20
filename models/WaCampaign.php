<?php
class WaCampaign {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all campaigns with pagination
     */
    public function getAll($page = 1, $perPage = 20, $search = '', $sortBy = 'created_at', $sortOrder = 'DESC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (nama LIKE ? OR pesan LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam];
        }
        
        $validSortColumns = ['id', 'nama', 'status', 'created_at', 'sent_at', 'total_recipient'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'created_at';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql = "SELECT c.*, 
                t.nama as template_nama,
                u.namalengkap as created_by_name
                FROM wa_campaigns c
                LEFT JOIN wa_templates t ON c.template_id = t.id
                LEFT JOIN users u ON c.created_by = u.id
                WHERE {$where}
                ORDER BY c.{$sortBy} {$sortOrder}
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count campaigns
     */
    public function count($search = '') {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (nama LIKE ? OR pesan LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam];
        }
        
        $sql = "SELECT COUNT(*) as total FROM wa_campaigns WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Get campaign by ID
     */
    public function findById($id) {
        $sql = "SELECT c.*, 
                t.nama as template_nama,
                u.namalengkap as created_by_name
                FROM wa_campaigns c
                LEFT JOIN wa_templates t ON c.template_id = t.id
                LEFT JOIN users u ON c.created_by = u.id
                WHERE c.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Create campaign
     */
    public function create($data) {
        $sql = "INSERT INTO wa_campaigns (
                    nama, template_id, pesan, tipe_recipient, 
                    total_recipient, status, scheduled_at, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['nama'] ?? '',
            !empty($data['template_id']) ? (int)$data['template_id'] : null,
            $data['pesan'] ?? '',
            $data['tipe_recipient'] ?? 'custom',
            (int)($data['total_recipient'] ?? 0),
            $data['status'] ?? 'draft',
            !empty($data['scheduled_at']) ? $data['scheduled_at'] : null,
            !empty($data['created_by']) ? (int)$data['created_by'] : null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update campaign
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['nama', 'template_id', 'pesan', 'tipe_recipient', 'total_recipient', 
                         'status', 'scheduled_at', 'total_sent', 'total_delivered', 'total_failed', 'sent_at'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                if (in_array($field, ['template_id', 'total_recipient', 'total_sent', 'total_delivered', 'total_failed', 'created_by'])) {
                    // Ensure integer fields are never null - use 0 as default
                    $value = isset($data[$field]) ? (int)$data[$field] : 0;
                    $params[] = $value;
                } else {
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE wa_campaigns SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Delete campaign
     */
    public function delete($id) {
        $sql = "DELETE FROM wa_campaigns WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}

