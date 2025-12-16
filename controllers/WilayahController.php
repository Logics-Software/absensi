<?php
class WilayahController extends Controller {
    
    public function provinsi() {
        Auth::requireRole(['admin']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'kode';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        $validPerPage = [25, 50, 100, 200, 500];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 50;
        }
        
        $wilayahModel = new Wilayah();
        $provinsi = $wilayahModel->getAllProvinsi($page, $perPage, $search, $sortBy, $sortOrder);
        $total = $wilayahModel->countProvinsi($search);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'provinsi' => $provinsi,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('wilayah/provinsi', $data);
    }
    
    public function provinsiCreate() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kode' => trim($_POST['kode'] ?? ''),
                'nama' => trim($_POST['nama'] ?? '')
            ];
            
            if (empty($data['kode']) || empty($data['nama'])) {
                Message::error('Kode dan Nama wajib diisi');
                $this->redirect('/wilayah/provinsi/create');
            }
            
            $wilayahModel = new Wilayah();
            
            if ($wilayahModel->getProvinsiByKode($data['kode'])) {
                Message::error('Kode provinsi sudah digunakan');
                $this->redirect('/wilayah/provinsi/create');
            }
            
            $wilayahModel->createProvinsi($data);
            Message::success('Provinsi berhasil ditambahkan');
            $this->redirect('/wilayah/provinsi');
        }
        
        $this->view('wilayah/provinsi_create');
    }
    
    public function provinsiEdit($id) {
        Auth::requireRole(['admin']);
        
        $wilayahModel = new Wilayah();
        $provinsi = $wilayahModel->getProvinsiById($id);
        
        if (!$provinsi) {
            Message::error('Provinsi tidak ditemukan');
            $this->redirect('/wilayah/provinsi');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kode' => trim($_POST['kode'] ?? ''),
                'nama' => trim($_POST['nama'] ?? '')
            ];
            
            if (empty($data['kode']) || empty($data['nama'])) {
                Message::error('Kode dan Nama wajib diisi');
                $this->redirect("/wilayah/provinsi/edit/{$id}");
            }
            
            $existing = $wilayahModel->getProvinsiByKode($data['kode']);
            if ($existing && $existing['id'] != $id) {
                Message::error('Kode provinsi sudah digunakan');
                $this->redirect("/wilayah/provinsi/edit/{$id}");
            }
            
            $wilayahModel->updateProvinsi($id, $data);
            Message::success('Provinsi berhasil diupdate');
            $this->redirect('/wilayah/provinsi');
        }
        
        $data = ['provinsi' => $provinsi];
        $this->view('wilayah/provinsi_edit', $data);
    }
    
    public function provinsiDelete($id) {
        Auth::requireRole(['admin']);
        
        $wilayahModel = new Wilayah();
        $provinsi = $wilayahModel->getProvinsiById($id);
        
        if (!$provinsi) {
            Message::error('Provinsi tidak ditemukan');
            $this->redirect('/wilayah/provinsi');
        }
        
        $wilayahModel->deleteProvinsi($id);
        Message::success('Provinsi berhasil dihapus');
        $this->redirect('/wilayah/provinsi');
    }
    
    // Kabupaten/Kota methods
    public function kabupaten() {
        Auth::requireRole(['admin']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
        $search = $_GET['search'] ?? '';
        $provinsiId = isset($_GET['provinsi_id']) ? (int)$_GET['provinsi_id'] : null;
        $sortBy = $_GET['sort_by'] ?? 'kode';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        $validPerPage = [25, 50, 100, 200, 500];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 50;
        }
        
        $wilayahModel = new Wilayah();
        $kabupaten = $wilayahModel->getAllKabupatenKota($page, $perPage, $search, $provinsiId, $sortBy, $sortOrder);
        $total = $wilayahModel->countKabupatenKota($search, $provinsiId);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000);
        
        $data = [
            'kabupaten' => $kabupaten,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'provinsiId' => $provinsiId,
            'provinsiList' => $provinsiList,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('wilayah/kabupaten', $data);
    }
    
    public function kabupatenCreate() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kode' => trim($_POST['kode'] ?? ''),
                'provinsi_id' => (int)($_POST['provinsi_id'] ?? 0),
                'nama' => trim($_POST['nama'] ?? ''),
                'tipe' => $_POST['tipe'] ?? 'Kabupaten'
            ];
            
            if (empty($data['kode']) || empty($data['nama']) || empty($data['provinsi_id'])) {
                Message::error('Kode, Nama, dan Provinsi wajib diisi');
                $this->redirect('/wilayah/kabupaten/create');
            }
            
            $wilayahModel = new Wilayah();
            
            if ($wilayahModel->getKabupatenKotaByKode($data['kode'])) {
                Message::error('Kode kabupaten/kota sudah digunakan');
                $this->redirect('/wilayah/kabupaten/create');
            }
            
            $wilayahModel->createKabupatenKota($data);
            Message::success('Kabupaten/Kota berhasil ditambahkan');
            $this->redirect('/wilayah/kabupaten');
        }
        
        $wilayahModel = new Wilayah();
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000);
        $data = ['provinsiList' => $provinsiList];
        $this->view('wilayah/kabupaten_create', $data);
    }
    
    public function kabupatenEdit($id) {
        Auth::requireRole(['admin']);
        
        $wilayahModel = new Wilayah();
        $kabupaten = $wilayahModel->getKabupatenKotaById($id);
        
        if (!$kabupaten) {
            Message::error('Kabupaten/Kota tidak ditemukan');
            $this->redirect('/wilayah/kabupaten');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kode' => trim($_POST['kode'] ?? ''),
                'provinsi_id' => (int)($_POST['provinsi_id'] ?? 0),
                'nama' => trim($_POST['nama'] ?? ''),
                'tipe' => $_POST['tipe'] ?? 'Kabupaten'
            ];
            
            if (empty($data['kode']) || empty($data['nama']) || empty($data['provinsi_id'])) {
                Message::error('Kode, Nama, dan Provinsi wajib diisi');
                $this->redirect("/wilayah/kabupaten/edit/{$id}");
            }
            
            $existing = $wilayahModel->getKabupatenKotaByKode($data['kode']);
            if ($existing && $existing['id'] != $id) {
                Message::error('Kode kabupaten/kota sudah digunakan');
                $this->redirect("/wilayah/kabupaten/edit/{$id}");
            }
            
            $wilayahModel->updateKabupatenKota($id, $data);
            Message::success('Kabupaten/Kota berhasil diupdate');
            $this->redirect('/wilayah/kabupaten');
        }
        
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000);
        $data = ['kabupaten' => $kabupaten, 'provinsiList' => $provinsiList];
        $this->view('wilayah/kabupaten_edit', $data);
    }
    
    public function kabupatenDelete($id) {
        Auth::requireRole(['admin']);
        
        $wilayahModel = new Wilayah();
        $kabupaten = $wilayahModel->getKabupatenKotaById($id);
        
        if (!$kabupaten) {
            Message::error('Kabupaten/Kota tidak ditemukan');
            $this->redirect('/wilayah/kabupaten');
        }
        
        $wilayahModel->deleteKabupatenKota($id);
        Message::success('Kabupaten/Kota berhasil dihapus');
        $this->redirect('/wilayah/kabupaten');
    }
    
    // Kecamatan methods
    public function kecamatan() {
        Auth::requireRole(['admin']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
        $search = $_GET['search'] ?? '';
        $provinsiId = isset($_GET['provinsi_id']) ? (int)$_GET['provinsi_id'] : null;
        $kabupatenId = isset($_GET['kabupaten_id']) ? (int)$_GET['kabupaten_id'] : null;
        $sortBy = $_GET['sort_by'] ?? 'kode';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        $validPerPage = [25, 50, 100, 200, 500];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 50;
        }
        
        $wilayahModel = new Wilayah();
        $kecamatan = $wilayahModel->getAllKecamatan($page, $perPage, $search, $kabupatenId, $sortBy, $sortOrder, $provinsiId);
        $total = $wilayahModel->countKecamatan($search, $kabupatenId, $provinsiId);
        
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000);
        
        // Get kabupaten list for filter dropdown
        $kabupatenList = [];
        if ($provinsiId) {
            // If provinsi is selected, show kabupaten from that provinsi
            $kabupatenList = $wilayahModel->getKabupatenKotaByProvinsi($provinsiId);
        } elseif ($kabupatenId) {
            // If only kabupaten is selected, get kabupaten from its provinsi
            $kabupatenItem = $wilayahModel->getKabupatenKotaById($kabupatenId);
            if ($kabupatenItem) {
                $provinsiId = $kabupatenItem['provinsi_id'];
                $kabupatenList = $wilayahModel->getKabupatenKotaByProvinsi($kabupatenItem['provinsi_id']);
            }
        }
        
        $data = [
            'kecamatan' => $kecamatan,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'provinsiId' => $provinsiId,
            'kabupatenId' => $kabupatenId,
            'provinsiList' => $provinsiList,
            'kabupatenList' => $kabupatenList,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('wilayah/kecamatan', $data);
    }
    
    public function kecamatanCreate() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kode' => trim($_POST['kode'] ?? ''),
                'kabupaten_kota_id' => (int)($_POST['kabupaten_kota_id'] ?? 0),
                'nama' => trim($_POST['nama'] ?? '')
            ];
            
            if (empty($data['kode']) || empty($data['nama']) || empty($data['kabupaten_kota_id'])) {
                Message::error('Kode, Nama, dan Kabupaten/Kota wajib diisi');
                $this->redirect('/wilayah/kecamatan/create');
            }
            
            $wilayahModel = new Wilayah();
            
            if ($wilayahModel->getKecamatanByKode($data['kode'])) {
                Message::error('Kode kecamatan sudah digunakan');
                $this->redirect('/wilayah/kecamatan/create');
            }
            
            $wilayahModel->createKecamatan($data);
            Message::success('Kecamatan berhasil ditambahkan');
            $this->redirect('/wilayah/kecamatan');
        }
        
        $wilayahModel = new Wilayah();
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000);
        $data = ['provinsiList' => $provinsiList];
        $this->view('wilayah/kecamatan_create', $data);
    }
    
    public function kecamatanEdit($id) {
        Auth::requireRole(['admin']);
        
        $wilayahModel = new Wilayah();
        $kecamatan = $wilayahModel->getKecamatanById($id);
        
        if (!$kecamatan) {
            Message::error('Kecamatan tidak ditemukan');
            $this->redirect('/wilayah/kecamatan');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kode' => trim($_POST['kode'] ?? ''),
                'kabupaten_kota_id' => (int)($_POST['kabupaten_kota_id'] ?? 0),
                'nama' => trim($_POST['nama'] ?? '')
            ];
            
            if (empty($data['kode']) || empty($data['nama']) || empty($data['kabupaten_kota_id'])) {
                Message::error('Kode, Nama, dan Kabupaten/Kota wajib diisi');
                $this->redirect("/wilayah/kecamatan/edit/{$id}");
            }
            
            $existing = $wilayahModel->getKecamatanByKode($data['kode']);
            if ($existing && $existing['id'] != $id) {
                Message::error('Kode kecamatan sudah digunakan');
                $this->redirect("/wilayah/kecamatan/edit/{$id}");
            }
            
            $wilayahModel->updateKecamatan($id, $data);
            Message::success('Kecamatan berhasil diupdate');
            $this->redirect('/wilayah/kecamatan');
        }
        
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000);
        $kabupatenList = $wilayahModel->getKabupatenKotaByProvinsi($kecamatan['provinsi_id']);
        $data = ['kecamatan' => $kecamatan, 'provinsiList' => $provinsiList, 'kabupatenList' => $kabupatenList];
        $this->view('wilayah/kecamatan_edit', $data);
    }
    
    public function kecamatanDelete($id) {
        Auth::requireRole(['admin']);
        
        $wilayahModel = new Wilayah();
        $kecamatan = $wilayahModel->getKecamatanById($id);
        
        if (!$kecamatan) {
            Message::error('Kecamatan tidak ditemukan');
            $this->redirect('/wilayah/kecamatan');
        }
        
        $wilayahModel->deleteKecamatan($id);
        Message::success('Kecamatan berhasil dihapus');
        $this->redirect('/wilayah/kecamatan');
    }
    
    // Kelurahan methods
    public function kelurahan() {
        Auth::requireRole(['admin']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
        $search = $_GET['search'] ?? '';
        $provinsiId = isset($_GET['provinsi_id']) ? (int)$_GET['provinsi_id'] : null;
        $kabupatenId = isset($_GET['kabupaten_id']) ? (int)$_GET['kabupaten_id'] : null;
        $kecamatanId = isset($_GET['kecamatan_id']) ? (int)$_GET['kecamatan_id'] : null;
        $sortBy = $_GET['sort_by'] ?? 'kode';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        $validPerPage = [25, 50, 100, 200, 500];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 50;
        }
        
        $wilayahModel = new Wilayah();
        $kelurahan = $wilayahModel->getAllKelurahan($page, $perPage, $search, $kecamatanId, $sortBy, $sortOrder, $provinsiId, $kabupatenId);
        $total = $wilayahModel->countKelurahan($search, $kecamatanId, $provinsiId, $kabupatenId);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000);
        
        // Get kabupaten list for filter dropdown
        $kabupatenList = [];
        if ($provinsiId) {
            $kabupatenList = $wilayahModel->getKabupatenKotaByProvinsi($provinsiId);
        } elseif ($kabupatenId) {
            $kabupatenItem = $wilayahModel->getKabupatenKotaById($kabupatenId);
            if ($kabupatenItem) {
                $provinsiId = $kabupatenItem['provinsi_id'];
                $kabupatenList = $wilayahModel->getKabupatenKotaByProvinsi($kabupatenItem['provinsi_id']);
            }
        }
        
        // Get kecamatan list for filter dropdown
        $kecamatanList = [];
        if ($kabupatenId) {
            $kecamatanList = $wilayahModel->getKecamatanByKabupaten($kabupatenId);
        } elseif ($kecamatanId) {
            $kecamatanItem = $wilayahModel->getKecamatanById($kecamatanId);
            if ($kecamatanItem) {
                $kabupatenId = $kecamatanItem['kabupaten_kota_id'];
                $kecamatanList = $wilayahModel->getKecamatanByKabupaten($kecamatanItem['kabupaten_kota_id']);
            }
        }
        
        $data = [
            'kelurahan' => $kelurahan,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'provinsiId' => $provinsiId,
            'kabupatenId' => $kabupatenId,
            'kecamatanId' => $kecamatanId,
            'kabupatenList' => $kabupatenList,
            'kecamatanList' => $kecamatanList,
            'provinsiList' => $provinsiList,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('wilayah/kelurahan', $data);
    }
    
    public function kelurahanCreate() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kode' => trim($_POST['kode'] ?? ''),
                'kecamatan_id' => (int)($_POST['kecamatan_id'] ?? 0),
                'nama' => trim($_POST['nama'] ?? ''),
                'tipe' => $_POST['tipe'] ?? 'Kelurahan'
            ];
            
            if (empty($data['kode']) || empty($data['nama']) || empty($data['kecamatan_id'])) {
                Message::error('Kode, Nama, dan Kecamatan wajib diisi');
                $this->redirect('/wilayah/kelurahan/create');
            }
            
            $wilayahModel = new Wilayah();
            
            if ($wilayahModel->getKelurahanByKode($data['kode'])) {
                Message::error('Kode kelurahan sudah digunakan');
                $this->redirect('/wilayah/kelurahan/create');
            }
            
            $wilayahModel->createKelurahan($data);
            Message::success('Kelurahan berhasil ditambahkan');
            $this->redirect('/wilayah/kelurahan');
        }
        
        $wilayahModel = new Wilayah();
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000);
        $data = ['provinsiList' => $provinsiList];
        $this->view('wilayah/kelurahan_create', $data);
    }
    
    public function kelurahanEdit($id) {
        Auth::requireRole(['admin']);
        
        $wilayahModel = new Wilayah();
        $kelurahan = $wilayahModel->getKelurahanById($id);
        
        if (!$kelurahan) {
            Message::error('Kelurahan tidak ditemukan');
            $this->redirect('/wilayah/kelurahan');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kode' => trim($_POST['kode'] ?? ''),
                'kecamatan_id' => (int)($_POST['kecamatan_id'] ?? 0),
                'nama' => trim($_POST['nama'] ?? ''),
                'tipe' => $_POST['tipe'] ?? 'Kelurahan'
            ];
            
            if (empty($data['kode']) || empty($data['nama']) || empty($data['kecamatan_id'])) {
                Message::error('Kode, Nama, dan Kecamatan wajib diisi');
                $this->redirect("/wilayah/kelurahan/edit/{$id}");
            }
            
            $existing = $wilayahModel->getKelurahanByKode($data['kode']);
            if ($existing && $existing['id'] != $id) {
                Message::error('Kode kelurahan sudah digunakan');
                $this->redirect("/wilayah/kelurahan/edit/{$id}");
            }
            
            $wilayahModel->updateKelurahan($id, $data);
            Message::success('Kelurahan berhasil diupdate');
            $this->redirect('/wilayah/kelurahan');
        }
        
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000);
        $kabupatenList = $wilayahModel->getKabupatenKotaByProvinsi($kelurahan['provinsi_id']);
        $kecamatanList = $wilayahModel->getKecamatanByKabupaten($kelurahan['kabupaten_kota_id']);
        $data = [
            'kelurahan' => $kelurahan,
            'provinsiList' => $provinsiList,
            'kabupatenList' => $kabupatenList,
            'kecamatanList' => $kecamatanList
        ];
        $this->view('wilayah/kelurahan_edit', $data);
    }
    
    public function kelurahanDelete($id) {
        Auth::requireRole(['admin']);
        
        $wilayahModel = new Wilayah();
        $kelurahan = $wilayahModel->getKelurahanById($id);
        
        if (!$kelurahan) {
            Message::error('Kelurahan tidak ditemukan');
            $this->redirect('/wilayah/kelurahan');
        }
        
        $wilayahModel->deleteKelurahan($id);
        Message::success('Kelurahan berhasil dihapus');
        $this->redirect('/wilayah/kelurahan');
    }
    
    // API endpoints for AJAX
    public function apiKabupaten() {
        Auth::requireRole(['admin']);
        
        $provinsiId = isset($_GET['provinsi_id']) ? (int)$_GET['provinsi_id'] : 0;
        
        $wilayahModel = new Wilayah();
        $kabupaten = $wilayahModel->getKabupatenKotaByProvinsi($provinsiId);
        
        header('Content-Type: application/json');
        echo json_encode($kabupaten);
        exit;
    }
    
    public function apiKecamatan() {
        Auth::requireRole(['admin']);
        
        $kabupatenId = isset($_GET['kabupaten_id']) ? (int)$_GET['kabupaten_id'] : 0;
        
        $wilayahModel = new Wilayah();
        $kecamatan = $wilayahModel->getKecamatanByKabupaten($kabupatenId);
        
        header('Content-Type: application/json');
        echo json_encode($kecamatan);
        exit;
    }
    
    public function apiKelurahan() {
        Auth::requireRole(['admin']);
        
        $kecamatanId = isset($_GET['kecamatan_id']) ? (int)$_GET['kecamatan_id'] : 0;
        
        $wilayahModel = new Wilayah();
        $kelurahan = $wilayahModel->getKelurahanByKecamatan($kecamatanId);
        
        header('Content-Type: application/json');
        echo json_encode($kelurahan);
        exit;
    }
}
