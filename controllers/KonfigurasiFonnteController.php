<?php
// Load FonnteService
require_once __DIR__ . '/../services/FonnteService.php';

class KonfigurasiFonnteController extends Controller {
    public function index() {
        Auth::requireRole(['admin']);
        
        $konfigurasiFonnteModel = new KonfigurasiFonnte();
        
        // 1. Cek dengan SELECT apakah ada data di table
        $konfigurasi = $konfigurasiFonnteModel->get();
        
        // 3. Update jika data sudah ada, Simpan jika create baru
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'api_key' => trim($_POST['api_key'] ?? ''),
                'api_url' => trim($_POST['api_url'] ?? 'https://api.fonnte.com'),
                'device_id' => trim($_POST['device_id'] ?? ''),
                'webhook_url' => trim($_POST['webhook_url'] ?? '')
            ];
            
            // Validation
            if (empty($data['api_key'])) {
                Message::error('API Key tidak boleh kosong');
                $this->redirect('/konfigurasi-fonnte');
                return;
            }
            
            if (empty($data['api_url'])) {
                $data['api_url'] = 'https://api.fonnte.com';
            }
            
            // Save akan otomatis UPDATE jika sudah ada, CREATE jika belum ada
            if ($konfigurasiFonnteModel->save($data)) {
                Message::success('Konfigurasi Fonnte berhasil disimpan');
            } else {
                Message::error('Gagal menyimpan konfigurasi Fonnte');
            }
            
            $this->redirect('/konfigurasi-fonnte');
        }
        
        // 2. Jika ada maka ditampilkan dalam form (inputbox) masing-masing
        // Pass data to view - gunakan nama yang sama dengan KonfigurasiController
        $data = [
            'konfigurasi' => $konfigurasi
        ];
        
        $this->view('konfigurasifonnte/index', $data);
    }
    
    /**
     * Test connection to Fonnte API
     */
    public function test() {
        Auth::requireRole(['admin']);
        
        try {
            $fonnteService = new FonnteService();
            $result = $fonnteService->checkDevice();
            
            if ($result['status']) {
                Message::success('Koneksi ke Fonnte API berhasil!');
            } else {
                Message::error('Koneksi gagal: ' . ($result['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            Message::error('Error: ' . $e->getMessage());
        }
        
        $this->redirect('/konfigurasi-fonnte');
    }
}
