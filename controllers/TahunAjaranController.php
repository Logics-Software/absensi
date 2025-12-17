<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/TahunAjaran.php';

class TahunAjaranController {
    private function view($view, $data = []) {
        extract($data);
        require_once __DIR__ . "/../views/{$view}.php";
    }
    
    public function index() {
        Auth::requireRole(['admin']);
        
        $tahunAjaranModel = new TahunAjaran();
        $hasData = $tahunAjaranModel->hasData();
        
        // Jika table kosong, redirect ke create
        if (!$hasData) {
            header('Location: /tahunajaran/create');
            exit;
        }
        
        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'id';
        $sortOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        
        $tahunAjaranList = $tahunAjaranModel->getAll($page, $perPage, $search, $sortBy, $sortOrder);
        $total = $tahunAjaranModel->count($search);
        $totalPages = ceil($total / $perPage);
        
        $data = [
            'tahunAjaranList' => $tahunAjaranList,
            'page' => (int)$page,
            'perPage' => (int)$perPage,
            'totalPages' => (int)$totalPages,
            'total' => (int)$total,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('tahunajaran/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin']);
        
        $tahunAjaranModel = new TahunAjaran();
        $hasData = $tahunAjaranModel->hasData();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            
            if (empty($_POST['tahunajaran'])) {
                $errors[] = 'Tahun ajaran harus diisi';
            }
            
            if (empty($_POST['tanggalawal'])) {
                $errors[] = 'Tanggal awal harus diisi';
            }
            
            if (empty($_POST['tanggalakhir'])) {
                $errors[] = 'Tanggal akhir harus diisi';
            }
            
            if (!empty($_POST['tanggalawal']) && !empty($_POST['tanggalakhir'])) {
                if (strtotime($_POST['tanggalawal']) > strtotime($_POST['tanggalakhir'])) {
                    $errors[] = 'Tanggal awal tidak boleh lebih besar dari tanggal akhir';
                }
            }
            
            if (empty($errors)) {
                // CEK APAKAH TABLE KOSONG
                $isTableEmpty = !$tahunAjaranModel->hasData();
                
                // TENTUKAN STATUS
                if ($isTableEmpty) {
                    // TABLE KOSONG = STATUS HARUS 'AKTIF'
                    $status = 'aktif';
                } else {
                    // TABLE SUDAH ADA DATA = GUNAKAN POST VALUE
                    $status = !empty($_POST['status']) ? $_POST['status'] : 'selesai';
                }
                
                // PREPARE DATA
                $data = [
                    'tahunajaran' => trim($_POST['tahunajaran']),
                    'tanggalawal' => $_POST['tanggalawal'],
                    'tanggalakhir' => $_POST['tanggalakhir'],
                    'status' => $status
                ];
                
                // FINAL CHECK: JIKA TABLE KOSONG, PASTIKAN STATUS = 'AKTIF'
                if ($isTableEmpty) {
                    $data['status'] = 'aktif';
                }
                
                try {
                    $id = $tahunAjaranModel->create($data);
                    $_SESSION['success_message'] = 'Tahun ajaran berhasil ditambahkan';
                    header('Location: /tahunajaran');
                    exit;
                } catch (Exception $e) {
                    $errors[] = 'Gagal menambahkan tahun ajaran: ' . $e->getMessage();
                }
            }
            
            if (!empty($errors)) {
                $_SESSION['error_message'] = implode('<br>', $errors);
            }
        }
        
        $activeTahunAjaran = $tahunAjaranModel->getActive();
        
        $data = [
            'hasData' => $hasData,
            'activeTahunAjaran' => $activeTahunAjaran
        ];
        
        $this->view('tahunajaran/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin']);
        
        $tahunAjaranModel = new TahunAjaran();
        $tahunAjaran = $tahunAjaranModel->findById($id);
        
        if (!$tahunAjaran) {
            $_SESSION['error_message'] = 'Tahun ajaran tidak ditemukan';
            header('Location: /tahunajaran');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            
            if (empty($_POST['tahunajaran'])) {
                $errors[] = 'Tahun ajaran harus diisi';
            }
            
            if (empty($_POST['tanggalawal'])) {
                $errors[] = 'Tanggal awal harus diisi';
            }
            
            if (empty($_POST['tanggalakhir'])) {
                $errors[] = 'Tanggal akhir harus diisi';
            }
            
            if (!empty($_POST['tanggalawal']) && !empty($_POST['tanggalakhir'])) {
                if (strtotime($_POST['tanggalawal']) > strtotime($_POST['tanggalakhir'])) {
                    $errors[] = 'Tanggal awal tidak boleh lebih besar dari tanggal akhir';
                }
            }
            
            if (empty($errors)) {
                $status = !empty($_POST['status']) ? $_POST['status'] : $tahunAjaran['status'];
                
                $data = [
                    'tahunajaran' => trim($_POST['tahunajaran']),
                    'tanggalawal' => $_POST['tanggalawal'],
                    'tanggalakhir' => $_POST['tanggalakhir'],
                    'status' => $status
                ];
                
                try {
                    $tahunAjaranModel->update($id, $data);
                    $_SESSION['success_message'] = 'Tahun ajaran berhasil diupdate';
                    header('Location: /tahunajaran');
                    exit;
                } catch (Exception $e) {
                    $errors[] = 'Gagal mengupdate tahun ajaran: ' . $e->getMessage();
                }
            }
            
            if (!empty($errors)) {
                $_SESSION['error_message'] = implode('<br>', $errors);
            }
        }
        
        $data = [
            'tahunAjaran' => $tahunAjaran
        ];
        
        $this->view('tahunajaran/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        $tahunAjaranModel = new TahunAjaran();
        $tahunAjaran = $tahunAjaranModel->findById($id);
        
        if (!$tahunAjaran) {
            $_SESSION['error_message'] = 'Tahun ajaran tidak ditemukan';
            header('Location: /tahunajaran');
            exit;
        }
        
        try {
            $tahunAjaranModel->delete($id);
            $_SESSION['success_message'] = 'Tahun ajaran berhasil dihapus';
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Gagal menghapus tahun ajaran: ' . $e->getMessage();
        }
        
        header('Location: /tahunajaran');
        exit;
    }
}

