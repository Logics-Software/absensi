<?php
/**
 * Controller untuk laporan kehadiran per bulan per kelas
 */

require_once __DIR__ . '/../models/AbsensiSiswa.php';
require_once __DIR__ . '/../models/Mastersiswa.php';
require_once __DIR__ . '/../models/Kelas.php';
require_once __DIR__ . '/../models/KalenderAkademik.php';

class LaporanKehadiranController extends Controller {
    
    /**
     * Halaman index - form filter
     */
    public function index() {
        Auth::requireRole(['admin', 'guru']);
        
        $kelasModel = new Kelas();
        // Get all kelas without pagination limit
        $kelasList = $kelasModel->getAll(1, 1000, '', 'namakelas', 'ASC');
        
        // Default bulan dan tahun ke bulan/tahun saat ini
        $bulan = $_GET['bulan'] ?? date('m');
        $tahun = $_GET['tahun'] ?? date('Y');
        $idKelas = $_GET['kelas'] ?? null;
        
        $data = [
            'kelas_list' => $kelasList,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'id_kelas' => $idKelas,
            'laporan' => null
        ];
        
        // Jika kelas dipilih, generate laporan
        if ($idKelas) {
            $data['laporan'] = $this->generateLaporan($idKelas, $tahun, $bulan);
        }
        
        $this->view('laporankehadiran/index', $data);
    }
    
    /**
     * Generate laporan kehadiran
     */
    private function generateLaporan($idKelas, $tahun, $bulan) {
        $mastersiswaModel = new Mastersiswa();
        $absensiSiswaModel = new AbsensiSiswa();
        $kalenderModel = new KalenderAkademik();
        $kelasModel = new Kelas();
        
        // Get kelas info
        $kelas = $kelasModel->findById($idKelas);
        if (!$kelas) {
            return null;
        }
        
        // Get siswa dalam kelas (tanpa pagination untuk mendapatkan semua siswa)
        $siswaList = $mastersiswaModel->getAll(1, 10000, '', 'namasiswa', 'ASC', null, $idKelas);
        
        if (empty($siswaList)) {
            // Jika tidak ada siswa, return null
            return null;
        }
        
        // Get kalender akademik untuk bulan tersebut
        $kalenderList = $kalenderModel->getByMonth($tahun, $bulan);
        
        // Buat array tanggal aktif (hanya tanggal yang ada di kalender akademik)
        $tanggalAktif = [];
        foreach ($kalenderList as $kal) {
            $tanggalAktif[] = $kal['tanggal'];
        }
        
        // Jika tidak ada kalender akademik, gunakan semua hari kerja dalam bulan
        if (empty($tanggalAktif)) {
            $tanggalAktif = $this->getWorkingDays($tahun, $bulan);
        }
        
        // Get absensi untuk semua siswa dan tanggal
        $absensiData = [];
        foreach ($siswaList as $siswa) {
            if (!isset($siswa['nisn']) || empty($siswa['nisn'])) {
                continue; // Skip jika tidak ada nisn
            }
            $absensiData[$siswa['nisn']] = [];
            foreach ($tanggalAktif as $tanggal) {
                $absensi = $absensiSiswaModel->getByNisnAndDate($siswa['nisn'], $tanggal);
                if ($absensi) {
                    $absensiData[$siswa['nisn']][$tanggal] = $this->mapStatusToCode($absensi['status']);
                } else {
                    $absensiData[$siswa['nisn']][$tanggal] = 'A'; // Alpha jika tidak ada data
                }
            }
        }
        
        return [
            'kelas' => $kelas,
            'siswa_list' => $siswaList,
            'tanggal_aktif' => $tanggalAktif,
            'absensi_data' => $absensiData,
            'bulan' => $bulan,
            'tahun' => $tahun
        ];
    }
    
    /**
     * Get working days in a month (exclude weekends)
     */
    private function getWorkingDays($tahun, $bulan) {
        $tanggalAktif = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $tanggal = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);
            $dayOfWeek = date('w', strtotime($tanggal));
            
            // Exclude Sunday (0) and Saturday (6)
            if ($dayOfWeek != 0 && $dayOfWeek != 6) {
                $tanggalAktif[] = $tanggal;
            }
        }
        
        return $tanggalAktif;
    }
    
    /**
     * Map status to code
     * H = Hadir, I = Ijin, T = Terlambat, A = Alpha, P = Pulang Awal, S = Sakit
     */
    private function mapStatusToCode($status) {
        $statusMap = [
            'hadir' => 'H',
            'ijin' => 'I',
            'terlambat' => 'T',
            'tidak_hadir' => 'A',
            'alpha' => 'A',
            'pulang_awal' => 'P',
            'sakit' => 'S'
        ];
        
        return $statusMap[strtolower($status)] ?? 'A';
    }
}

