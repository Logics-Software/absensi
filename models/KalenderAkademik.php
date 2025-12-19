<?php
class KalenderAkademik {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get kalender akademik by tanggal
     */
    public function getByTanggal($tanggal) {
        $sql = "SELECT * FROM kalenderakademik WHERE tanggal = ?";
        return $this->db->fetchOne($sql, [$tanggal]);
    }
    
    /**
     * Get all kalender akademik for a month
     */
    public function getByMonth($year, $month) {
        $sql = "SELECT * FROM kalenderakademik 
                WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ? 
                ORDER BY tanggal ASC";
        return $this->db->fetchAll($sql, [$year, $month]);
    }
    
    /**
     * Get all holidays for a month (returns array with date as key and holiday_name as value)
     */
    public function getHolidaysByMonth($year, $month) {
        // Get exact date matches
        $sql = "SELECT holiday_date, holiday_name FROM holiday 
                WHERE YEAR(holiday_date) = ? AND MONTH(holiday_date) = ? 
                ORDER BY holiday_date ASC";
        $exactHolidays = $this->db->fetchAll($sql, [$year, $month]);
        
        // Get recurring holidays (same month and day, different year)
        $sql = "SELECT holiday_date, holiday_name, is_recurring_yearly FROM holiday 
                WHERE is_recurring_yearly = 1 AND MONTH(holiday_date) = ? 
                ORDER BY holiday_date ASC";
        $recurringHolidays = $this->db->fetchAll($sql, [$month]);
        
        $result = [];
        
        // Add exact date matches
        foreach ($exactHolidays as $holiday) {
            $result[$holiday['holiday_date']] = $holiday['holiday_name'];
        }
        
        // Add recurring holidays for the requested year
        foreach ($recurringHolidays as $holiday) {
            $originalDate = new DateTime($holiday['holiday_date']);
            $newDate = new DateTime($year . '-' . $originalDate->format('m-d'));
            $result[$newDate->format('Y-m-d')] = $holiday['holiday_name'];
        }
        
        return $result;
    }
    
    /**
     * Save or update kalender akademik
     */
    public function save($data) {
        $existing = $this->getByTanggal($data['tanggal']);
        
        if ($existing) {
            return $this->update($data['tanggal'], $data);
        } else {
            return $this->create($data);
        }
    }
    
    /**
     * Create kalender akademik
     */
    private function create($data) {
        $sql = "INSERT INTO kalenderakademik (tanggal, jammasuk, jamkeluar, keterangan) 
                VALUES (?, ?, ?, ?)";
        
        $params = [
            $data['tanggal'],
            !empty($data['jammasuk']) ? $data['jammasuk'] : null,
            !empty($data['jamkeluar']) ? $data['jamkeluar'] : null,
            !empty($data['keterangan']) ? $data['keterangan'] : null
        ];
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Update kalender akademik
     */
    private function update($tanggal, $data) {
        $sql = "UPDATE kalenderakademik SET 
                jammasuk = ?,
                jamkeluar = ?,
                keterangan = ?
                WHERE tanggal = ?";
        
        $params = [
            !empty($data['jammasuk']) ? $data['jammasuk'] : null,
            !empty($data['jamkeluar']) ? $data['jamkeluar'] : null,
            !empty($data['keterangan']) ? $data['keterangan'] : null,
            $tanggal
        ];
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Delete kalender akademik by tanggal
     */
    public function delete($tanggal) {
        $sql = "DELETE FROM kalenderakademik WHERE tanggal = ?";
        return $this->db->query($sql, [$tanggal]);
    }
    
    /**
     * Delete all kalender akademik for a month
     */
    public function deleteByMonth($year, $month) {
        $sql = "DELETE FROM kalenderakademik 
                WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?";
        return $this->db->query($sql, [$year, $month]);
    }
}

