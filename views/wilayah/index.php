<?php
// Variables are extracted from controller data via extract() in Controller::view()
// Ensure defaults are set if not provided
$type = isset($type) ? $type : 'provinsi';
$provinsi = isset($provinsi) ? $provinsi : [];
$kabupaten = isset($kabupaten) ? $kabupaten : [];
$kecamatan = isset($kecamatan) ? $kecamatan : [];
$kelurahan = isset($kelurahan) ? $kelurahan : [];
$page = isset($page) ? $page : 1;
$perPage = isset($perPage) ? $perPage : 50;
$total = isset($total) ? $total : 0;
$totalPages = isset($totalPages) ? $totalPages : 1;
$search = isset($search) ? $search : '';
$sortBy = isset($sortBy) ? $sortBy : 'kode';
$sortOrder = isset($sortOrder) ? $sortOrder : 'ASC';

$typeLabels = [
    'provinsi' => 'Provinsi',
    'kabupaten' => 'Kabupaten/Kota',
    'kecamatan' => 'Kecamatan',
    'kelurahan' => 'Kelurahan/Desa'
];

$typeRoutes = [
    'provinsi' => '/wilayah/provinsi',
    'kabupaten' => '/wilayah/kabupaten',
    'kecamatan' => '/wilayah/kecamatan',
    'kelurahan' => '/wilayah/kelurahan'
];

$title = 'Manajemen ' . ($typeLabels[$type] ?? 'Wilayah');
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Helper function to generate sort URL
if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $perPage, $type, $filterId = null) {
        global $typeRoutes;
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = ['page' => 1, 'per_page' => $perPage, 'search' => $search, 'sort_by' => $column, 'sort_order' => $newSortOrder];
        if ($type === 'kabupaten' && $filterId) $params['provinsi_id'] = $filterId;
        if ($type === 'kecamatan' && $filterId) $params['kabupaten_id'] = $filterId;
        if ($type === 'kelurahan' && $filterId) $params['kecamatan_id'] = $filterId;
        $route = $typeRoutes[$type] ?? '/wilayah/provinsi';
        return htmlspecialchars($route) . '?' . http_build_query($params);
    }
}

