<?php
class Konfigurasi {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get konfigurasi (hanya 1 record)
     */
    public function get() {
        $sql = "SELECT k.*, 
                u.namalengkap as kepala_sekolah_nama,
                u.email as kepala_sekolah_email
                FROM konfigurasi k
                LEFT JOIN users u ON k.idkepalasekolah = u.id
                LIMIT 1";
        return $this->db->fetchOne($sql);
    }
    
    /**
     * Create or update konfigurasi (hanya 1 record)
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
     * Create konfigurasi
     */
    private function create($data) {
        $sql = "INSERT INTO konfigurasi (npsn, namasekolah, alamatsekolah, skpendirian, tanggalskpendirian, skoperasional, tanggalskoperasional, idkepalasekolah, logo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['npsn'] ?? null,
            $data['namasekolah'] ?? null,
            $data['alamatsekolah'] ?? null,
            $data['skpendirian'] ?? null,
            !empty($data['tanggalskpendirian']) ? $data['tanggalskpendirian'] : null,
            $data['skoperasional'] ?? null,
            !empty($data['tanggalskoperasional']) ? $data['tanggalskoperasional'] : null,
            !empty($data['idkepalasekolah']) ? (int)$data['idkepalasekolah'] : null,
            $data['logo'] ?? null
        ];
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Update konfigurasi
     */
    private function update($data) {
        $existing = $this->get();
        if (!$existing || !isset($existing['id'])) {
            return false;
        }
        
        $sql = "UPDATE konfigurasi SET 
                npsn = ?,
                namasekolah = ?,
                alamatsekolah = ?,
                skpendirian = ?,
                tanggalskpendirian = ?,
                skoperasional = ?,
                tanggalskoperasional = ?,
                idkepalasekolah = ?,
                logo = ?
                WHERE id = ?";
        
        $params = [
            $data['npsn'] ?? null,
            $data['namasekolah'] ?? null,
            $data['alamatsekolah'] ?? null,
            $data['skpendirian'] ?? null,
            !empty($data['tanggalskpendirian']) ? $data['tanggalskpendirian'] : null,
            $data['skoperasional'] ?? null,
            !empty($data['tanggalskoperasional']) ? $data['tanggalskoperasional'] : null,
            !empty($data['idkepalasekolah']) ? (int)$data['idkepalasekolah'] : null,
            $data['logo'] ?? null,
            $existing['id']
        ];
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Get list kepala sekolah untuk dropdown
     */
    public function getKepalaSekolahList() {
        $sql = "SELECT id, namalengkap, email FROM users WHERE role = 'kepala_sekolah' AND status = 'aktif' ORDER BY namalengkap";
        return $this->db->fetchAll($sql);
    }
}

