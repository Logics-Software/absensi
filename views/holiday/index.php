<?php
$title = 'Setting Hari Libur';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Initialize variables if not set (must be before header)
// Note: Variables are extracted from controller via extract() in Controller::view()
// IMPORTANT: Get view from GET parameter directly as fallback
$view = isset($view) ? trim(strtolower($view)) : (isset($_GET['view']) ? trim(strtolower($_GET['view'])) : 'calendar');
// Ensure view is either 'calendar' or 'list'
if ($view !== 'calendar' && $view !== 'list') {
    $view = 'calendar';
}
$year = isset($year) ? (int)$year : (int)date('Y');
$month = isset($month) ? (int)$month : (int)date('m');
$search = isset($search) ? $search : '';
$perPage = isset($perPage) ? (int)$perPage : 10;
$sortBy = isset($sortBy) ? $sortBy : 'holiday_date';
$sortOrder = isset($sortOrder) ? $sortOrder : 'ASC';
$holidays = isset($holidays) ? $holidays : [];
$holidayList = isset($holidayList) ? $holidayList : [];
$page = isset($page) ? (int)$page : 1;
$total = isset($total) ? (int)$total : 0;
$totalPages = isset($totalPages) ? (int)$totalPages : 1;

require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Hari Libur</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php 
            // Use normalized $view from above (already normalized at line 12-16)
            // $view is already normalized, so use it directly
            $currentView = $view;
            $finalView = $view; // Ensure $finalView is defined
            ?>
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Hari Libur</h4>
                        <div class="ms-auto d-flex gap-2">
                            <a href="/holiday?view=list" class="btn btn-sm <?= ($currentView === 'list') ? 'btn-primary' : 'btn-secondary' ?>">List View</a>
                            <a href="/holiday?view=calendar" class="btn btn-sm <?= ($currentView === 'calendar') ? 'btn-primary' : 'btn-secondary' ?>">Calendar View</a>
                            <a href="/holiday/create" class="btn btn-primary btn-sm">Tambah Hari Libur</a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php 
                    // Include the appropriate view file based on $finalView
                    if ($finalView === 'list') {
                        $listFile = __DIR__ . '/list.php';
                        if (file_exists($listFile)) {
                            require $listFile;
                        } else {
                            echo '<div class="alert alert-danger">File list.php tidak ditemukan. Path: ' . htmlspecialchars($listFile) . '</div>';
                        }
                    } else {
                        // Default to calendar view
                        $calendarFile = __DIR__ . '/calendar.php';
                        if (file_exists($calendarFile)) {
                            require $calendarFile;
                        } else {
                            echo '<div class="alert alert-danger">File calendar.php tidak ditemukan.</div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

