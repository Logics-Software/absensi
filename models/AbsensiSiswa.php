<?php
class AbsensiSiswa {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find absensi by ID
     */
    public function findById($id) {
        $sql = "SELECT a.*, 
                ms.namasiswa,
                ms.nisn as nisn_full
                FROM absensi_siswa a
                LEFT JOIN mastersiswa ms ON a.nisn = ms.nisn
                WHERE a.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Get all absensi with pagination, search, and period filter
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'id', $sortOrder = 'DESC', $period = null, $dateFrom = null, $dateTo = null) {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        // Search filter
        if (!empty($search)) {
            $where .= " AND (a.nisn LIKE ? OR ms.namasiswa LIKE ? OR a.keterangan LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        // Period filter
        if ($period !== null && $period !== 'custom') {
            $dateFilter = $this->getPeriodDateRange($period);
            if ($dateFilter) {
                $where .= " AND a.tanggalabsen >= ? AND a.tanggalabsen <= ?";
                $params[] = $dateFilter['from'];
                $params[] = $dateFilter['to'];
            }
        } elseif ($period === 'custom' && $dateFrom && $dateTo) {
            $where .= " AND a.tanggalabsen >= ? AND a.tanggalabsen <= ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }
        
        $validSortColumns = ['id', 'nisn', 'tanggalabsen', 'jammasuk', 'jamkeluar', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'id';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql = "SELECT a.*, 
                ms.namasiswa,
                ms.nisn as nisn_full
                FROM absensi_siswa a
                LEFT JOIN mastersiswa ms ON a.nisn = ms.nisn
                WHERE {$where} 
                ORDER BY a.{$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Count total absensi with search and period filter
     */
    public function count($search = '', $period = null, $dateFrom = null, $dateTo = null) {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (a.nisn LIKE ? OR ms.namasiswa LIKE ? OR a.keterangan LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        // Period filter
        if ($period !== null && $period !== 'custom') {
            $dateFilter = $this->getPeriodDateRange($period);
            if ($dateFilter) {
                $where .= " AND a.tanggalabsen >= ? AND a.tanggalabsen <= ?";
                $params[] = $dateFilter['from'];
                $params[] = $dateFilter['to'];
            }
        } elseif ($period === 'custom' && $dateFrom && $dateTo) {
            $where .= " AND a.tanggalabsen >= ? AND a.tanggalabsen <= ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }
        
        $sql = "SELECT COUNT(*) as total 
                FROM absensi_siswa a
                LEFT JOIN mastersiswa ms ON a.nisn = ms.nisn
                WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Get date range for period filter
     */
    private function getPeriodDateRange($period) {
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        switch ($period) {
            case 'today':
                return [
                    'from' => $today->format('Y-m-d'),
                    'to' => $today->format('Y-m-d')
                ];
            
            case 'this_week':
                $startOfWeek = clone $today;
                $startOfWeek->modify('monday this week');
                $endOfWeek = clone $startOfWeek;
                $endOfWeek->modify('+6 days');
                return [
                    'from' => $startOfWeek->format('Y-m-d'),
                    'to' => $endOfWeek->format('Y-m-d')
                ];
            
            case 'last_week':
                $startOfLastWeek = clone $today;
                $startOfLastWeek->modify('monday last week');
                $endOfLastWeek = clone $startOfLastWeek;
                $endOfLastWeek->modify('+6 days');
                return [
                    'from' => $startOfLastWeek->format('Y-m-d'),
                    'to' => $endOfLastWeek->format('Y-m-d')
                ];
            
            case 'this_month':
                $startOfMonth = clone $today;
                $startOfMonth->modify('first day of this month');
                $endOfMonth = clone $today;
                $endOfMonth->modify('last day of this month');
                return [
                    'from' => $startOfMonth->format('Y-m-d'),
                    'to' => $endOfMonth->format('Y-m-d')
                ];
            
            case 'last_month':
                $startOfLastMonth = clone $today;
                $startOfLastMonth->modify('first day of last month');
                $endOfLastMonth = clone $today;
                $endOfLastMonth->modify('last day of last month');
                return [
                    'from' => $startOfLastMonth->format('Y-m-d'),
                    'to' => $endOfLastMonth->format('Y-m-d')
                ];
            
            default:
                return null;
        }
    }
    
    /**
     * Create absensi
     */
    public function create($data) {
        // Calculate duration if jammasuk and jamkeluar are provided
        $durasijam = 0;
        $durasimenit = 0;
        $durasidetik = 0;
        
        if (!empty($data['jammasuk']) && !empty($data['jamkeluar'])) {
            $jamMasuk = new DateTime($data['jammasuk']);
            $jamKeluar = new DateTime($data['jamkeluar']);
            $diff = $jamMasuk->diff($jamKeluar);
            $durasijam = (int)$diff->h;
            $durasimenit = (int)$diff->i;
            $durasidetik = (int)$diff->s;
        }
        
        $sql = "INSERT INTO absensi_siswa (nisn, tanggalabsen, jammasuk, jamkeluar, durasijam, durasimenit, durasidetik, status, keterangan) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['nisn'] ?? null,
            $data['tanggalabsen'] ?? null,
            !empty($data['jammasuk']) ? $data['jammasuk'] : null,
            !empty($data['jamkeluar']) ? $data['jamkeluar'] : null,
            $durasijam,
            $durasimenit,
            $durasidetik,
            $data['status'] ?? 'hadir',
            $data['keterangan'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update absensi
     */
    public function update($id, $data) {
        // Calculate duration if jammasuk and jamkeluar are provided
        $durasijam = 0;
        $durasimenit = 0;
        $durasidetik = 0;
        
        if (!empty($data['jammasuk']) && !empty($data['jamkeluar'])) {
            $jamMasuk = new DateTime($data['jammasuk']);
            $jamKeluar = new DateTime($data['jamkeluar']);
            $diff = $jamMasuk->diff($jamKeluar);
            $durasijam = (int)$diff->h;
            $durasimenit = (int)$diff->i;
            $durasidetik = (int)$diff->s;
        }
        
        $fields = [];
        $params = [];
        
        $allowedFields = ['nisn', 'tanggalabsen', 'jammasuk', 'jamkeluar', 'status', 'keterangan'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                if ($field === 'jammasuk' || $field === 'jamkeluar') {
                    $params[] = !empty($data[$field]) ? $data[$field] : null;
                } else {
                    $params[] = $data[$field];
                }
            }
        }
        
        // Always update duration fields if jammasuk and jamkeluar are provided
        if (!empty($data['jammasuk']) && !empty($data['jamkeluar'])) {
            $fields[] = "durasijam = ?";
            $params[] = $durasijam;
            $fields[] = "durasimenit = ?";
            $params[] = $durasimenit;
            $fields[] = "durasidetik = ?";
            $params[] = $durasidetik;
        } else {
            // Reset duration if jammasuk or jamkeluar is empty
            $fields[] = "durasijam = ?";
            $params[] = 0;
            $fields[] = "durasimenit = ?";
            $params[] = 0;
            $fields[] = "durasidetik = ?";
            $params[] = 0;
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE absensi_siswa SET " . implode(', ', $fields) . " WHERE id = ?";
        
        try {
            $result = $this->db->query($sql, $params);
            return $result !== false;
        } catch (Exception $e) {
            error_log("AbsensiSiswa update error: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    /**
     * Delete absensi
     */
    public function delete($id) {
        $sql = "DELETE FROM absensi_siswa WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Get all students for dropdown
     */
    public function getAllStudents() {
        $sql = "SELECT nisn, namasiswa FROM mastersiswa WHERE status = 'aktif' ORDER BY namasiswa";
        return $this->db->fetchAll($sql, []);
    }
}

