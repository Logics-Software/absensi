<?php
class MasterGuru {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find master guru by ID
     */
    public function findById($id) {
        $sql = "SELECT mg.*, 
                u.namalengkap as user_nama,
                u.email as user_email,
                p.nama as provinsi_nama,
                kk.nama as kabupaten_nama,
                kc.nama as kecamatan_nama,
                kl.nama as kelurahan_nama
                FROM masterguru mg
                LEFT JOIN users u ON mg.iduser = u.id
                LEFT JOIN provinsi p ON mg.idprovinsi = p.id
                LEFT JOIN kabupaten_kota kk ON mg.idkabupaten = kk.id
                LEFT JOIN kecamatan kc ON mg.idkecamatan = kc.id
                LEFT JOIN kelurahan kl ON mg.idkelurahan = kl.id
                WHERE mg.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Find master guru by NIP
     */
    public function findByNip($nip) {
        $sql = "SELECT * FROM masterguru WHERE nip = ?";
        return $this->db->fetchOne($sql, [$nip]);
    }
    
    /**
     * Get all master guru with pagination and search
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'ASC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (mg.nip LIKE ? OR mg.namaguru LIKE ? OR mg.email LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $validSortColumns = ['id', 'nip', 'namaguru', 'jeniskelamin', 'email', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT mg.*, 
                u.namalengkap as user_nama,
                p.nama as provinsi_nama,
                kk.nama as kabupaten_nama
                FROM masterguru mg
                LEFT JOIN users u ON mg.iduser = u.id
                LEFT JOIN provinsi p ON mg.idprovinsi = p.id
                LEFT JOIN kabupaten_kota kk ON mg.idkabupaten = kk.id
                WHERE {$where} 
                ORDER BY mg.{$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count total master guru with search
     */
    public function count($search = '') {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (nip LIKE ? OR namaguru LIKE ? OR email LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $sql = "SELECT COUNT(*) as total FROM masterguru WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Create master guru
     */
    public function create($data) {
        $sql = "INSERT INTO masterguru (nip, namaguru, jeniskelamin, tempatlahir, tanggallahir, alamatguru, idprovinsi, idkabupaten, idkecamatan, idkelurahan, kodepos, nomorhp, email, foto, iduser, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['nip'] ?? null,
            $data['namaguru'] ?? null,
            $data['jeniskelamin'] ?? null,
            $data['tempatlahir'] ?? null,
            !empty($data['tanggallahir']) ? $data['tanggallahir'] : null,
            $data['alamatguru'] ?? null,
            !empty($data['idprovinsi']) ? (int)$data['idprovinsi'] : null,
            !empty($data['idkabupaten']) ? (int)$data['idkabupaten'] : null,
            !empty($data['idkecamatan']) ? (int)$data['idkecamatan'] : null,
            !empty($data['idkelurahan']) ? (int)$data['idkelurahan'] : null,
            $data['kodepos'] ?? null,
            $data['nomorhp'] ?? null,
            $data['email'] ?? null,
            $data['foto'] ?? null,
            !empty($data['iduser']) ? (int)$data['iduser'] : null,
            $data['status'] ?? 'aktif'
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update master guru
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['nip', 'namaguru', 'jeniskelamin', 'tempatlahir', 'tanggallahir', 'alamatguru', 
                          'idprovinsi', 'idkabupaten', 'idkecamatan', 'idkelurahan', 'kodepos', 'nomorhp', 
                          'email', 'foto', 'iduser', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if (in_array($field, ['idprovinsi', 'idkabupaten', 'idkecamatan', 'idkelurahan', 'iduser'])) {
                    $fields[] = "{$field} = ?";
                    $params[] = !empty($data[$field]) ? (int)$data[$field] : null;
                } elseif ($field === 'tanggallahir') {
                    $fields[] = "{$field} = ?";
                    $params[] = !empty($data[$field]) ? $data[$field] : null;
                } else {
                    $fields[] = "{$field} = ?";
                    $params[] = $data[$field];
                }
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE masterguru SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Delete master guru
     */
    public function delete($id) {
        $sql = "DELETE FROM masterguru WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Get list guru (user dengan role guru) untuk dropdown
     */
    public function getGuruList() {
        // Check if id_guru column exists by querying information_schema
        $checkColumn = "SELECT COUNT(*) as cnt FROM information_schema.COLUMNS 
                       WHERE TABLE_SCHEMA = DATABASE() 
                       AND TABLE_NAME = 'users' 
                       AND COLUMN_NAME = 'id_guru'";
        $result = $this->db->fetchOne($checkColumn);
        $hasIdGuru = $result && $result['cnt'] > 0;
        
        if ($hasIdGuru) {
            $sql = "SELECT id, namalengkap, email, id_guru FROM users WHERE role = 'guru' AND status = 'aktif' ORDER BY namalengkap";
        } else {
            // Fallback if id_guru column doesn't exist yet (use kodesales or empty)
            $sql = "SELECT id, namalengkap, email, COALESCE(kodesales, '') as id_guru FROM users WHERE role = 'guru' AND status = 'aktif' ORDER BY namalengkap";
        }
        
        return $this->db->fetchAll($sql);
    }
}

