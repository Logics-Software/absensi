<?php
class TahunAjaran {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all tahun ajaran with pagination
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'DESC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (tahunajaran LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
        }
        
        $validSortColumns = ['id', 'tahunajaran', 'tanggalawal', 'tanggalakhir', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql = "SELECT * FROM tahunajaran WHERE {$where} ORDER BY {$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count total tahun ajaran
     */
    public function count($search = '') {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (tahunajaran LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
        }
        
        $sql = "SELECT COUNT(*) as total FROM tahunajaran WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Check if table has any data
     */
    public function hasData() {
        $sql = "SELECT COUNT(*) as total FROM tahunajaran";
        $result = $this->db->fetchOne($sql);
        $count = (int)($result['total'] ?? 0);
        return $count > 0;
    }
    
    /**
     * Find tahun ajaran by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM tahunajaran WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Get active tahun ajaran
     */
    public function getActive() {
        $sql = "SELECT * FROM tahunajaran WHERE status = 'aktif' LIMIT 1";
        return $this->db->fetchOne($sql);
    }
    
    /**
     * Set all active tahun ajaran to 'selesai' except the specified ID
     * @param int|null $exceptId ID to exclude from update (null = update all)
     */
    private function setOthersToSelesai($exceptId = null) {
        if ($exceptId !== null) {
            $sql = "UPDATE tahunajaran SET status = 'selesai' WHERE status = 'aktif' AND id != ?";
            $this->db->query($sql, [$exceptId]);
        } else {
            $sql = "UPDATE tahunajaran SET status = 'selesai' WHERE status = 'aktif'";
            $this->db->query($sql);
        }
    }
    
    /**
     * Create tahun ajaran
     * If table is empty, status must be 'aktif'
     * If status is 'aktif', set all others to 'selesai'
     */
    public function create($data) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->beginTransaction();
            
            // CEK APAKAH TABLE KOSONG
            $checkSql = "SELECT COUNT(*) as total FROM tahunajaran";
            $checkResult = $this->db->fetchOne($checkSql);
            $totalCount = (int)($checkResult['total'] ?? 0);
            $hasData = $totalCount > 0;
            
            // INSERT DATA
            // JIKA TABLE KOSONG, HARDCODE 'AKTIF' LANGSUNG DI SQL
            if (!$hasData) {
                // TABLE KOSONG = STATUS HARUS 'AKTIF' - HARDCODE DI SQL
                $sql = "INSERT INTO tahunajaran (tahunajaran, tanggalawal, tanggalakhir, status) 
                        VALUES (?, ?, ?, 'aktif')";
                $params = [
                    $data['tahunajaran'] ?? '',
                    !empty($data['tanggalawal']) ? $data['tanggalawal'] : null,
                    !empty($data['tanggalakhir']) ? $data['tanggalakhir'] : null
                ];
            } else {
                // TABLE SUDAH ADA DATA = GUNAKAN STATUS DARI CONTROLLER
                $status = isset($data['status']) && !empty($data['status']) ? $data['status'] : 'selesai';
                
                // JIKA STATUS = 'AKTIF', SET YANG LAIN MENJADI SELESAI
                if ($status === 'aktif') {
                    $this->setOthersToSelesai();
                }
                
                $sql = "INSERT INTO tahunajaran (tahunajaran, tanggalawal, tanggalakhir, status) 
                        VALUES (?, ?, ?, ?)";
                $params = [
                    $data['tahunajaran'] ?? '',
                    !empty($data['tanggalawal']) ? $data['tanggalawal'] : null,
                    !empty($data['tanggalakhir']) ? $data['tanggalakhir'] : null,
                    $status
                ];
            }
            
            $this->db->query($sql, $params);
            $id = $this->db->lastInsertId();
            
            $conn->commit();
            return $id;
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
    
    /**
     * Update tahun ajaran
     * If status is changed to 'aktif', set all others to 'selesai'
     */
    public function update($id, $data) {
        $existing = $this->findById($id);
        if (!$existing) {
            return false;
        }
        
        $conn = $this->db->getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Get status from data
            $status = isset($data['status']) && !empty($data['status']) ? $data['status'] : $existing['status'];
            
            // If changing status to aktif, set all others to selesai
            if ($status === 'aktif') {
                $this->setOthersToSelesai($id);
            }
            
            // Update tahun ajaran
            $sql = "UPDATE tahunajaran SET 
                    tahunajaran = ?,
                    tanggalawal = ?,
                    tanggalakhir = ?,
                    status = ?
                    WHERE id = ?";
            
            $params = [
                $data['tahunajaran'] ?? '',
                !empty($data['tanggalawal']) ? $data['tanggalawal'] : null,
                !empty($data['tanggalakhir']) ? $data['tanggalakhir'] : null,
                $status,
                $id
            ];
            
            $result = $this->db->query($sql, $params);
            
            $conn->commit();
            return $result;
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
    
    /**
     * Delete tahun ajaran
     */
    public function delete($id) {
        $sql = "DELETE FROM tahunajaran WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Check if tahun ajaran has ended (tanggalakhir < today)
     */
    public function checkAndUpdateEnded() {
        $sql = "UPDATE tahunajaran SET status = 'selesai' 
                WHERE status = 'aktif' AND tanggalakhir < CURDATE()";
        return $this->db->query($sql);
    }
}

