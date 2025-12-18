<?php
class AbsensiSiswaController extends Controller {
    public function index() {
        Auth::requireRole(['admin']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';
        $period = $_GET['period'] ?? null;
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }
        
        $absensiSiswaModel = new AbsensiSiswa();
        $absensiList = $absensiSiswaModel->getAll($page, $perPage, $search, $sortBy, $sortOrder, $period, $dateFrom, $dateTo);
        $total = $absensiSiswaModel->count($search, $period, $dateFrom, $dateTo);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'absensiList' => $absensiList,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('absensisiswa/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin']);
        
        $absensiSiswaModel = new AbsensiSiswa();
        $studentsList = $absensiSiswaModel->getAllStudents();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate and sanitize status
            $validStatuses = ['hadir', 'alpha', 'ijin', 'sakit'];
            $status = trim($_POST['status'] ?? 'hadir');
            if (!in_array($status, $validStatuses)) {
                $status = 'hadir';
            }
            
            // Format time to HH:MM:SS (add :00 seconds if only HH:MM provided)
            $jammasuk = !empty($_POST['jammasuk']) ? $_POST['jammasuk'] : null;
            if ($jammasuk && strlen($jammasuk) == 5) {
                $jammasuk .= ':00';
            }
            
            $jamkeluar = !empty($_POST['jamkeluar']) ? $_POST['jamkeluar'] : null;
            if ($jamkeluar && strlen($jamkeluar) == 5) {
                $jamkeluar .= ':00';
            }
            
            $data = [
                'nisn' => trim($_POST['nisn'] ?? ''),
                'tanggalabsen' => $_POST['tanggalabsen'] ?? null,
                'jammasuk' => $jammasuk,
                'jamkeluar' => $jamkeluar,
                'status' => $status,
                'keterangan' => trim($_POST['keterangan'] ?? '')
            ];
            
            // Validate required fields
            if (empty($data['nisn'])) {
                Message::error('NISN wajib diisi');
                $this->redirect('/absensisiswa/create');
            }
            
            if (empty($data['tanggalabsen'])) {
                Message::error('Tanggal Absen wajib diisi');
                $this->redirect('/absensisiswa/create');
            }
            
            try {
                $absensiSiswaModel->create($data);
                Message::success('Data Absensi Siswa berhasil ditambahkan');
                $this->redirect('/absensisiswa');
            } catch (Exception $e) {
                error_log("Error creating absensi siswa: " . $e->getMessage());
                Message::error('Gagal menambahkan data. Silakan coba lagi atau hubungi administrator.');
                $this->redirect('/absensisiswa/create');
            }
        }
        
        $data = [
            'studentsList' => $studentsList
        ];
        
        $this->view('absensisiswa/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin']);
        
        $absensiSiswaModel = new AbsensiSiswa();
        $absensi = $absensiSiswaModel->findById($id);
        
        if (!$absensi) {
            Message::error('Data Absensi Siswa tidak ditemukan');
            $this->redirect('/absensisiswa');
        }
        
        $studentsList = $absensiSiswaModel->getAllStudents();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate and sanitize status
            $validStatuses = ['hadir', 'alpha', 'ijin', 'sakit'];
            $status = trim($_POST['status'] ?? 'hadir');
            if (!in_array($status, $validStatuses)) {
                $status = 'hadir';
            }
            
            // Format time to HH:MM:SS (add :00 seconds if only HH:MM provided)
            $jammasuk = !empty($_POST['jammasuk']) ? $_POST['jammasuk'] : null;
            if ($jammasuk && strlen($jammasuk) == 5) {
                $jammasuk .= ':00';
            }
            
            $jamkeluar = !empty($_POST['jamkeluar']) ? $_POST['jamkeluar'] : null;
            if ($jamkeluar && strlen($jamkeluar) == 5) {
                $jamkeluar .= ':00';
            }
            
            $data = [
                'nisn' => trim($_POST['nisn'] ?? ''),
                'tanggalabsen' => $_POST['tanggalabsen'] ?? null,
                'jammasuk' => $jammasuk,
                'jamkeluar' => $jamkeluar,
                'status' => $status,
                'keterangan' => trim($_POST['keterangan'] ?? '')
            ];
            
            // Validate required fields
            if (empty($data['nisn'])) {
                Message::error('NISN wajib diisi');
                $this->redirect("/absensisiswa/edit/{$id}");
            }
            
            if (empty($data['tanggalabsen'])) {
                Message::error('Tanggal Absen wajib diisi');
                $this->redirect("/absensisiswa/edit/{$id}");
            }
            
            try {
                $result = $absensiSiswaModel->update($id, $data);
                if ($result) {
                    Message::success('Data Absensi Siswa berhasil diupdate');
                    $this->redirect('/absensisiswa');
                } else {
                    Message::error('Gagal mengupdate data. Tidak ada perubahan data.');
                    $this->redirect("/absensisiswa/edit/{$id}");
                }
            } catch (Exception $e) {
                error_log("Error updating absensi siswa: " . $e->getMessage());
                Message::error('Gagal mengupdate data. Silakan coba lagi atau hubungi administrator.');
                $this->redirect("/absensisiswa/edit/{$id}");
            }
        }
        
        $data = [
            'absensi' => $absensi,
            'studentsList' => $studentsList
        ];
        
        $this->view('absensisiswa/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        $absensiSiswaModel = new AbsensiSiswa();
        $absensi = $absensiSiswaModel->findById($id);
        
        if (!$absensi) {
            Message::error('Data Absensi Siswa tidak ditemukan');
            $this->redirect('/absensisiswa');
        }
        
        $absensiSiswaModel->delete($id);
        Message::success('Data Absensi Siswa berhasil dihapus');
        $this->redirect('/absensisiswa');
    }
}

