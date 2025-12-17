<?php
class KelasController extends Controller {
    public function index() {
        Auth::requireRole(['admin']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';
        $filterTahunAjaran = isset($_GET['filter_tahunajaran']) ? (int)$_GET['filter_tahunajaran'] : null;
        $sortBy = $_GET['sort_by'] ?? 'idkelas';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }
        
        // Get active tahun ajaran for default filter
        $tahunAjaranModel = new TahunAjaran();
        $activeTahunAjaran = $tahunAjaranModel->getActive();
        
        // If no filter selected, use active tahun ajaran
        if ($filterTahunAjaran === null && $activeTahunAjaran) {
            $filterTahunAjaran = (int)$activeTahunAjaran['id'];
        }
        
        // Get all tahun ajaran for dropdown
        $tahunAjaranList = $tahunAjaranModel->getAll(1, 1000) ?: [];
        
        $kelasModel = new Kelas();
        $kelasList = $kelasModel->getAll($page, $perPage, $search, $sortBy, $sortOrder, $filterTahunAjaran);
        $total = $kelasModel->count($search, $filterTahunAjaran);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'kelasList' => $kelasList,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'filterTahunAjaran' => $filterTahunAjaran,
            'tahunAjaranList' => $tahunAjaranList,
            'activeTahunAjaran' => $activeTahunAjaran,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('kelas/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin']);
        
        // Get konfigurasi for jenjang
        $konfigurasiModel = new Konfigurasi();
        $konfigurasi = $konfigurasiModel->get();
        $jenjang = !empty($konfigurasi['jenjang']) ? trim($konfigurasi['jenjang']) : 'SD';
        
        // Get active tahun ajaran
        $tahunAjaranModel = new TahunAjaran();
        $activeTahunAjaran = $tahunAjaranModel->getActive();
        
        // Get all tahun ajaran for dropdown
        $tahunAjaranList = $tahunAjaranModel->getAll(1, 1000) ?: [];
        
        // Get all jurusan for dropdown
        $jurusanModel = new Jurusan();
        $jurusanList = $jurusanModel->getAll(1, 1000) ?: [];
        
        // Get active master guru for dropdown
        $masterGuruModel = new MasterGuru();
        $guruList = $masterGuruModel->getAll(1, 1000, '', 'namaguru', 'ASC') ?: [];
        // Filter only active guru
        $guruList = array_filter($guruList, function($guru) {
            return ($guru['status'] ?? '') === 'aktif';
        });
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate and sanitize status
            $validStatuses = ['aktif', 'non aktif'];
            $status = trim($_POST['status'] ?? '');
            if (empty($status) || !in_array($status, $validStatuses)) {
                $status = 'aktif'; // Default fallback
            }
            
            $data = [
                'idtahunajaran' => !empty($_POST['idtahunajaran']) ? (int)$_POST['idtahunajaran'] : null,
                'kelas' => trim($_POST['kelas'] ?? ''),
                'idjurusan' => !empty($_POST['idjurusan']) ? (int)$_POST['idjurusan'] : null,
                'namakelas' => trim($_POST['namakelas'] ?? ''),
                'idguru' => !empty($_POST['idguru']) ? (int)$_POST['idguru'] : null,
                'status' => $status
            ];
            
            // Validate required fields
            if (empty($data['idtahunajaran'])) {
                Message::error('Tahun Ajaran wajib diisi');
                $this->redirect('/kelas/create');
            }
            
            if (empty($data['kelas'])) {
                Message::error('Kelas wajib diisi');
                $this->redirect('/kelas/create');
            }
            
            if (empty($data['namakelas'])) {
                Message::error('Nama Kelas wajib diisi');
                $this->redirect('/kelas/create');
            }
            
            $kelasModel = new Kelas();
            
