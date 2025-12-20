<?php
class KonfigurasiFonnte {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get konfigurasi Fonnte (hanya 1 record)
     */
    public function get() {
        $sql = "SELECT * FROM konfigurasi_fonnte LIMIT 1";
        return $this->db->fetchOne($sql);
    }
    
    /**
     * Create or update konfigurasi Fonnte (hanya 1 record)
     */
    public function save($data) {
        $existing = $this->get();
        
        if ($existing) {
            // Update existing record
            return $this->update($data);
        } else {
            // Create new record
            return $this->create($data);
        }
    }
    
    /**
     * Create konfigurasi Fonnte
     */
    private function create($data) {
        $sql = "INSERT INTO konfigurasi_fonnte (api_key, api_url, device_id, webhook_url) 
                VALUES (?, ?, ?, ?)";
        
        $params = [
            $data['api_key'] ?? null,
            $data['api_url'] ?? 'https://api.fonnte.com',
            $data['device_id'] ?? null,
            $data['webhook_url'] ?? null
        ];
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Update konfigurasi Fonnte
     */
    private function update($data) {
        $existing = $this->get();
        if (!$existing || !isset($existing['id'])) {
            return false;
        }
        
        $sql = "UPDATE konfigurasi_fonnte SET 
                api_key = ?,
                api_url = ?,
                device_id = ?,
                webhook_url = ?
                WHERE id = ?";
        
        $params = [
            $data['api_key'] ?? null,
            $data['api_url'] ?? 'https://api.fonnte.com',
            $data['device_id'] ?? null,
            $data['webhook_url'] ?? null,
            $existing['id']
        ];
        
        return $this->db->query($sql, $params);
    }
}
