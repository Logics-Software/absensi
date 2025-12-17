<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ? AND status = 'aktif'";
        return $this->db->fetchOne($sql, [$username]);
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        return $this->db->fetchOne($sql, [$email]);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Check if id_guru column exists and is INT type
     */
    private function hasIdGuruColumn() {
        try {
            $sql = "SELECT COLUMN_TYPE 
                    FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'users' 
                    AND COLUMN_NAME = 'id_guru'";
            $result = $this->db->fetchOne($sql);
            if ($result && strpos($result['COLUMN_TYPE'], 'int') !== false) {
                return true;
            }
        } catch (Exception $e) {
            // Column doesn't exist or error
        }
        return false;
    }
    
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'ASC') {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        // Check if id_guru column exists and is INT (for JOIN)
        $hasIdGuru = $this->hasIdGuruColumn();
        
        if ($hasIdGuru) {
            if (!empty($search)) {
                $where .= " AND (u.username LIKE ? OR u.namalengkap LIKE ? OR u.email LIKE ?)";
                $searchParam = "%{$search}%";
                $params = [$searchParam, $searchParam, $searchParam];
            }
            
            $validSortColumns = ['id', 'username', 'namalengkap', 'email', 'role', 'status', 'created_at'];
            $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
            $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
            
            $sql = "SELECT u.*, mg.namaguru as masterguru_nama, mg.nip as masterguru_nip
                    FROM users u
                    LEFT JOIN masterguru mg ON u.id_guru = mg.id
                    WHERE {$where} 
                    ORDER BY u.{$sortBy} {$sortOrder} 
                    LIMIT ? OFFSET ?";
        } else {
            if (!empty($search)) {
                $where .= " AND (username LIKE ? OR namalengkap LIKE ? OR email LIKE ?)";
                $searchParam = "%{$search}%";
                $params = [$searchParam, $searchParam, $searchParam];
            }
            
            $validSortColumns = ['id', 'username', 'namalengkap', 'email', 'role', 'status', 'created_at'];
            $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
            $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
            
            // Fallback: no JOIN if column doesn't exist or is not INT
            $sql = "SELECT *, NULL as masterguru_nama, NULL as masterguru_nip
                    FROM users 
                    WHERE {$where} 
                    ORDER BY {$sortBy} {$sortOrder} 
                    LIMIT ? OFFSET ?";
        }
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function findById($id) {
        // Check if id_guru column exists and is INT (for JOIN)
        if ($this->hasIdGuruColumn()) {
            $sql = "SELECT u.*, mg.namaguru as masterguru_nama, mg.nip as masterguru_nip
                    FROM users u
                    LEFT JOIN masterguru mg ON u.id_guru = mg.id
                    WHERE u.id = ?";
        } else {
            // Fallback: no JOIN if column doesn't exist or is not INT
            $sql = "SELECT *, NULL as masterguru_nama, NULL as masterguru_nip
                    FROM users 
                    WHERE id = ?";
        }
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function count($search = '') {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (username LIKE ? OR namalengkap LIKE ? OR email LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $sql = "SELECT COUNT(*) as total FROM users WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (username, namalengkap, email, password, role, id_guru, picture, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['username'],
            $data['namalengkap'],
            $data['email'],
            $this->hashPassword($data['password']),
            $data['role'],
            $data['id_guru'] ?? null,
            $data['picture'] ?? null,
            $data['status'] ?? 'aktif'
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['username', 'namalengkap', 'email', 'role', 'id_guru', 'picture', 'status'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                // Handle null values for id_guru and picture
                if (in_array($field, ['id_guru', 'picture']) && $data[$field] === null) {
                    $params[] = null;
                } else {
                    $params[] = $data[$field];
                }
            }
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = ?";
            $params[] = $this->hashPassword($data['password']);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        try {
            $result = $this->db->query($sql, $params);
            return $result !== false;
        } catch (PDOException $e) {
            error_log("User update PDO error: " . $e->getMessage() . " | SQL: " . $sql . " | Params: " . json_encode($params));
            throw $e;
        } catch (Exception $e) {
            error_log("User update error: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}

