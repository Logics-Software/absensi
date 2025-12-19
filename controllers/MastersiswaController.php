<?php
class MastersiswaController extends Controller {
    public function index() {
        Auth::requireRole(['admin']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';
        $filterTahunAjaran = isset($_GET['filter_tahunajaran']) ? (int)$_GET['filter_tahunajaran'] : null;
        $filterKelas = isset($_GET['filter_kelas']) ? (int)$_GET['filter_kelas'] : null;
        $sortBy = $_GET['sort_by'] ?? 'id';
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
        
        // Get kelas list based on selected tahun ajaran
        $kelasList = [];
        if ($filterTahunAjaran) {
            $mastersiswaModel = new Mastersiswa();
            $kelasList = $mastersiswaModel->getKelasByTahunAjaran($filterTahunAjaran);
        }
        
        $mastersiswaModel = new Mastersiswa();
        $mastersiswaList = $mastersiswaModel->getAll($page, $perPage, $search, $sortBy, $sortOrder, $filterTahunAjaran, $filterKelas);
        $total = $mastersiswaModel->count($search, $filterTahunAjaran, $filterKelas);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'mastersiswaList' => $mastersiswaList,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'filterTahunAjaran' => $filterTahunAjaran,
            'filterKelas' => $filterKelas,
            'tahunAjaranList' => $tahunAjaranList,
            'kelasList' => $kelasList,
            'activeTahunAjaran' => $activeTahunAjaran,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('mastersiswa/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin']);
        
        // Get wilayah data for dropdowns
        $wilayahModel = new Wilayah();
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000) ?: [];
        
        // Get active tahun ajaran
        $tahunAjaranModel = new TahunAjaran();
        $activeTahunAjaran = $tahunAjaranModel->getActive();
        
        // Get all tahun ajaran for dropdown
        $tahunAjaranList = $tahunAjaranModel->getAll(1, 1000) ?: [];
        
        // Get kelas list based on active tahun ajaran
        $kelasList = [];
        if ($activeTahunAjaran) {
            $mastersiswaModel = new Mastersiswa();
            $kelasList = $mastersiswaModel->getKelasByTahunAjaran($activeTahunAjaran['id']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Convert tanggal lahir from DD/MM/YYYY to YYYY-MM-DD if needed
            $tanggallahir = $_POST['tanggallahir'] ?? null;
            if (!empty($tanggallahir) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $tanggallahir)) {
                $parts = explode('/', $tanggallahir);
                $tanggallahir = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
            
            // Validate and sanitize status
            $validStatuses = ['aktif', 'non aktif'];
            $status = trim($_POST['status'] ?? '');
            if (empty($status) || !in_array($status, $validStatuses)) {
                $status = 'aktif'; // Default fallback
            }
            
            // Validate and sanitize hubungan
            $validHubungan = ['orangtua', 'saudara', 'lainlain'];
            $hubungan = trim($_POST['hubungan'] ?? '');
            if (empty($hubungan) || !in_array($hubungan, $validHubungan)) {
                $hubungan = null;
            }
            
            $data = [
                'nisn' => trim($_POST['nisn'] ?? ''),
                'nik' => trim($_POST['nik'] ?? ''),
                'noabsensi' => trim($_POST['noabsensi'] ?? ''),
                'namasiswa' => trim($_POST['namasiswa'] ?? ''),
                'jeniskelamin' => $_POST['jeniskelamin'] ?? null,
                'tempatlahir' => trim($_POST['tempatlahir'] ?? ''),
                'tanggallahir' => !empty($tanggallahir) ? $tanggallahir : null,
                'email' => trim($_POST['email'] ?? ''),
                'nomorhp' => !empty($_POST['nomorhp_full']) ? trim($_POST['nomorhp_full']) : (!empty($_POST['nomorhp']) ? '+62' . trim($_POST['nomorhp']) : ''),
                'idprovinsi' => !empty($_POST['idprovinsi']) ? (int)$_POST['idprovinsi'] : null,
                'idkabupaten' => !empty($_POST['idkabupaten']) ? (int)$_POST['idkabupaten'] : null,
                'idkecamatan' => !empty($_POST['idkecamatan']) ? (int)$_POST['idkecamatan'] : null,
                'idkelurahan' => !empty($_POST['idkelurahan']) ? (int)$_POST['idkelurahan'] : null,
                'alamatsiswa' => trim($_POST['alamatsiswa'] ?? ''),
                'idtahunajaran' => !empty($_POST['idtahunajaran']) ? (int)$_POST['idtahunajaran'] : null,
                'idkelas' => !empty($_POST['idkelas']) ? (int)$_POST['idkelas'] : null,
                'namawali' => trim($_POST['namawali'] ?? ''),
                'hubungan' => $hubungan,
                'nomorhpwali' => !empty($_POST['nomorhpwali_full']) ? trim($_POST['nomorhpwali_full']) : (!empty($_POST['nomorhpwali']) ? '+62' . trim($_POST['nomorhpwali']) : ''),
                'idprovinsiwali' => !empty($_POST['idprovinsiwali']) ? (int)$_POST['idprovinsiwali'] : null,
                'idkabupatenwali' => !empty($_POST['idkabupatenwali']) ? (int)$_POST['idkabupatenwali'] : null,
                'idkecamatanwali' => !empty($_POST['idkecamatanwali']) ? (int)$_POST['idkecamatanwali'] : null,
                'idkelurahanwali' => !empty($_POST['idkelurahanwali']) ? (int)$_POST['idkelurahanwali'] : null,
                'alamatwali' => trim($_POST['alamatwali'] ?? ''),
                'foto' => null,
                'status' => $status
            ];
            
            // Validate required fields
            if (empty($data['namasiswa'])) {
                Message::error('Nama Siswa wajib diisi');
                $this->redirect('/mastersiswa/create');
            }
            
            $mastersiswaModel = new Mastersiswa();
            
            // Check if NISN exists (if provided)
            if (!empty($data['nisn'])) {
                $existing = $mastersiswaModel->findByNisn($data['nisn']);
                if ($existing) {
                    Message::error('NISN sudah digunakan');
                    $this->redirect('/mastersiswa/create');
                }
            }
            
            // Handle foto upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                try {
                    $data['foto'] = $this->uploadFoto($_FILES['foto']);
                } catch (Exception $e) {
                    Message::error($e->getMessage());
                    $this->redirect('/mastersiswa/create');
                }
            }
            