require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($typeLabels[$type] ?? 'Wilayah') ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?= $type === 'provinsi' ? 'active' : '' ?>" href="/wilayah/provinsi">Provinsi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $type === 'kabupaten' ? 'active' : '' ?>" href="/wilayah/kabupaten">Kabupaten/Kota</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $type === 'kecamatan' ? 'active' : '' ?>" href="/wilayah/kecamatan">Kecamatan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $type === 'kelurahan' ? 'active' : '' ?>" href="/wilayah/kelurahan">Kelurahan/Desa</a>
                </li>
            </ul>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar <?= htmlspecialchars($typeLabels[$type] ?? 'Wilayah') ?></h4>
                        <a href="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>/create" class="btn btn-primary btn-sm ms-auto">Tambah <?= htmlspecialchars($typeLabels[$type] ?? 'Data') ?></a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <form method="GET" action="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>" id="searchForm">
                            <div class="row g-2 align-items-end">
                                <?php if ($type === 'kabupaten' && isset($provinsiList)): ?>
                                <div class="col-12 col-md-3">
                                    <select name="provinsi_id" class="form-select">
                                        <option value="">Semua Provinsi</option>
                                        <?php foreach ($provinsiList as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= (isset($provinsiId) && $provinsiId == $p['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['nama']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($type === 'kecamatan' && isset($provinsiList)): ?>
                                <div class="col-12 col-md-3">
                                    <select name="kabupaten_id" class="form-select">
                                        <option value="">Semua Kabupaten/Kota</option>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($type === 'kelurahan' && isset($provinsiList)): ?>
                                <div class="col-12 col-md-3">
                                    <select name="kecamatan_id" class="form-select">
                                        <option value="">Semua Kecamatan</option>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <div class="col-12 col-md-<?= ($type === 'kabupaten' || $type === 'kecamatan' || $type === 'kelurahan') ? '4' : '6' ?>">
                                    <input type="text" class="form-control" name="search" placeholder="Cari kode atau nama..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-4 col-md-2">
                                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ([25, 50, 100, 200, 500] as $pp): ?>
                                        <option value="<?= $pp ?>" <?= $perPage == $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4 col-md-2">
                                    <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                                </div>
                                <div class="col-4 col-md-2">
                                    <a href="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                                </div>
                            </div>
                            <input type="hidden" name="page" value="1">
                            <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sortBy) ?>">
                            <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sortOrder) ?>">
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="th-sortable"><a href="<?= getSortUrl('kode', $sortBy, $sortOrder, $search, $perPage, $type, isset($provinsiId) ? $provinsiId : (isset($kabupatenId) ? $kabupatenId : (isset($kecamatanId) ? $kecamatanId : null))) ?>">Kode</a></th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('nama', $sortBy, $sortOrder, $search, $perPage, $type, isset($provinsiId) ? $provinsiId : (isset($kabupatenId) ? $kabupatenId : (isset($kecamatanId) ? $kecamatanId : null))) ?>">Nama</a></th>
                                    <?php if ($type === 'kabupaten'): ?>
                                    <th>Provinsi</th>
                                    <th>Tipe</th>
                                    <?php endif; ?>
                                    <?php if ($type === 'kecamatan'): ?>
                                    <th>Kabupaten/Kota</th>
                                    <th>Provinsi</th>
                                    <?php endif; ?>
                                    <?php if ($type === 'kelurahan'): ?>
                                    <th>Kecamatan</th>
                                    <th>Kabupaten/Kota</th>
                                    <th>Provinsi</th>
                                    <th>Tipe</th>
                                    <?php endif; ?>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Get items based on type
                                // Variables are already set at the top of this file
                                $items = [];
                                
                                // Debug output (temporary - remove after fixing)
                                $debugVars = [
                                    'type' => $type,
                                    'provinsi_type' => gettype($provinsi),
                                    'provinsi_count' => is_array($provinsi) ? count($provinsi) : 0,
                                    'provinsi_first' => is_array($provinsi) && !empty($provinsi) ? $provinsi[0]['nama'] ?? 'NO NAME' : 'EMPTY'
                                ];
                                echo "<!-- DEBUG: " . json_encode($debugVars, JSON_UNESCAPED_UNICODE) . " -->\n";
                                
                                switch ($type) {
                                    case 'provinsi':
                                        $items = is_array($provinsi) ? $provinsi : [];
                                        break;
                                    case 'kabupaten':
                                        $items = is_array($kabupaten) ? $kabupaten : [];
                                        break;
                                    case 'kecamatan':
                                        $items = is_array($kecamatan) ? $kecamatan : [];
                                        break;
                                    case 'kelurahan':
                                        $items = is_array($kelurahan) ? $kelurahan : [];
                                        break;
                                    default:
                                        $items = [];
                                }
                                
                                echo "<!-- Items count after switch: " . count($items) . " -->\n";
                                
                                if (empty($items)): 
                                ?>
                                <tr>
                                    <td colspan="<?= $type === 'provinsi' ? '3' : ($type === 'kabupaten' ? '5' : ($type === 'kecamatan' ? '5' : '7')) ?>" class="text-center">Tidak ada data</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['kode']) ?></td>
                                    <td><?= htmlspecialchars($item['nama']) ?></td>
                                    <?php if ($type === 'kabupaten'): ?>
                                    <td><?= htmlspecialchars($item['provinsi_nama'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= $item['tipe'] === 'Kota' ? 'primary' : 'secondary' ?>"><?= htmlspecialchars($item['tipe']) ?></span></td>
                                    <?php endif; ?>
                                    <?php if ($type === 'kecamatan'): ?>
                                    <td><?= htmlspecialchars($item['kabupaten_nama'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($item['provinsi_nama'] ?? '-') ?></td>
                                    <?php endif; ?>
                                    <?php if ($type === 'kelurahan'): ?>
                                    <td><?= htmlspecialchars($item['kecamatan_nama'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($item['kabupaten_nama'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($item['provinsi_nama'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= $item['tipe'] === 'Kelurahan' ? 'info' : 'success' ?>"><?= htmlspecialchars($item['tipe']) ?></span></td>
                                    <?php endif; ?>
                                    <td>
                                        <a href="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>/edit/<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary"><?= icon('pen-to-square', '', 14) ?> Edit</a>
                                        <a href="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>/delete/<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus?')"><?= icon('trash-can', '', 14) ?> Hapus</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>?page=<?= $page - 1 ?>&per_page=<?= $perPage ?>&search=<?= urlencode($search) ?>&sort_by=<?= htmlspecialchars($sortBy) ?>&sort_order=<?= htmlspecialchars($sortOrder) ?><?= isset($provinsiId) ? '&provinsi_id=' . $provinsiId : '' ?><?= isset($kabupatenId) ? '&kabupaten_id=' . $kabupatenId : '' ?><?= isset($kecamatanId) ? '&kecamatan_id=' . $kecamatanId : '' ?>">Previous</a>
                            </li>
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>?page=<?= $i ?>&per_page=<?= $perPage ?>&search=<?= urlencode($search) ?>&sort_by=<?= htmlspecialchars($sortBy) ?>&sort_order=<?= htmlspecialchars($sortOrder) ?><?= isset($provinsiId) ? '&provinsi_id=' . $provinsiId : '' ?><?= isset($kabupatenId) ? '&kabupaten_id=' . $kabupatenId : '' ?><?= isset($kecamatanId) ? '&kecamatan_id=' . $kecamatanId : '' ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>?page=<?= $page + 1 ?>&per_page=<?= $perPage ?>&search=<?= urlencode($search) ?>&sort_by=<?= htmlspecialchars($sortBy) ?>&sort_order=<?= htmlspecialchars($sortOrder) ?><?= isset($provinsiId) ? '&provinsi_id=' . $provinsiId : '' ?><?= isset($kabupatenId) ? '&kabupaten_id=' . $kabupatenId : '' ?><?= isset($kecamatanId) ? '&kecamatan_id=' . $kecamatanId : '' ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                    <div class="mt-3 text-muted">
                        Menampilkan <?= number_format($total, 0, ',', '.') ?> data (Halaman <?= $page ?> dari <?= $totalPages ?>)
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

