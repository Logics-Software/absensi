<?php
class JurusanController extends Controller {
    public function index() {
        Auth::requireRole(['admin']);
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'idjurusan';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        
        $validPerPage = [10, 25, 50, 100, 200, 500, 1000];
        if (!in_array($perPage, $validPerPage)) {
            $perPage = 10;
        }
        
        $jurusanModel = new Jurusan();
        $jurusanList = $jurusanModel->getAll($page, $perPage, $search, $sortBy, $sortOrder);
        $total = $jurusanModel->count($search);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'jurusanList' => $jurusanList,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('jurusan/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'namajurusan' => $_POST['namajurusan'] ?? '',
                'status' => $_POST['status'] ?? 'aktif'
            ];
            
            // Validate
            if (empty($data['namajurusan'])) {
                Message::error('Nama Jurusan wajib diisi');
                $this->redirect('/jurusan/create');
            }
            
            $jurusanModel = new Jurusan();
            $jurusanModel->create($data);
            Message::success('Jurusan berhasil ditambahkan');
            $this->redirect('/jurusan');
        }
        
        $this->view('jurusan/create');
    }
    
    public function edit($id) {
        Auth::requireRole(['admin']);
        
        $jurusanModel = new Jurusan();
        $jurusan = $jurusanModel->findById($id);
        
        if (!$jurusan) {
            Message::error('Jurusan tidak ditemukan');
            $this->redirect('/jurusan');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'namajurusan' => $_POST['namajurusan'] ?? '',
                'status' => $_POST['status'] ?? 'aktif'
            ];
            
            // Validate
            if (empty($data['namajurusan'])) {
                Message::error('Nama Jurusan wajib diisi');
                $this->redirect("/jurusan/edit/{$id}");
            }
            
            $jurusanModel->update($id, $data);
            Message::success('Jurusan berhasil diupdate');
            $this->redirect('/jurusan');
        }
        
        $data = [
            'jurusan' => $jurusan
        ];
        $this->view('jurusan/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        $jurusanModel = new Jurusan();
        $jurusan = $jurusanModel->findById($id);
        
        if (!$jurusan) {
            Message::error('Jurusan tidak ditemukan');
            $this->redirect('/jurusan');
        }
        
        $jurusanModel->delete($id);
        Message::success('Jurusan berhasil dihapus');
        $this->redirect('/jurusan');
    }
}

