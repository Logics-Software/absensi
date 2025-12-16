<?php
$title = 'Manajemen Kelurahan/Desa';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Ensure page is always an integer and at least 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : (isset($page) ? (int)$page : 1);
if ($page < 1) $page = 1;
$totalPages = isset($totalPages) ? max(1, (int)$totalPages) : 1;
$total = isset($total) ? (int)$total : 0;
$perPage = isset($perPage) ? (int)$perPage : 50;
$search = isset($search) ? $search : '';
$sortBy = isset($sortBy) ? $sortBy : 'kode';
$sortOrder = isset($sortOrder) ? $sortOrder : 'ASC';
$kecamatanId = isset($kecamatanId) ? (int)$kecamatanId : null;

// Helper function to generate sort URL
if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $perPage, $kecamatanId = null) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = [
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ];
        if ($kecamatanId) $params['kecamatan_id'] = $kecamatanId;
        return '/wilayah/kelurahan?' . http_build_query($params);
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
                    <li class="breadcrumb-item active">Kelurahan/Desa</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-0" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" href="/wilayah/provinsi">Provinsi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/wilayah/kabupaten">Kabupaten/Kota</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/wilayah/kecamatan">Kecamatan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/wilayah/kelurahan">Kelurahan/Desa</a>
                </li>
            </ul>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Kelurahan/Desa</h4>
                        <a href="/wilayah/kelurahan/create" class="btn btn-primary btn-sm ms-auto">Tambah Kelurahan/Desa</a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <form method="GET" action="/wilayah/kelurahan" id="searchForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-3">
                                    <select name="kecamatan_id" class="form-select" id="filterKecamatan">
                                        <option value="">Semua Kecamatan</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
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
                                <div class="col-4 col-md-1">
                                    <a href="/wilayah/kelurahan?page=1&per_page=50&sort_by=<?= htmlspecialchars($sortBy) ?>&sort_order=<?= htmlspecialchars($sortOrder) ?>" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
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
                                    <th class="th-sortable"><a href="<?= getSortUrl('kode', $sortBy, $sortOrder, $search, $perPage, $kecamatanId) ?>">Kode</a></th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('nama', $sortBy, $sortOrder, $search, $perPage, $kecamatanId) ?>">Nama</a></th>
                                    <th>Kecamatan</th>
                                    <th>Kabupaten/Kota</th>
                                    <th>Provinsi</th>
                                    <th>Tipe</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kelurahan)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($kelurahan as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['kode']) ?></td>
                                    <td><?= htmlspecialchars($item['nama']) ?></td>
                                    <td><?= htmlspecialchars($item['kecamatan_nama'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($item['kabupaten_nama'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($item['provinsi_nama'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= $item['tipe'] === 'Kelurahan' ? 'info' : 'success' ?>"><?= htmlspecialchars($item['tipe']) ?></span></td>
                                    <td>
                                        <a href="/wilayah/kelurahan/edit/<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary"><?= icon('pen-to-square', '', 14) ?> Edit</a>
                                        <a href="/wilayah/kelurahan/delete/<?= $item['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus?')"><?= icon('trash-can', '', 14) ?> Hapus</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <?php
                    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : (isset($page) ? (int)$page : 1);
                    if ($currentPage < 1) {
                        $currentPage = 1;
                    }
                    $page = $currentPage;
                    $totalPages = (int)$totalPages;
                    $perPage = (int)$perPage;
                    
                    $buildLink = function ($p) use ($perPage, $search, $sortBy, $sortOrder, $kecamatanId) {
                        $params = '?page=' . $p . '&per_page=' . $perPage . '&search=' . urlencode($search) . '&sort_by=' . $sortBy . '&sort_order=' . $sortOrder;
                        if ($kecamatanId) $params .= '&kecamatan_id=' . $kecamatanId;
                        return $params;
                    };
                    $maxLinks = 3;
                    $half = (int)floor($maxLinks / 2);
                    $start = max(1, $page - $half);
                    $end = min($totalPages, $start + $maxLinks - 1);
                    if ($end - $start + 1 < $maxLinks) {
                        $start = max(1, $end - $maxLinks + 1);
                    }
                    ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <?php
                                $prevPage = (int)max(1, (int)$page - 1);
                                if ($prevPage < 1) $prevPage = 1;
                                ?>
                                <a class="page-link" href="/wilayah/kelurahan<?php echo $buildLink($prevPage); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/wilayah/kelurahan' . $buildLink(1) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/wilayah/kelurahan' . $buildLink($i) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="/wilayah/kelurahan' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <?php
                                $nextPage = $page + 1;
                                if ($nextPage > $totalPages) {
                                    $nextPage = $totalPages;
                                }
                                $nextPage = (int)$nextPage;
                                ?>
                                <a class="page-link" href="/wilayah/kelurahan<?php echo $buildLink($nextPage); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                    <div class="mt-3 text-muted text-center">
                        <?php 
                        // Ensure page is at least 1 for display
                        $displayPage = max(1, (int)$page);
                        $displayTotalPages = max(1, (int)$totalPages);
                        ?>
                        Menampilkan <?= number_format($total, 0, ',', '.') ?> data (Halaman <?= $displayPage ?> dari <?= $displayTotalPages ?>)
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

