<?php
// Load config first to check debug mode
$config = require __DIR__ . '/config/app.php';

// Set timezone
date_default_timezone_set($config['timezone'] ?? 'Asia/Jakarta');

// Error reporting based on debug mode and environment
$isDebug = $config['debug'] ?? false;
$environment = $config['environment'] ?? 'production';

if ($isDebug && $environment === 'development') {
    // Development mode: Show all errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // Production mode: Hide errors, log them instead
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    
    // Create logs directory if it doesn't exist
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    // Set error log file
    ini_set('error_log', $logDir . '/error.log');
}

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
        __DIR__ . '/controllers/api/' . $class . '.php',
        __DIR__ . '/services/' . $class . '.php'
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
    
    // Autoload vendor libraries (PhpSpreadsheet and dependencies)
    // Handle PSR-4 namespaces
    $vendorDir = __DIR__ . '/vendor';
    
    // PhpOffice\PhpSpreadsheet namespace
    if (strpos($class, 'PhpOffice\\PhpSpreadsheet\\') === 0) {
        $relativeClass = substr($class, strlen('PhpOffice\\PhpSpreadsheet\\'));
        $file = $vendorDir . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
    
    // PSR\SimpleCache namespace
    if (strpos($class, 'Psr\\SimpleCache\\') === 0) {
        $relativeClass = substr($class, strlen('Psr\\SimpleCache\\'));
        $file = $vendorDir . '/psr/simple-cache/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
    
    // Matrix namespace
    if (strpos($class, 'Matrix\\') === 0) {
        $relativeClass = substr($class, strlen('Matrix\\'));
        $file = $vendorDir . '/markbaker/matrix/classes/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
    
    // Complex namespace
    if (strpos($class, 'Complex\\') === 0) {
        $relativeClass = substr($class, strlen('Complex\\'));
        $file = $vendorDir . '/markbaker/complex/classes/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
    
    // Composer\Pcre namespace
    if (strpos($class, 'Composer\\Pcre\\') === 0) {
        $relativeClass = substr($class, strlen('Composer\\Pcre\\'));
        $file = $vendorDir . '/composer/pcre/src/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
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

// Konfigurasi Fonnte routes (admin only)
$router->get('/konfigurasi-fonnte', 'KonfigurasiFonnteController', 'index');
$router->post('/konfigurasi-fonnte', 'KonfigurasiFonnteController', 'index');
$router->get('/konfigurasi-fonnte/test', 'KonfigurasiFonnteController', 'test');

// Setting Jam Belajar routes (admin only)
$router->get('/settingjambelajar', 'SettingJamBelajarController', 'index');
$router->post('/settingjambelajar', 'SettingJamBelajarController', 'index');

// Kalender Akademik routes (admin only)
$router->get('/kalenderakademik', 'KalenderAkademikController', 'index');
$router->post('/kalenderakademik', 'KalenderAkademikController', 'index');

// WA Blast routes
$router->get('/wablast', 'WablastController', 'index');
$router->get('/wablast/create', 'WablastController', 'create');
$router->post('/wablast/create', 'WablastController', 'create');
$router->get('/wablast/view/{id}', 'WablastController', 'viewCampaign');
$router->post('/wablast/send/{id}', 'WablastController', 'send');
$router->post('/wablast/resend/{campaignId}/{messageId}', 'WablastController', 'resend');
$router->post('/wablast/delete/{id}', 'WablastController', 'delete');
$router->get('/wablast/api/recipients', 'WablastController', 'apiGetRecipients');

// WA Blast Webhook (no auth required - Fonnte will call this)
$router->post('/wablast/webhook', 'WablastWebhookController', 'handle');
$router->get('/wablast/webhook', 'WablastWebhookController', 'handle'); // Some services use GET for verification

// Finger Import routes (admin only)
$router->get('/fingerimport', 'FingerImportController', 'index');
$router->post('/fingerimport/upload', 'FingerImportController', 'upload');

// Laporan Kehadiran routes (admin, guru)
$router->get('/laporankehadiran', 'LaporanKehadiranController', 'index');

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

// Jurusan routes (admin only)
$router->get('/jurusan', 'JurusanController', 'index');
$router->get('/jurusan/create', 'JurusanController', 'create');
$router->post('/jurusan/create', 'JurusanController', 'create');
$router->get('/jurusan/edit/{id}', 'JurusanController', 'edit');
$router->post('/jurusan/edit/{id}', 'JurusanController', 'edit');
$router->get('/jurusan/delete/{id}', 'JurusanController', 'delete');

// Kelas routes (admin only)
$router->get('/kelas', 'KelasController', 'index');
$router->get('/kelas/create', 'KelasController', 'create');
$router->post('/kelas/create', 'KelasController', 'create');
$router->get('/kelas/edit/{id}', 'KelasController', 'edit');
$router->post('/kelas/edit/{id}', 'KelasController', 'edit');
$router->get('/kelas/delete/{id}', 'KelasController', 'delete');

// Master Siswa routes (admin only)
$router->get('/mastersiswa', 'MastersiswaController', 'index');
$router->get('/mastersiswa/create', 'MastersiswaController', 'create');
$router->post('/mastersiswa/create', 'MastersiswaController', 'create');
$router->get('/mastersiswa/edit/{id}', 'MastersiswaController', 'edit');
$router->post('/mastersiswa/edit/{id}', 'MastersiswaController', 'edit');
$router->get('/mastersiswa/delete/{id}', 'MastersiswaController', 'delete');
$router->get('/mastersiswa/api/getkelas', 'MastersiswaController', 'apiGetKelas');

// Absensi Siswa routes (admin only)
$router->get('/absensisiswa', 'AbsensiSiswaController', 'index');
$router->get('/absensisiswa/create', 'AbsensiSiswaController', 'create');
$router->post('/absensisiswa/create', 'AbsensiSiswaController', 'create');
$router->get('/absensisiswa/edit/{id}', 'AbsensiSiswaController', 'edit');
$router->post('/absensisiswa/edit/{id}', 'AbsensiSiswaController', 'edit');
$router->get('/absensisiswa/delete/{id}', 'AbsensiSiswaController', 'delete');

// Absensi Guru routes (admin only)
$router->get('/absensiguru', 'AbsensiGuruController', 'index');
$router->get('/absensiguru/create', 'AbsensiGuruController', 'create');
$router->post('/absensiguru/create', 'AbsensiGuruController', 'create');
$router->get('/absensiguru/edit/{id}', 'AbsensiGuruController', 'edit');
$router->post('/absensiguru/edit/{id}', 'AbsensiGuruController', 'edit');
$router->get('/absensiguru/delete/{id}', 'AbsensiGuruController', 'delete');

// Holiday routes (admin only)
$router->get('/holiday', 'HolidayController', 'index');
$router->get('/holiday/create', 'HolidayController', 'create');
$router->post('/holiday/create', 'HolidayController', 'create');
$router->get('/holiday/edit/{id}', 'HolidayController', 'edit');
$router->post('/holiday/edit/{id}', 'HolidayController', 'edit');
$router->get('/holiday/delete/{id}', 'HolidayController', 'delete');
$router->get('/holiday/api/getholidays', 'HolidayController', 'apiGetHolidays');

// Dispatch
$router->dispatch();

