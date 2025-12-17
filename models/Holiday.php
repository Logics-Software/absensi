<?php
class Holiday {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find holiday by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM holiday WHERE holiday_id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Get all holidays with pagination and search
     */
    public function getAll($page = 1, $perPage = 10, $search = '', $sortBy = 'holiday_date', $sortOrder = 'ASC', $year = null) {
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND holiday_name LIKE ?";
            $params[] = "%{$search}%";
        }
        
        // Filter by year
        if ($year !== null && $year > 0) {
            $where .= " AND YEAR(holiday_date) = ?";
            $params[] = (int)$year;
        }
        
        $validSortColumns = ['holiday_id', 'holiday_date', 'holiday_name', 'is_national', 'is_recurring_yearly', 'created_at'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'holiday_date';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
        
        $sql = "SELECT * FROM holiday 
                WHERE {$where} 
                ORDER BY {$sortBy} {$sortOrder} 
                LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get holidays for calendar view (all holidays for a specific year)
     */
    public function getHolidaysForYear($year) {
        // Get all holidays (both specific year and recurring)
        $sql = "SELECT holiday_id, holiday_date, holiday_name, is_national, is_recurring_yearly 
                FROM holiday 
                ORDER BY holiday_date ASC";
        
        $allHolidays = $this->db->fetchAll($sql);
        
        // Process holidays - include exact year matches and recurring holidays
        $result = [];
        foreach ($allHolidays as $holiday) {
            $holidayYear = (int)date('Y', strtotime($holiday['holiday_date']));
            
            if ($holidayYear == $year) {
                // Exact year match
                $result[] = $holiday;
            } elseif ($holiday['is_recurring_yearly'] == 1) {
                // Recurring holiday - generate date for the requested year
                $originalDate = new DateTime($holiday['holiday_date']);
                $newDate = new DateTime($year . '-' . $originalDate->format('m-d'));
                $holiday['holiday_date'] = $newDate->format('Y-m-d');
                $result[] = $holiday;
            }
        }
        
        return $result;
    }
    
    /**
     * Get holidays for calendar (all dates as array keyed by date, with multiple holidays per date)
     */
    public function getHolidaysForCalendar($year, $month = null) {
        $holidays = $this->getHolidaysForYear($year);
        $result = [];
        
        foreach ($holidays as $holiday) {
            $date = $holiday['holiday_date'];
            // Convert to DateTime for reliable month comparison
            $dateObj = new DateTime($date);
            $holidayMonth = (int)$dateObj->format('m');
            
            if ($month === null || $holidayMonth == $month) {
                // Initialize array for this date if not exists
                if (!isset($result[$date])) {
                    $result[$date] = [];
                }
                
                // Add holiday to the array for this date
                $result[$date][] = [
                    'id' => $holiday['holiday_id'],
                    'name' => $holiday['holiday_name'],
                    'is_national' => $holiday['is_national'] == 1,
                    'is_recurring' => $holiday['is_recurring_yearly'] == 1
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Count total holidays with search
     */
    public function count($search = '', $year = null) {
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND holiday_name LIKE ?";
            $params[] = "%{$search}%";
        }
        
        // Filter by year
        if ($year !== null && $year > 0) {
            $where .= " AND YEAR(holiday_date) = ?";
            $params[] = (int)$year;
        }
        
        $sql = "SELECT COUNT(*) as total FROM holiday WHERE {$where}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Create holiday
     */
    public function create($data) {
        $sql = "INSERT INTO holiday (holiday_date, holiday_name, is_national, is_recurring_yearly) 
                VALUES (?, ?, ?, ?)";
        
        $params = [
            $data['holiday_date'] ?? null,
            $data['holiday_name'] ?? null,
            isset($data['is_national']) ? (int)$data['is_national'] : 1,
            isset($data['is_recurring_yearly']) ? (int)$data['is_recurring_yearly'] : 0
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update holiday
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['holiday_date', 'holiday_name', 'is_national', 'is_recurring_yearly'];
        
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                if (in_array($field, ['is_national', 'is_recurring_yearly'])) {
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
        $sql = "UPDATE holiday SET " . implode(', ', $fields) . " WHERE holiday_id = ?";
        
        try {
            $result = $this->db->query($sql, $params);
            return $result !== false;
        } catch (Exception $e) {
            error_log("Holiday update error: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    /**
     * Delete holiday
     */
    public function delete($id) {
        $sql = "DELETE FROM holiday WHERE holiday_id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Check if date is holiday
     */
    public function isHoliday($date) {
        $dateStr = is_string($date) ? $date : $date->format('Y-m-d');
        $year = (int)date('Y', strtotime($dateStr));
        $month = (int)date('m', strtotime($dateStr));
        $day = (int)date('d', strtotime($dateStr));
        
        // Check exact date
        $sql = "SELECT * FROM holiday WHERE holiday_date = ?";
        $exact = $this->db->fetchOne($sql, [$dateStr]);
        if ($exact) {
            return $exact;
        }
        
        // Check recurring holidays (same month and day, different year)
        $sql = "SELECT * FROM holiday WHERE is_recurring_yearly = 1 AND MONTH(holiday_date) = ? AND DAY(holiday_date) = ?";
        $recurring = $this->db->fetchOne($sql, [$month, $day]);
        if ($recurring) {
            return $recurring;
        }
        
        return false;
    }
}

