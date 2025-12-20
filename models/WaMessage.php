<?php
class WaMessage {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get messages by campaign
     */
    public function getByCampaign($campaignId, $page = 1, $perPage = 50) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM wa_messages 
                WHERE campaign_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        
        return $this->db->fetchAll($sql, [$campaignId, $perPage, $offset]);
    }
    
    /**
     * Count messages by campaign
     */
    public function countByCampaign($campaignId) {
        $sql = "SELECT COUNT(*) as total FROM wa_messages WHERE campaign_id = ?";
        $result = $this->db->fetchOne($sql, [$campaignId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Get message by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM wa_messages WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Create message
     */
    public function create($data) {
        $sql = "INSERT INTO wa_messages (
                    campaign_id, recipient_type, recipient_id, nomor_hp, nama, 
                    pesan, status, fonnte_message_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            !empty($data['campaign_id']) ? (int)$data['campaign_id'] : null,
            $data['recipient_type'] ?? 'custom',
            !empty($data['recipient_id']) ? (int)$data['recipient_id'] : null,
            $data['nomor_hp'] ?? '',
            $data['nama'] ?? null,
            $data['pesan'] ?? '',
            $data['status'] ?? 'pending',
            $data['fonnte_message_id'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Create bulk messages
     */
    public function createBulk($messages) {
        if (empty($messages)) {
            return [];
        }
        
        $sql = "INSERT INTO wa_messages (
                    campaign_id, recipient_type, recipient_id, nomor_hp, nama, 
                    pesan, status
                ) VALUES ";
        
        $values = [];
        $params = [];
        
        foreach ($messages as $msg) {
            $values[] = "(?, ?, ?, ?, ?, ?, ?)";
            $params[] = !empty($msg['campaign_id']) ? (int)$msg['campaign_id'] : null;
            $params[] = $msg['recipient_type'] ?? 'custom';
            $params[] = !empty($msg['recipient_id']) ? (int)$msg['recipient_id'] : null;
            $params[] = $msg['nomor_hp'] ?? '';
            $params[] = $msg['nama'] ?? null;
            $params[] = $msg['pesan'] ?? '';
            $params[] = $msg['status'] ?? 'pending';
        }
        
        $sql .= implode(', ', $values);
        
        $this->db->query($sql, $params);
        return true;
    }
    
    /**
     * Update message status
     */
    public function updateStatus($id, $status, $data = []) {
        try {
            $fields = ['status = ?'];
            $params = [$status];
            
            if (isset($data['status_message'])) {
                $fields[] = 'status_message = ?';
                $params[] = trim($data['status_message']);
            }
            
            if (isset($data['error_message'])) {
                $fields[] = 'error_message = ?';
                // Ensure error message is not empty and is a string
                $errorMsg = is_string($data['error_message']) ? trim($data['error_message']) : (string)$data['error_message'];
                if (empty($errorMsg)) {
                    $errorMsg = 'Error tidak diketahui';
                }
                $params[] = $errorMsg;
            }
            
            if (isset($data['fonnte_message_id'])) {
                $fields[] = 'fonnte_message_id = ?';
                $params[] = $data['fonnte_message_id'];
            }
            
            if ($status === 'sent' && !isset($data['sent_at'])) {
                $fields[] = 'sent_at = NOW()';
            }
            
            if ($status === 'delivered' && !isset($data['delivered_at'])) {
                $fields[] = 'delivered_at = NOW()';
            }
            
            if ($status === 'read' && !isset($data['read_at'])) {
                $fields[] = 'read_at = NOW()';
            }
            
            if (isset($data['sent_at'])) {
                $fields[] = 'sent_at = ?';
                $params[] = $data['sent_at'];
            }
            
            if (isset($data['delivered_at'])) {
                $fields[] = 'delivered_at = ?';
                $params[] = $data['delivered_at'];
            }
            
            if (isset($data['read_at'])) {
                $fields[] = 'read_at = ?';
                $params[] = $data['read_at'];
            }
            
            if (isset($data['nomor_hp'])) {
                $fields[] = 'nomor_hp = ?';
                $params[] = $data['nomor_hp'];
            }
            
            $params[] = $id;
            $sql = "UPDATE wa_messages SET " . implode(', ', $fields) . " WHERE id = ?";
            
            $result = $this->db->query($sql, $params);
            
            // Log if update failed
            if (!$result) {
                error_log("WaMessage::updateStatus() - Failed to update message ID {$id} with status {$status}");
                error_log("SQL: " . $sql);
                error_log("Params: " . json_encode($params));
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("WaMessage::updateStatus() - Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Get statistics by campaign
     */
    public function getStatsByCampaign($campaignId) {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM wa_messages
                WHERE campaign_id = ?";
        
        return $this->db->fetchOne($sql, [$campaignId]);
    }
}

