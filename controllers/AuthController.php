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
        
        $this->view('auth/login');
    }
    
    public function logout() {
        Session::start();
        Auth::logout();
        Session::flash('success', 'Logout berhasil');
        $this->redirect('/login');
    }
}
