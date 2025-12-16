<?php
class Auth {
    public static function check() {
        Session::start();
        return Session::has('user_id');
    }
    
    public static function user() {
        Session::start();
        if (self::check()) {
            $userId = Session::get('user_id');
            $userModel = new User();
            return $userModel->findById($userId);
        }
        return null;
    }
    
    public static function login($userId, $userData) {
        Session::start();
        Session::set('user_id', $userId);
        Session::set('user_role', $userData['role']);
        Session::set('user_username', $userData['username']);
    }
    
    public static function logout() {
        Session::start();
        Session::destroy();
    }
    
    public static function isAdmin() {
        return self::user() && self::user()['role'] === 'admin';
    }
    
    public static function isTataUsaha() {
        $user = self::user();
        return $user && ($user['role'] === 'admin' || $user['role'] === 'tata_usaha');
    }
    
    public static function isGuru() {
        $user = self::user();
        return $user && $user['role'] === 'guru';
    }

    public static function isKepalaSekolah() {
        $user = self::user();
        return $user && $user['role'] === 'kepala_sekolah';
    }
    
    public static function isPenilikSekolah() {
        $user = self::user();
        return $user && $user['role'] === 'penilik_sekolah';
    }
    
    public static function isWaliMurid() {
        $user = self::user();
        return $user && $user['role'] === 'wali_murid';
    }
    
    public static function requireAuth() {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }
    
    public static function requireRole($roles) {
        self::requireAuth();
        $user = self::user();
        if (!in_array($user['role'], (array)$roles)) {
            header('Location: /dashboard');
            exit;
        }
    }
}

