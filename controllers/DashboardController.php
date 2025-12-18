<?php
class DashboardController extends Controller {
    public function index() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $role = $user['role'] ?? '';
        
        // Initialize default values
        $jumlahSiswaAktif = 0;
        $jumlahGuruAktif = 0;
        $activeTahunAjaran = null;
        $chartData = [];
        
        try {
            // Load models
            if (file_exists(__DIR__ . '/../models/Mastersiswa.php')) {
                require_once __DIR__ . '/../models/Mastersiswa.php';
            }
            if (file_exists(__DIR__ . '/../models/MasterGuru.php')) {
                require_once __DIR__ . '/../models/MasterGuru.php';
            }
            if (file_exists(__DIR__ . '/../models/TahunAjaran.php')) {
                require_once __DIR__ . '/../models/TahunAjaran.php';
            }
            
            if (class_exists('TahunAjaran')) {
                $tahunAjaran = new TahunAjaran();
                
                // Get active tahun ajaran
                $activeTahunAjaran = $tahunAjaran->getActive();
                $activeTahunAjaranId = $activeTahunAjaran['id'] ?? null;
                
                // Count active students in active tahun ajaran
                if ($activeTahunAjaranId) {
                    try {
                        $db = Database::getInstance();
                        $sql = "SELECT COUNT(*) as total 
                                FROM mastersiswa 
                                WHERE idtahunajaran = ? AND status = 'aktif'";
                        $result = $db->fetchOne($sql, [$activeTahunAjaranId]);
                        $jumlahSiswaAktif = (int)($result['total'] ?? 0);
                    } catch (Exception $e) {
                        error_log('Dashboard error counting students: ' . $e->getMessage());
                    }
                }
            }
            
            // Count active teachers
            try {
                $db = Database::getInstance();
                $sql = "SELECT COUNT(*) as total 
                        FROM masterguru 
                        WHERE status = 'aktif'";
                $result = $db->fetchOne($sql);
                $jumlahGuruAktif = (int)($result['total'] ?? 0);
            } catch (Exception $e) {
                error_log('Dashboard error counting teachers: ' . $e->getMessage());
            }
            
            // Get student count per tahun ajaran for last 5 years
            try {
                $db = Database::getInstance();
                $sql = "SELECT ta.id, ta.tahunajaran, COUNT(ms.id) as jumlah_siswa
                        FROM tahunajaran ta
                        LEFT JOIN mastersiswa ms ON ta.id = ms.idtahunajaran AND ms.status = 'aktif'
                        WHERE ta.status IN ('aktif', 'selesai')
                        GROUP BY ta.id, ta.tahunajaran
                        ORDER BY ta.tahunajaran DESC
                        LIMIT 5";
                $siswaPerTahunAjaran = $db->fetchAll($sql);
                
                // Prepare chart data
                if (is_array($siswaPerTahunAjaran)) {
                    foreach ($siswaPerTahunAjaran as $row) {
                        $chartData[] = [
                            'tahun' => $row['tahunajaran'] ?? '',
                            'jumlah' => (int)($row['jumlah_siswa'] ?? 0)
                        ];
                    }
                }
            } catch (Exception $e) {
                error_log('Dashboard error getting chart data: ' . $e->getMessage());
            }
        } catch (Exception $e) {
            // Log error but don't break the page
            error_log('Dashboard error: ' . $e->getMessage());
        }
        
        $data = [
            'user' => $user,
            'role' => $role,
            'stats' => [],
            'jumlahSiswaAktif' => $jumlahSiswaAktif,
            'jumlahGuruAktif' => $jumlahGuruAktif,
            'activeTahunAjaran' => $activeTahunAjaran,
            'chartData' => $chartData
        ];
        
        $this->view('dashboard/index', $data);
    }
}
