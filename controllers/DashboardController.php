<?php
class DashboardController extends Controller {
    public function index() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $role = $user['role'] ?? '';
        
        $data = [
            'user' => $user,
            'role' => $role,
            'stats' => []
        ];
        
        $this->view('dashboard/index', $data);
    }
}
