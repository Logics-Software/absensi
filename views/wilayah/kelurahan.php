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
$provinsiId = isset($provinsiId) ? (int)$provinsiId : null;
$kabupatenId = isset($kabupatenId) ? (int)$kabupatenId : null;
$kecamatanId = isset($kecamatanId) ? (int)$kecamatanId : null;

// Helper function to generate sort URL
if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $perPage, $kecamatanId = null, $provinsiId = null, $kabupatenId = null) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = [
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ];
        if ($provinsiId) $params['provinsi_id'] = $provinsiId;
        if ($kabupatenId) $params['kabupaten_id'] = $kabupatenId;
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
                                <div class="col-12 col-md-2">
                                    <select name="provinsi_id" class="form-select" id="filterProvinsi" onchange="loadFilterKabupaten(); document.getElementById('searchForm').submit();">
                                        <option value="">Semua Provinsi</option>
                                        <?php foreach ($provinsiList as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= (isset($provinsiId) && $provinsiId == $p['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['nama']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <select name="kabupaten_id" class="form-select" id="filterKabupaten" onchange="loadFilterKecamatan(); document.getElementById('searchForm').submit();">
                                        <option value="">Semua Kabupaten/Kota</option>
                                        <?php if (isset($kabupatenList) && !empty($kabupatenList)): ?>
                                        <?php foreach ($kabupatenList as $k): ?>
                                        <option value="<?= $k['id'] ?>" <?= (isset($kabupatenId) && $kabupatenId == $k['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k['nama']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <select name="kecamatan_id" class="form-select" id="filterKecamatan" onchange="document.getElementById('searchForm').submit()">
                                        <option value="">Semua Kecamatan</option>
                                        <?php if (isset($kecamatanList) && !empty($kecamatanList)): ?>
                                        <?php foreach ($kecamatanList as $k): ?>
                                        <option value="<?= $k['id'] ?>" <?= (isset($kecamatanId) && $kecamatanId == $k['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k['nama']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <input type="text" class="form-control" name="search" placeholder="Cari kode atau nama..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-4 col-md-1">
                                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ([25, 50, 100, 200, 500] as $pp): ?>
                                        <option value="<?= $pp ?>" <?= $perPage == $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4 col-md-1">
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
                                    <th class="th-sortable"><a href="<?= getSortUrl('kode', $sortBy, $sortOrder, $search, $perPage, $kecamatanId, $provinsiId, $kabupatenId) ?>">Kode</a></th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('nama', $sortBy, $sortOrder, $search, $perPage, $kecamatanId, $provinsiId, $kabupatenId) ?>">Nama</a></th>
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
                                        <a href="/wilayah/kelurahan/edit/<?= $item['id'] ?>" class="btn btn-sm btn-primary"><?= icon('pen-to-square', '', 14) ?> Edit</a>
                                        <a href="/wilayah/kelurahan/delete/<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus kelurahan <?= htmlspecialchars($item['nama']) ?>?', this.href); return false;"><?= icon('trash-can', '', 14) ?> Hapus</a>
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
                    
                    $buildLink = function ($p) use ($perPage, $search, $sortBy, $sortOrder, $provinsiId, $kabupatenId, $kecamatanId) {
                        $params = '?page=' . $p . '&per_page=' . $perPage . '&search=' . urlencode($search) . '&sort_by=' . $sortBy . '&sort_order=' . $sortOrder;
                        if ($provinsiId) $params .= '&provinsi_id=' . $provinsiId;
                        if ($kabupatenId) $params .= '&kabupaten_id=' . $kabupatenId;
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

<script>
function loadFilterKabupaten() {
    return new Promise((resolve, reject) => {
        const provinsiId = document.getElementById('filterProvinsi').value;
        const kabupatenSelect = document.getElementById('filterKabupaten');
        const kecamatanSelect = document.getElementById('filterKecamatan');
        const currentKabupatenValue = kabupatenSelect.value;
        
        kabupatenSelect.innerHTML = '<option value="">Semua Kabupaten/Kota</option>';
        kecamatanSelect.innerHTML = '<option value="">Semua Kecamatan</option>';
        
        if (!provinsiId) {
            resolve();
            return;
        }
        
        fetch(`/wilayah/api/kabupaten?provinsi_id=${provinsiId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.nama;
                    if (item.id == currentKabupatenValue) {
                        option.selected = true;
                    }
                    kabupatenSelect.appendChild(option);
                });
                
                // If kabupaten was already selected, load kecamatan
                if (currentKabupatenValue) {
                    loadFilterKecamatan();
                }
                resolve();
            })
            .catch(error => {
                console.error('Error loading kabupaten:', error);
                reject(error);
            });
    });
}

function loadFilterKecamatan() {
    const kabupatenSelect = document.getElementById('filterKabupaten');
    const kecamatanSelect = document.getElementById('filterKecamatan');
    
    if (!kabupatenSelect || !kecamatanSelect) {
        console.error('Filter elements not found');
        return;
    }
    
    const kabupatenId = kabupatenSelect.value;
    // Get kecamatanId from URL parameter to preserve selection after form submit
    const urlParams = new URLSearchParams(window.location.search);
    const kecamatanIdFromUrl = urlParams.get('kecamatan_id');
    const currentKecamatanValue = kecamatanIdFromUrl || kecamatanSelect.value;
    
    kecamatanSelect.innerHTML = '<option value="">Semua Kecamatan</option>';
    
    if (!kabupatenId) {
        return;
    }
    
    fetch(`/wilayah/api/kecamatan?kabupaten_id=${kabupatenId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.nama;
                    // Use == for loose comparison to handle string vs number
                    if (currentKecamatanValue && (item.id == currentKecamatanValue || String(item.id) === String(currentKecamatanValue))) {
                        option.selected = true;
                    }
                    kecamatanSelect.appendChild(option);
                });
            } else {
                console.log('No kecamatan data found for kabupaten:', kabupatenId);
            }
        })
        .catch(error => {
            console.error('Error loading kecamatan:', error);
        });
}

// Load kabupaten and kecamatan on page load if provinsi/kabupaten is selected
document.addEventListener('DOMContentLoaded', function() {
    const provinsiSelect = document.getElementById('filterProvinsi');
    const kabupatenSelect = document.getElementById('filterKabupaten');
    const kecamatanSelect = document.getElementById('filterKecamatan');
    
    if (!provinsiSelect || !kabupatenSelect || !kecamatanSelect) {
        console.error('Filter elements not found');
        return;
    }
    
    const provinsiId = provinsiSelect.value;
    const kabupatenId = kabupatenSelect.value;
    const kecamatanId = kecamatanSelect.value;
    
    // Check if kecamatan dropdown already has options (from server-side rendering)
    const hasKecamatanOptions = kecamatanSelect.options.length > 1;
    const hasSelectedKecamatan = kecamatanId && kecamatanSelect.querySelector(`option[value="${kecamatanId}"]`);
    
    // Only load via AJAX if:
    // 1. Kecamatan dropdown doesn't have options yet, OR
    // 2. Kecamatan is selected but not found in current options
    const needToLoadKecamatan = !hasKecamatanOptions || (kecamatanId && !hasSelectedKecamatan);
    
    if (provinsiId) {
        // Load kabupaten first, then kecamatan if needed
        loadFilterKabupaten().then(() => {
            if (kabupatenId && needToLoadKecamatan) {
                // Wait a bit for kabupaten to load, then load kecamatan
                setTimeout(() => {
                    loadFilterKecamatan();
                }, 300);
            }
        }).catch(error => {
            console.error('Error loading kabupaten on page load:', error);
        });
    } else if (kabupatenId && needToLoadKecamatan) {
        // If only kabupaten is selected (without provinsi), load kecamatan directly
        setTimeout(() => {
            loadFilterKecamatan();
        }, 100);
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