            try {
                $mastersiswaModel->create($data);
                Message::success('Data Master Siswa berhasil ditambahkan');
                $this->redirect('/mastersiswa');
            } catch (Exception $e) {
                error_log("Error creating mastersiswa: " . $e->getMessage());
                Message::error('Gagal menambahkan data. Silakan coba lagi atau hubungi administrator.');
                $this->redirect('/mastersiswa/create');
            }
        }
        
        $data = [
            'provinsiList' => $provinsiList,
            'activeTahunAjaran' => $activeTahunAjaran,
            'tahunAjaranList' => $tahunAjaranList,
            'kelasList' => $kelasList
        ];
        
        $this->view('mastersiswa/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin']);
        
        $mastersiswaModel = new Mastersiswa();
        $mastersiswa = $mastersiswaModel->findById($id);
        
        if (!$mastersiswa) {
            Message::error('Data Master Siswa tidak ditemukan');
            $this->redirect('/mastersiswa');
        }
        
        // Get wilayah data for dropdowns
        $wilayahModel = new Wilayah();
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000) ?: [];
        
        // Get kabupaten, kecamatan, kelurahan based on selected values
        $kabupatenList = [];
        $kecamatanList = [];
        $kelurahanList = [];
        
        if (!empty($mastersiswa['idprovinsi'])) {
            $kabupatenList = $wilayahModel->getKabupatenKotaByProvinsi($mastersiswa['idprovinsi']) ?: [];
        }
        if (!empty($mastersiswa['idkabupaten'])) {
            $kecamatanList = $wilayahModel->getKecamatanByKabupaten($mastersiswa['idkabupaten']) ?: [];
        }
        if (!empty($mastersiswa['idkecamatan'])) {
            $kelurahanList = $wilayahModel->getKelurahanByKecamatan($mastersiswa['idkecamatan']) ?: [];
        }
        
        // Get kabupaten, kecamatan, kelurahan for wali
        $kabupatenWaliList = [];
        $kecamatanWaliList = [];
        $kelurahanWaliList = [];
        
        if (!empty($mastersiswa['idprovinsiwali'])) {
            $kabupatenWaliList = $wilayahModel->getKabupatenKotaByProvinsi($mastersiswa['idprovinsiwali']) ?: [];
        }
        if (!empty($mastersiswa['idkabupatenwali'])) {
            $kecamatanWaliList = $wilayahModel->getKecamatanByKabupaten($mastersiswa['idkabupatenwali']) ?: [];
        }
        if (!empty($mastersiswa['idkecamatanwali'])) {
            $kelurahanWaliList = $wilayahModel->getKelurahanByKecamatan($mastersiswa['idkecamatanwali']) ?: [];
        }
        
        // Get all tahun ajaran for dropdown
        $tahunAjaranModel = new TahunAjaran();
        $tahunAjaranList = $tahunAjaranModel->getAll(1, 1000) ?: [];
        
        // Get kelas list based on selected tahun ajaran
        $kelasList = [];
        if (!empty($mastersiswa['idtahunajaran'])) {
            $kelasList = $mastersiswaModel->getKelasByTahunAjaran($mastersiswa['idtahunajaran']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Convert tanggal lahir from DD/MM/YYYY to YYYY-MM-DD if needed
            $tanggallahir = $_POST['tanggallahir'] ?? null;
            if (!empty($tanggallahir) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $tanggallahir)) {
                $parts = explode('/', $tanggallahir);
                $tanggallahir = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
            
            // Validate and sanitize status
            $validStatuses = ['aktif', 'non aktif'];
            $status = trim($_POST['status'] ?? '');
            if (empty($status) || !in_array($status, $validStatuses)) {
                $status = 'aktif'; // Default fallback
            }
            
            // Validate and sanitize hubungan
            $validHubungan = ['orangtua', 'saudara', 'lainlain'];
            $hubungan = trim($_POST['hubungan'] ?? '');
            if (empty($hubungan) || !in_array($hubungan, $validHubungan)) {
                $hubungan = null;
            }
            
            $data = [
                'nisn' => trim($_POST['nisn'] ?? ''),
                'nik' => trim($_POST['nik'] ?? ''),
                'noabsensi' => trim($_POST['noabsensi'] ?? ''),
                'namasiswa' => trim($_POST['namasiswa'] ?? ''),
                'jeniskelamin' => $_POST['jeniskelamin'] ?? null,
                'tempatlahir' => trim($_POST['tempatlahir'] ?? ''),
                'tanggallahir' => !empty($tanggallahir) ? $tanggallahir : null,
                'email' => trim($_POST['email'] ?? ''),
                'nomorhp' => !empty($_POST['nomorhp_full']) ? trim($_POST['nomorhp_full']) : (!empty($_POST['nomorhp']) ? '+62' . trim($_POST['nomorhp']) : ''),
                'idprovinsi' => !empty($_POST['idprovinsi']) ? (int)$_POST['idprovinsi'] : null,
                'idkabupaten' => !empty($_POST['idkabupaten']) ? (int)$_POST['idkabupaten'] : null,
                'idkecamatan' => !empty($_POST['idkecamatan']) ? (int)$_POST['idkecamatan'] : null,
                'idkelurahan' => !empty($_POST['idkelurahan']) ? (int)$_POST['idkelurahan'] : null,
                'alamatsiswa' => trim($_POST['alamatsiswa'] ?? ''),
                'idtahunajaran' => !empty($_POST['idtahunajaran']) ? (int)$_POST['idtahunajaran'] : null,
                'idkelas' => !empty($_POST['idkelas']) ? (int)$_POST['idkelas'] : null,
                'namawali' => trim($_POST['namawali'] ?? ''),
                'hubungan' => $hubungan,
                'nomorhpwali' => !empty($_POST['nomorhpwali_full']) ? trim($_POST['nomorhpwali_full']) : (!empty($_POST['nomorhpwali']) ? '+62' . trim($_POST['nomorhpwali']) : ''),
                'idprovinsiwali' => !empty($_POST['idprovinsiwali']) ? (int)$_POST['idprovinsiwali'] : null,
                'idkabupatenwali' => !empty($_POST['idkabupatenwali']) ? (int)$_POST['idkabupatenwali'] : null,
                'idkecamatanwali' => !empty($_POST['idkecamatanwali']) ? (int)$_POST['idkecamatanwali'] : null,
                'idkelurahanwali' => !empty($_POST['idkelurahanwali']) ? (int)$_POST['idkelurahanwali'] : null,
                'alamatwali' => trim($_POST['alamatwali'] ?? ''),
                'status' => $status
            ];
            
            // Validate required fields
            if (empty($data['namasiswa'])) {
                Message::error('Nama Siswa wajib diisi');
                $this->redirect("/mastersiswa/edit/{$id}");
            }
            
            // Check NISN uniqueness (except current record)
            if (!empty($data['nisn'])) {
                $existing = $mastersiswaModel->findByNisn($data['nisn']);
                if ($existing && $existing['id'] != $id) {
                    Message::error('NISN sudah digunakan');
                    $this->redirect("/mastersiswa/edit/{$id}");
                }
            }
            
            // Handle foto upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Delete old foto if exists
                    if ($mastersiswa['foto']) {
                        $oldFotoPath = __DIR__ . '/../uploads/' . $mastersiswa['foto'];
                        if (file_exists($oldFotoPath)) {
                            unlink($oldFotoPath);
                        }
                    }
                    $data['foto'] = $this->uploadFoto($_FILES['foto']);
                } catch (Exception $e) {
                    Message::error($e->getMessage());
                    $this->redirect("/mastersiswa/edit/{$id}");
                }
            } else {
                // Retain existing foto if no new file uploaded
                $data['foto'] = $mastersiswa['foto'] ?? null;
            }
            
            try {
                $result = $mastersiswaModel->update($id, $data);
                if ($result) {
                    Message::success('Data Master Siswa berhasil diupdate');
                    $this->redirect('/mastersiswa');
                } else {
                    Message::error('Gagal mengupdate data. Tidak ada perubahan data.');
                    $this->redirect("/mastersiswa/edit/{$id}");
                }
            } catch (Exception $e) {
                error_log("Error updating mastersiswa: " . $e->getMessage());
                Message::error('Gagal mengupdate data. Silakan coba lagi atau hubungi administrator.');
                $this->redirect("/mastersiswa/edit/{$id}");
            }
        }
        
        $data = [
            'mastersiswa' => $mastersiswa,
            'provinsiList' => $provinsiList,
            'kabupatenList' => $kabupatenList,
            'kecamatanList' => $kecamatanList,
            'kelurahanList' => $kelurahanList,
            'kabupatenWaliList' => $kabupatenWaliList,
            'kecamatanWaliList' => $kecamatanWaliList,
            'kelurahanWaliList' => $kelurahanWaliList,
            'tahunAjaranList' => $tahunAjaranList,
            'kelasList' => $kelasList
        ];
        
        $this->view('mastersiswa/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        $mastersiswaModel = new Mastersiswa();
        $mastersiswa = $mastersiswaModel->findById($id);
        
        if (!$mastersiswa) {
            Message::error('Data Master Siswa tidak ditemukan');
            $this->redirect('/mastersiswa');
        }
        
        // Delete foto
        if ($mastersiswa['foto']) {
            $fotoPath = __DIR__ . '/../uploads/' . $mastersiswa['foto'];
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }
        
        $mastersiswaModel->delete($id);
        Message::success('Data Master Siswa berhasil dihapus');
        $this->redirect('/mastersiswa');
    }
    
    private function uploadFoto($file) {
        $config = require __DIR__ . '/../config/app.php';
        $uploadPath = $config['upload_path'];
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $config['allowed_image_types'])) {
            throw new Exception('Format file tidak diizinkan. Gunakan JPG, PNG, atau GIF.');
        }
        
        if ($file['size'] > $config['max_file_size']) {
            throw new Exception('Ukuran file terlalu besar. Maksimal 5MB.');
        }
        
        $filename = 'siswa_' . uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $uploadPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $filename;
        }
        
        throw new Exception('Gagal mengupload file');
    }
    
    /**
     * API endpoint to get kelas by tahun ajaran (for AJAX)
     */
    public function apiGetKelas() {
        Auth::requireRole(['admin']);
        
        $idtahunajaran = isset($_GET['idtahunajaran']) ? (int)$_GET['idtahunajaran'] : 0;
        
        if ($idtahunajaran <= 0) {
            header('Content-Type: application/json');
            echo json_encode([]);
            exit;
        }
        
        $mastersiswaModel = new Mastersiswa();
        $kelasList = $mastersiswaModel->getKelasByTahunAjaran($idtahunajaran);
        
        header('Content-Type: application/json');
        echo json_encode($kelasList);
        exit;
    }
}

