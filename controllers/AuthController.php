<?php
class AuthController extends Controller {
    public function login() {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                Session::flash('error', 'Username dan password harus diisi');
                $this->redirect('/login');
            }
            
            $userModel = new User();
            $user = $userModel->findByUsername($username);
            
            if ($user && $userModel->verifyPassword($password, $user['password'])) {
                Session::start();
                Auth::login($user['id'], $user);
                $this->redirect('/dashboard');
            } else {
                Session::flash('error', 'Username atau password salah');
                $this->redirect('/login');
            }
        }
        
        // Get konfigurasi logo for login form (same logic as header.php)
        $konfigurasiLogo = null;
        try {
            $config = require __DIR__ . '/../config/app.php';
            $baseUrl = rtrim($config['base_url'], '/');
            if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
                $baseUrl = '/';
            }
            
            $konfigurasiModel = new Konfigurasi();
            $konfigurasi = $konfigurasiModel->get();
            if ($konfigurasi && !empty($konfigurasi['logo'])) {
                // Use same path check as header.php
                $logoFilePath = __DIR__ . '/../uploads/' . $konfigurasi['logo'];
                if (file_exists($logoFilePath)) {
                    $konfigurasiLogo = $baseUrl . $config['upload_url'] . $konfigurasi['logo'];
                }
            }
        } catch (Exception $e) {
            // Silently fail if Konfigurasi model not available
        }
        
        $data = [
            'konfigurasiLogo' => $konfigurasiLogo
        ];
        
        $this->view('auth/login', $data);
    }
    
    public function logout() {
        Session::start();
        Auth::logout();
        Session::flash('success', 'Logout berhasil');
        $this->redirect('/login');
    }
}