            try {
                $kelasModel->create($data);
                Message::success('Kelas berhasil ditambahkan');
                $this->redirect('/kelas');
            } catch (Exception $e) {
                error_log("Error creating kelas: " . $e->getMessage());
                Message::error('Gagal menambahkan kelas. Silakan coba lagi atau hubungi administrator.');
                $this->redirect('/kelas/create');
            }
        }
        
        $data = [
            'jenjang' => $jenjang,
            'activeTahunAjaran' => $activeTahunAjaran,
            'tahunAjaranList' => $tahunAjaranList,
            'jurusanList' => $jurusanList,
            'guruList' => $guruList
        ];
        
        $this->view('kelas/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin']);
        
        $kelasModel = new Kelas();
        $kelas = $kelasModel->findById($id);
        
        if (!$kelas) {
            Message::error('Kelas tidak ditemukan');
            $this->redirect('/kelas');
        }
        
        // Get konfigurasi for jenjang
        $konfigurasiModel = new Konfigurasi();
        $konfigurasi = $konfigurasiModel->get();
        $jenjang = !empty($konfigurasi['jenjang']) ? trim($konfigurasi['jenjang']) : 'SD';
        
        // Get all tahun ajaran for dropdown
        $tahunAjaranModel = new TahunAjaran();
        $tahunAjaranList = $tahunAjaranModel->getAll(1, 1000) ?: [];
        
        // Get all jurusan for dropdown
        $jurusanModel = new Jurusan();
        $jurusanList = $jurusanModel->getAll(1, 1000) ?: [];
        
        // Get active master guru for dropdown
        $masterGuruModel = new MasterGuru();
        $guruList = $masterGuruModel->getAll(1, 1000, '', 'namaguru', 'ASC') ?: [];
        // Filter only active guru
        $guruList = array_filter($guruList, function($guru) {
            return ($guru['status'] ?? '') === 'aktif';
        });
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate and sanitize status
            $validStatuses = ['aktif', 'non aktif'];
            $status = trim($_POST['status'] ?? '');
            if (empty($status) || !in_array($status, $validStatuses)) {
                $status = 'aktif'; // Default fallback
            }
            
            $data = [
                'idtahunajaran' => !empty($_POST['idtahunajaran']) ? (int)$_POST['idtahunajaran'] : null,
                'kelas' => trim($_POST['kelas'] ?? ''),
                'idjurusan' => !empty($_POST['idjurusan']) ? (int)$_POST['idjurusan'] : null,
                'namakelas' => trim($_POST['namakelas'] ?? ''),
                'idguru' => !empty($_POST['idguru']) ? (int)$_POST['idguru'] : null,
                'status' => $status
            ];
            
            // Validate required fields
            if (empty($data['idtahunajaran'])) {
                Message::error('Tahun Ajaran wajib diisi');
                $this->redirect("/kelas/edit/{$id}");
            }
            
            if (empty($data['kelas'])) {
                Message::error('Kelas wajib diisi');
                $this->redirect("/kelas/edit/{$id}");
            }
            
            if (empty($data['namakelas'])) {
                Message::error('Nama Kelas wajib diisi');
                $this->redirect("/kelas/edit/{$id}");
            }
            
            try {
                $result = $kelasModel->update($id, $data);
                if ($result) {
                    Message::success('Kelas berhasil diupdate');
                    $this->redirect('/kelas');
                } else {
                    Message::error('Gagal mengupdate kelas. Tidak ada perubahan data.');
                    $this->redirect("/kelas/edit/{$id}");
                }
            } catch (Exception $e) {
                error_log("Error updating kelas: " . $e->getMessage());
                Message::error('Gagal mengupdate kelas. Silakan coba lagi atau hubungi administrator.');
                $this->redirect("/kelas/edit/{$id}");
            }
        }
        
        $data = [
            'kelas' => $kelas,
            'jenjang' => $jenjang,
            'tahunAjaranList' => $tahunAjaranList,
            'jurusanList' => $jurusanList,
            'guruList' => $guruList
        ];
        
        $this->view('kelas/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        $kelasModel = new Kelas();
        $kelas = $kelasModel->findById($id);
        
        if (!$kelas) {
            Message::error('Kelas tidak ditemukan');
            $this->redirect('/kelas');
        }
        
        $kelasModel->delete($id);
        Message::success('Kelas berhasil dihapus');
        $this->redirect('/kelas');
    }
}

