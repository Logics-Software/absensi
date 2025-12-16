<?php
// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Override PHP upload settings if possible (only works if not disabled by server)
// This helps when php.ini cannot be modified
@ini_set('upload_max_filesize', '6M'); // 6M for buffer (app limit is 5MB)
@ini_set('post_max_size', '6M'); // 6M for buffer (app limit is 5MB)
@ini_set('max_execution_time', '300'); // 5 minutes for large uploads
@ini_set('max_input_time', '300');

// Autoload classes
spl_autoload_register(function ($class) {
    // Special handling for Message class to avoid conflict
    // Always load core Message first, never load models/Message.php
    if ($class === 'Message') {
        $corePath = __DIR__ . '/core/' . $class . '.php';
        if (file_exists($corePath) && !class_exists('Message', false)) {
            require $corePath;
        }
        // Never load models/Message.php to avoid conflict
        // Use MessageModel for database operations instead
        return;
    }
    
    $paths = [
        __DIR__ . '/core/' . $class . '.php',
        __DIR__ . '/models/' . $class . '.php',
        __DIR__ . '/controllers/' . $class . '.php',
        __DIR__ . '/controllers/api/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        // Skip models/Message.php to prevent conflict
        if (strpos($path, '/models/Message.php') !== false) {
            continue;
        }
        if (file_exists($path)) {
            require $path;
            return;
        }
    }
});

// Start session
Session::start();

// Initialize router
$router = new Router();

// Root route - handled in Router dispatch

// Auth routes
$router->get('/login', 'AuthController', 'login');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');

// Dashboard routes
$router->get('/dashboard', 'DashboardController', 'index');

// User management routes (admin/manajemen only)
$router->get('/users', 'UserController', 'index');
$router->get('/users/create', 'UserController', 'create');
$router->post('/users/create', 'UserController', 'create');
$router->get('/users/edit/{id}', 'UserController', 'edit');
$router->post('/users/edit/{id}', 'UserController', 'edit');
$router->get('/users/delete/{id}', 'UserController', 'delete');

// Profile routes
$router->get('/profile', 'ProfileController', 'index');
$router->post('/profile', 'ProfileController', 'update');
$router->get('/profile/change-password', 'ProfileController', 'changePassword');
$router->post('/profile/change-password', 'ProfileController', 'changePassword');

// Message routes - specific routes first, then generic ones
$router->get('/messages/show/{id}', 'MessageController', 'show');
$router->get('/messages/delete/{id}', 'MessageController', 'delete');
$router->get('/messages/sent', 'MessageController', 'sent');
$router->get('/messages/create', 'MessageController', 'create');
$router->get('/messages/search', 'MessageController', 'search');
$router->get('/messages/searchUsers', 'MessageController', 'searchUsers');
$router->get('/messages/getUnreadCount', 'MessageController', 'getUnreadCount');
$router->get('/messages/markAllAsRead', 'MessageController', 'markAllAsRead');
$router->get('/messages/markAsRead', 'MessageController', 'markAsRead');
$router->post('/messages/store', 'MessageController', 'store');
$router->get('/messages', 'MessageController', 'index');

// Wilayah routes (admin only) - Provinsi
$router->get('/wilayah/provinsi', 'WilayahController', 'provinsi');
$router->get('/wilayah/provinsi/create', 'WilayahController', 'provinsiCreate');
$router->post('/wilayah/provinsi/create', 'WilayahController', 'provinsiCreate');
$router->get('/wilayah/provinsi/edit/{id}', 'WilayahController', 'provinsiEdit');
$router->post('/wilayah/provinsi/edit/{id}', 'WilayahController', 'provinsiEdit');
$router->get('/wilayah/provinsi/delete/{id}', 'WilayahController', 'provinsiDelete');

$router->get('/wilayah/kabupaten', 'WilayahController', 'kabupaten');
$router->get('/wilayah/kabupaten/create', 'WilayahController', 'kabupatenCreate');
$router->post('/wilayah/kabupaten/create', 'WilayahController', 'kabupatenCreate');
$router->get('/wilayah/kabupaten/edit/{id}', 'WilayahController', 'kabupatenEdit');
$router->post('/wilayah/kabupaten/edit/{id}', 'WilayahController', 'kabupatenEdit');
$router->get('/wilayah/kabupaten/delete/{id}', 'WilayahController', 'kabupatenDelete');

$router->get('/wilayah/kecamatan', 'WilayahController', 'kecamatan');
$router->get('/wilayah/kecamatan/create', 'WilayahController', 'kecamatanCreate');
$router->post('/wilayah/kecamatan/create', 'WilayahController', 'kecamatanCreate');
$router->get('/wilayah/kecamatan/edit/{id}', 'WilayahController', 'kecamatanEdit');
$router->post('/wilayah/kecamatan/edit/{id}', 'WilayahController', 'kecamatanEdit');
$router->get('/wilayah/kecamatan/delete/{id}', 'WilayahController', 'kecamatanDelete');

$router->get('/wilayah/kelurahan', 'WilayahController', 'kelurahan');
$router->get('/wilayah/kelurahan/create', 'WilayahController', 'kelurahanCreate');
$router->post('/wilayah/kelurahan/create', 'WilayahController', 'kelurahanCreate');
$router->get('/wilayah/kelurahan/edit/{id}', 'WilayahController', 'kelurahanEdit');
$router->post('/wilayah/kelurahan/edit/{id}', 'WilayahController', 'kelurahanEdit');
$router->get('/wilayah/kelurahan/delete/{id}', 'WilayahController', 'kelurahanDelete');

// API routes for AJAX
$router->get('/wilayah/api/kabupaten', 'WilayahController', 'apiKabupaten');
$router->get('/wilayah/api/kecamatan', 'WilayahController', 'apiKecamatan');
$router->get('/wilayah/api/kelurahan', 'WilayahController', 'apiKelurahan');

// Konfigurasi routes (admin only)
$router->get('/konfigurasi', 'KonfigurasiController', 'index');
$router->post('/konfigurasi', 'KonfigurasiController', 'index');

// Master Guru routes (admin only)
$router->get('/masterguru', 'MasterGuruController', 'index');
$router->get('/masterguru/create', 'MasterGuruController', 'create');
$router->post('/masterguru/create', 'MasterGuruController', 'create');
$router->get('/masterguru/edit/{id}', 'MasterGuruController', 'edit');
$router->post('/masterguru/edit/{id}', 'MasterGuruController', 'edit');
$router->get('/masterguru/delete/{id}', 'MasterGuruController', 'delete');

// Tahun Ajaran routes (admin only)
$router->get('/tahunajaran', 'TahunAjaranController', 'index');
$router->get('/tahunajaran/create', 'TahunAjaranController', 'create');
$router->post('/tahunajaran/create', 'TahunAjaranController', 'create');
$router->get('/tahunajaran/edit/{id}', 'TahunAjaranController', 'edit');
$router->post('/tahunajaran/edit/{id}', 'TahunAjaranController', 'edit');
$router->get('/tahunajaran/delete/{id}', 'TahunAjaranController', 'delete');

// Dispatch
$router->dispatch();

