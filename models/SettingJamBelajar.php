<?php
class SettingJamBelajar {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get setting jam belajar (hanya 1 record)
     */
    public function get() {
        $sql = "SELECT * FROM setting_jam_belajar LIMIT 1";
        return $this->db->fetchOne($sql);
    }
    
    /**
     * Create or update setting jam belajar (hanya 1 record)
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
     * Create setting jam belajar
     */
    private function create($data) {
        $sql = "INSERT INTO setting_jam_belajar (
                    senin, jammasuksenin, jampulangsenin,
                    selasa, jammasukselasa, jampulangselasa,
                    rabu, jammasukrabu, jampulangrabu,
                    kamis, jammasukkamis, jampulangkamis,
                    jumat, jammasukjumat, jampulangjumat,
                    sabtu, jammasuksabtu, jampulangsabtu,
                    minggu, jammasukminggu, jampulangminggu
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['senin'] ?? 'nonaktif',
            !empty($data['jammasuksenin']) ? $data['jammasuksenin'] : null,
            !empty($data['jampulangsenin']) ? $data['jampulangsenin'] : null,
            $data['selasa'] ?? 'nonaktif',
            !empty($data['jammasukselasa']) ? $data['jammasukselasa'] : null,
            !empty($data['jampulangselasa']) ? $data['jampulangselasa'] : null,
            $data['rabu'] ?? 'nonaktif',
            !empty($data['jammasukrabu']) ? $data['jammasukrabu'] : null,
            !empty($data['jampulangrabu']) ? $data['jampulangrabu'] : null,
            $data['kamis'] ?? 'nonaktif',
            !empty($data['jammasukkamis']) ? $data['jammasukkamis'] : null,
            !empty($data['jampulangkamis']) ? $data['jampulangkamis'] : null,
            $data['jumat'] ?? 'nonaktif',
            !empty($data['jammasukjumat']) ? $data['jammasukjumat'] : null,
            !empty($data['jampulangjumat']) ? $data['jampulangjumat'] : null,
            $data['sabtu'] ?? 'nonaktif',
            !empty($data['jammasuksabtu']) ? $data['jammasuksabtu'] : null,
            !empty($data['jampulangsabtu']) ? $data['jampulangsabtu'] : null,
            $data['minggu'] ?? 'nonaktif',
            !empty($data['jammasukminggu']) ? $data['jammasukminggu'] : null,
            !empty($data['jampulangminggu']) ? $data['jampulangminggu'] : null
        ];
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Update setting jam belajar
     */
    private function update($data) {
        $existing = $this->get();
        if (!$existing || !isset($existing['id'])) {
            return false;
        }
        
        $sql = "UPDATE setting_jam_belajar SET 
                senin = ?,
                jammasuksenin = ?,
                jampulangsenin = ?,
                selasa = ?,
                jammasukselasa = ?,
                jampulangselasa = ?,
                rabu = ?,
                jammasukrabu = ?,
                jampulangrabu = ?,
                kamis = ?,
                jammasukkamis = ?,
                jampulangkamis = ?,
                jumat = ?,
                jammasukjumat = ?,
                jampulangjumat = ?,
                sabtu = ?,
                jammasuksabtu = ?,
                jampulangsabtu = ?,
                minggu = ?,
                jammasukminggu = ?,
                jampulangminggu = ?
                WHERE id = ?";
        
        $params = [
            $data['senin'] ?? 'nonaktif',
            !empty($data['jammasuksenin']) ? $data['jammasuksenin'] : null,
            !empty($data['jampulangsenin']) ? $data['jampulangsenin'] : null,
            $data['selasa'] ?? 'nonaktif',
            !empty($data['jammasukselasa']) ? $data['jammasukselasa'] : null,
            !empty($data['jampulangselasa']) ? $data['jampulangselasa'] : null,
            $data['rabu'] ?? 'nonaktif',
            !empty($data['jammasukrabu']) ? $data['jammasukrabu'] : null,
            !empty($data['jampulangrabu']) ? $data['jampulangrabu'] : null,
            $data['kamis'] ?? 'nonaktif',
            !empty($data['jammasukkamis']) ? $data['jammasukkamis'] : null,
            !empty($data['jampulangkamis']) ? $data['jampulangkamis'] : null,
            $data['jumat'] ?? 'nonaktif',
            !empty($data['jammasukjumat']) ? $data['jammasukjumat'] : null,
            !empty($data['jampulangjumat']) ? $data['jampulangjumat'] : null,
            $data['sabtu'] ?? 'nonaktif',
            !empty($data['jammasuksabtu']) ? $data['jammasuksabtu'] : null,
            !empty($data['jampulangsabtu']) ? $data['jampulangsabtu'] : null,
            $data['minggu'] ?? 'nonaktif',
            !empty($data['jammasukminggu']) ? $data['jammasukminggu'] : null,
            !empty($data['jampulangminggu']) ? $data['jampulangminggu'] : null,
            $existing['id']
        ];
        
        return $this->db->query($sql, $params);
    }
}

