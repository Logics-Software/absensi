<?php
class DashboardController extends Controller {
    public function index() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $role = $user['role'] ?? '';
        
        // Load models
        require_once __DIR__ . '/../models/MasterSiswa.php';
        require_once __DIR__ . '/../models/MasterGuru.php';
        require_once __DIR__ . '/../models/TahunAjaran.php';
        
        $masterSiswa = new Mastersiswa();
        $masterGuru = new MasterGuru();
        $tahunAjaran = new TahunAjaran();
        
        // Get active tahun ajaran
        $activeTahunAjaran = $tahunAjaran->getActive();
        $activeTahunAjaranId = $activeTahunAjaran['id'] ?? null;
        
        // Count active students in active tahun ajaran
        $jumlahSiswaAktif = 0;
        if ($activeTahunAjaranId) {
            $sql = "SELECT COUNT(*) as total 
                    FROM mastersiswa 
                    WHERE idtahunajaran = ? AND status = 'aktif'";
            $result = Database::getInstance()->fetchOne($sql, [$activeTahunAjaranId]);
            $jumlahSiswaAktif = $result['total'] ?? 0;
        }
        
        // Count active teachers
        $sql = "SELECT COUNT(*) as total 
                FROM masterguru 
                WHERE status = 'aktif'";
        $result = Database::getInstance()->fetchOne($sql);
        $jumlahGuruAktif = $result['total'] ?? 0;
        
        // Get student count per tahun ajaran for last 5 years
        $sql = "SELECT ta.id, ta.tahunajaran, COUNT(ms.id) as jumlah_siswa
                FROM tahunajaran ta
                LEFT JOIN mastersiswa ms ON ta.id = ms.idtahunajaran AND ms.status = 'aktif'
                WHERE ta.status IN ('aktif', 'selesai')
                GROUP BY ta.id, ta.tahunajaran
                ORDER BY ta.tahunajaran DESC
                LIMIT 5";
        $siswaPerTahunAjaran = Database::getInstance()->fetchAll($sql);
        
        // Prepare chart data
        $chartData = [];
        foreach ($siswaPerTahunAjaran as $row) {
            $chartData[] = [
                'tahun' => $row['tahunajaran'],
                'jumlah' => (int)($row['jumlah_siswa'] ?? 0)
            ];
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
