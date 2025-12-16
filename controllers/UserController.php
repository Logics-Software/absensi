<?php
class UserController extends Controller {
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
        
        $userModel = new User();
        $users = $userModel->getAll($page, $perPage, $search, $sortBy, $sortOrder);
        $total = $userModel->count($search);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $data = [
            'users' => $users,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('users/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin']);
        
        // Get master guru list for dropdown
        require_once __DIR__ . '/../models/MasterGuru.php';
        $masterGuruModel = new MasterGuru();
        $masterGuruList = $masterGuruModel->getAll(1, 1000) ?: []; // Get all for dropdown
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => $_POST['username'] ?? '',
                'namalengkap' => $_POST['namalengkap'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'role' => $_POST['role'] ?? 'tata_usaha',
                'id_guru' => $_POST['id_guru'] ?? null,
                'status' => $_POST['status'] ?? 'aktif'
            ];
            
            // Validate id_guru for guru role
            if ($data['role'] === 'guru') {
                if (empty($data['id_guru'])) {
                    Message::error('ID Guru wajib diisi untuk role Guru');
                    $this->redirect('/users/create');
                }
            } else {
                // Clear id_guru if role is not guru
                $data['id_guru'] = null;
            }
            
            // Handle picture upload
            if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                try {
                    $data['picture'] = $this->uploadPicture($_FILES['picture']);
                } catch (Exception $e) {
                    Message::error($e->getMessage());
                    $this->redirect('/users/create');
                }
            }
            
            $userModel = new User();
            
            // Check if username exists
            if ($userModel->findByUsername($data['username'])) {
                Message::error('Username sudah digunakan');
                $this->redirect('/users/create');
            }
            
            // Check if email exists
            if ($userModel->findByEmail($data['email'])) {
                Message::error('Email sudah digunakan');
                $this->redirect('/users/create');
            }
            
            $userModel->create($data);
            Message::success('User berhasil ditambahkan');
            $this->redirect('/users');
        }
        
        $data = [
            'masterGuruList' => $masterGuruList
        ];
        $this->view('users/create', $data);
    }
    
    public function edit($id) {
        Auth::requireRole(['admin']);
        
        $userModel = new User();
        $user = $userModel->findById($id);
        
        if (!$user) {
            Message::error('User tidak ditemukan');
            $this->redirect('/users');
        }
        
        // Get master guru list for dropdown
        require_once __DIR__ . '/../models/MasterGuru.php';
        $masterGuruModel = new MasterGuru();
        $masterGuruList = $masterGuruModel->getAll(1, 1000) ?: []; // Get all for dropdown
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => $_POST['username'] ?? '',
                'namalengkap' => $_POST['namalengkap'] ?? '',
                'email' => $_POST['email'] ?? '',
                'role' => $_POST['role'] ?? 'tata_usaha',
                'id_guru' => $_POST['id_guru'] ?? null,
                'status' => $_POST['status'] ?? 'aktif'
            ];
            
            // Validate id_guru for guru role
            if ($data['role'] === 'guru') {
                if (empty($data['id_guru'])) {
                    Message::error('ID Guru wajib diisi untuk role Guru');
                    $this->redirect("/users/edit/{$id}");
                }
            } else {
                // Clear id_guru if role is not guru
                $data['id_guru'] = null;
            }
            
            // Check username uniqueness (except current user)
            $existingUser = $userModel->findByUsername($data['username']);
            if ($existingUser && $existingUser['id'] != $id) {
                Message::error('Username sudah digunakan');
                $this->redirect("/users/edit/{$id}");
            }
            
            // Check email uniqueness (except current user)
            $existingEmail = $userModel->findByEmail($data['email']);
            if ($existingEmail && $existingEmail['id'] != $id) {
                Message::error('Email sudah digunakan');
                $this->redirect("/users/edit/{$id}");
            }
            
            // Handle picture upload
            if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Delete old picture
                    if ($user['picture']) {
                        $oldPicturePath = __DIR__ . '/../uploads/' . $user['picture'];
                        if (file_exists($oldPicturePath)) {
                            unlink($oldPicturePath);
                        }
                    }
                    $data['picture'] = $this->uploadPicture($_FILES['picture']);
                } catch (Exception $e) {
                    Message::error($e->getMessage());
                    $this->redirect("/users/edit/{$id}");
                }
            }
            
            $userModel->update($id, $data);
            Message::success('User berhasil diupdate');
            $this->redirect('/users');
        }
        
        $data = [
            'user' => $user,
            'masterGuruList' => $masterGuruList
        ];
        $this->view('users/edit', $data);
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        $userModel = new User();
        $user = $userModel->findById($id);
        
        if (!$user) {
            Message::error('User tidak ditemukan');
            $this->redirect('/users');
        }
        
        // Don't allow deleting yourself
        if ($user['id'] == Auth::user()['id']) {
            Message::error('Tidak dapat menghapus akun sendiri');
            $this->redirect('/users');
        }
        
        // Delete picture
        if ($user['picture']) {
            $picturePath = __DIR__ . '/../uploads/' . $user['picture'];
            if (file_exists($picturePath)) {
                unlink($picturePath);
            }
        }
        
        $userModel->delete($id);
        Message::success('User berhasil dihapus');
        $this->redirect('/users');
    }
    
    private function uploadPicture($file) {
        $config = require __DIR__ . '/../config/app.php';
        $uploadPath = $config['upload_path'];
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $config['allowed_image_types'])) {
            throw new Exception('Format file tidak diizinkan');
        }
        
        if ($file['size'] > $config['max_file_size']) {
            throw new Exception('Ukuran file terlalu besar');
        }
        
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $uploadPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $filename;
        }
        
        throw new Exception('Gagal mengupload file');
    }
}

