<?php
class MasterGuruController extends Controller {
    public function index() {
        Auth::requireRole(['admin']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }
        
        $masterGuruModel = new MasterGuru();
        $masterGuruList = $masterGuruModel->getAll($page, $perPage, $search, $sortBy, $sortOrder);
        $total = $masterGuruModel->count($search);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'masterGuruList' => $masterGuruList,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('masterguru/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin']);
        
        // Get wilayah data for dropdowns
        $wilayahModel = new Wilayah();
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000) ?: [];
        
        // Get guru user list
        $masterGuruModel = new MasterGuru();
        $guruList = $masterGuruModel->getGuruList();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Convert tanggal lahir from DD/MM/YYYY to YYYY-MM-DD if needed
            $tanggallahir = $_POST['tanggallahir'] ?? null;
            if (!empty($tanggallahir) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $tanggallahir)) {
                $parts = explode('/', $tanggallahir);
                $tanggallahir = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
            
            $data = [
                'nip' => $_POST['nip'] ?? null,
                'namaguru' => $_POST['namaguru'] ?? null,
                'jeniskelamin' => $_POST['jeniskelamin'] ?? null,
                'tempatlahir' => $_POST['tempatlahir'] ?? null,
                'tanggallahir' => !empty($tanggallahir) ? $tanggallahir : null,
                'alamatguru' => $_POST['alamatguru'] ?? null,
                'idprovinsi' => !empty($_POST['idprovinsi']) ? (int)$_POST['idprovinsi'] : null,
                'idkabupaten' => !empty($_POST['idkabupaten']) ? (int)$_POST['idkabupaten'] : null,
                'idkecamatan' => !empty($_POST['idkecamatan']) ? (int)$_POST['idkecamatan'] : null,
                'idkelurahan' => !empty($_POST['idkelurahan']) ? (int)$_POST['idkelurahan'] : null,
                'kodepos' => $_POST['kodepos'] ?? null,
                'nomorhp' => !empty($_POST['nomorhp']) ? $_POST['nomorhp'] : null,
                'email' => $_POST['email'] ?? null,
                'foto' => null,
                'iduser' => !empty($_POST['iduser']) ? (int)$_POST['iduser'] : null,
                'status' => $_POST['status'] ?? 'aktif'
            ];
            
            // Validate NIP uniqueness
            if (!empty($data['nip'])) {
                $existing = $masterGuruModel->findByNip($data['nip']);
                if ($existing) {
                    Message::error('NIP sudah digunakan');
                    $this->redirect('/masterguru/create');
                }
            }
            
            // Handle foto upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                try {
                    $data['foto'] = $this->uploadFoto($_FILES['foto']);
                } catch (Exception $e) {
                    Message::error($e->getMessage());
                    $this->redirect('/masterguru/create');
                }
            }
            
            $masterGuruModel->create($data);
            Message::success('Data Master Guru berhasil ditambahkan');
            $this->redirect('/masterguru');
        }
        
        $data = [
            'provinsiList' => $provinsiList,
            'guruList' => $guruList
        ];
        
        $this->view('masterguru/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin']);
        
        $masterGuruModel = new MasterGuru();
        $masterGuru = $masterGuruModel->findById($id);
        
        if (!$masterGuru) {
            Message::error('Data Master Guru tidak ditemukan');
            $this->redirect('/masterguru');
        }
        
        // Get wilayah data for dropdowns
        $wilayahModel = new Wilayah();
        $provinsiList = $wilayahModel->getAllProvinsi(1, 1000) ?: [];
        
        // Get kabupaten, kecamatan, kelurahan based on selected values
        $kabupatenList = [];
        $kecamatanList = [];
        $kelurahanList = [];
        
        if (!empty($masterGuru['idprovinsi'])) {
            $kabupatenList = $wilayahModel->getKabupatenKotaByProvinsi($masterGuru['idprovinsi']) ?: [];
        }
        if (!empty($masterGuru['idkabupaten'])) {
            $kecamatanList = $wilayahModel->getKecamatanByKabupaten($masterGuru['idkabupaten']) ?: [];
        }
        if (!empty($masterGuru['idkecamatan'])) {
            $kelurahanList = $wilayahModel->getKelurahanByKecamatan($masterGuru['idkecamatan']) ?: [];
        }
        
        // Get guru user list
        $guruList = $masterGuruModel->getGuruList();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Convert tanggal lahir from DD/MM/YYYY to YYYY-MM-DD if needed
            $tanggallahir = $_POST['tanggallahir'] ?? null;
            if (!empty($tanggallahir) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $tanggallahir)) {
                $parts = explode('/', $tanggallahir);
                $tanggallahir = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
            
            $data = [
                'nip' => $_POST['nip'] ?? null,
                'namaguru' => $_POST['namaguru'] ?? null,
                'jeniskelamin' => $_POST['jeniskelamin'] ?? null,
                'tempatlahir' => $_POST['tempatlahir'] ?? null,
                'tanggallahir' => !empty($tanggallahir) ? $tanggallahir : null,
                'alamatguru' => $_POST['alamatguru'] ?? null,
                'idprovinsi' => !empty($_POST['idprovinsi']) ? (int)$_POST['idprovinsi'] : null,
                'idkabupaten' => !empty($_POST['idkabupaten']) ? (int)$_POST['idkabupaten'] : null,
                'idkecamatan' => !empty($_POST['idkecamatan']) ? (int)$_POST['idkecamatan'] : null,
                'idkelurahan' => !empty($_POST['idkelurahan']) ? (int)$_POST['idkelurahan'] : null,
                'kodepos' => $_POST['kodepos'] ?? null,
                'nomorhp' => !empty($_POST['nomorhp']) ? $_POST['nomorhp'] : null,
                'email' => $_POST['email'] ?? null,
                'foto' => $masterGuru['foto'] ?? null, // Keep existing foto if not uploaded
                'iduser' => !empty($_POST['iduser']) ? (int)$_POST['iduser'] : null,
                'status' => $_POST['status'] ?? 'aktif'
            ];
            
            // Validate NIP uniqueness (except current record)
            if (!empty($data['nip'])) {
                $existing = $masterGuruModel->findByNip($data['nip']);
                if ($existing && $existing['id'] != $id) {
                    Message::error('NIP sudah digunakan');
                    $this->redirect("/masterguru/edit/{$id}");
                }
            }
            
            // Handle foto upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Delete old foto if exists
                    if ($masterGuru['foto']) {
                        $oldFotoPath = __DIR__ . '/../uploads/' . $masterGuru['foto'];
                        if (file_exists($oldFotoPath)) {
                            unlink($oldFotoPath);
                        }
                    }
                    $data['foto'] = $this->uploadFoto($_FILES['foto']);
                } catch (Exception $e) {
                    Message::error($e->getMessage());
                    $this->redirect("/masterguru/edit/{$id}");
                }
            }
            
            $masterGuruModel->update($id, $data);
            Message::success('Data Master Guru berhasil diupdate');
            $this->redirect('/masterguru');
        }
        
        $data = [
            'masterGuru' => $masterGuru,
            'provinsiList' => $provinsiList,
            'kabupatenList' => $kabupatenList,
            'kecamatanList' => $kecamatanList,
            'kelurahanList' => $kelurahanList,
            'guruList' => $guruList
        ];
        
        $this->view('masterguru/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        $masterGuruModel = new MasterGuru();
        $masterGuru = $masterGuruModel->findById($id);
        
        if (!$masterGuru) {
            Message::error('Data Master Guru tidak ditemukan');
            $this->redirect('/masterguru');
        }
        
        // Delete foto
        if ($masterGuru['foto']) {
            $fotoPath = __DIR__ . '/../uploads/' . $masterGuru['foto'];
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }
        
        $masterGuruModel->delete($id);
        Message::success('Data Master Guru berhasil dihapus');
        $this->redirect('/masterguru');
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
        
        $filename = 'guru_' . uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $uploadPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $filename;
        }
        
        throw new Exception('Gagal mengupload file');
    }
}
