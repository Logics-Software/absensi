<?php
class KonfigurasiController extends Controller {
    public function index() {
        Auth::requireRole(['admin']);
        
        $konfigurasiModel = new Konfigurasi();
        $konfigurasi = $konfigurasiModel->get();
        $kepalaSekolahList = $konfigurasiModel->getKepalaSekolahList();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'npsn' => $_POST['npsn'] ?? null,
                'namasekolah' => $_POST['namasekolah'] ?? null,
                'alamatsekolah' => $_POST['alamatsekolah'] ?? null,
                'skpendirian' => $_POST['skpendirian'] ?? null,
                'tanggalskpendirian' => $_POST['tanggalskpendirian'] ?? null,
                'skoperasional' => $_POST['skoperasional'] ?? null,
                'tanggalskoperasional' => $_POST['tanggalskoperasional'] ?? null,
                'idkepalasekolah' => !empty($_POST['idkepalasekolah']) ? (int)$_POST['idkepalasekolah'] : null,
                'logo' => $konfigurasi['logo'] ?? null // Keep existing logo if not uploaded
            ];
            
            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Delete old logo if exists
                    if ($konfigurasi && !empty($konfigurasi['logo'])) {
                        $oldLogoPath = __DIR__ . '/../uploads/' . $konfigurasi['logo'];
                        if (file_exists($oldLogoPath)) {
                            unlink($oldLogoPath);
                        }
                    }
                    $data['logo'] = $this->uploadLogo($_FILES['logo']);
                } catch (Exception $e) {
                    Message::error($e->getMessage());
                    $this->redirect('/konfigurasi');
                }
            }
            
            if ($konfigurasiModel->save($data)) {
                Message::success('Konfigurasi berhasil disimpan');
            } else {
                Message::error('Gagal menyimpan konfigurasi');
            }
            
            $this->redirect('/konfigurasi');
        }
        
        $data = [
            'konfigurasi' => $konfigurasi,
            'kepalaSekolahList' => $kepalaSekolahList
        ];
        
        $this->view('konfigurasi/index', $data);
    }
    
    private function uploadLogo($file) {
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
        
        $filename = 'logo_' . uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $uploadPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $filename;
        }
        
        throw new Exception('Gagal mengupload file');
    }
}

