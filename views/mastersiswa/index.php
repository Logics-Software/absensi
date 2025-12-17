<?php
$title = 'Master Siswa';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Helper function to generate sort URL
if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $perPage, $filterTahunAjaran, $filterKelas) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = [
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ];
        if ($filterTahunAjaran !== null && $filterTahunAjaran > 0) {
            $params['filter_tahunajaran'] = $filterTahunAjaran;
        }
        if ($filterKelas !== null && $filterKelas > 0) {
            $params['filter_kelas'] = $filterKelas;
        }
        return '/mastersiswa?' . http_build_query($params);
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
                    <li class="breadcrumb-item active">Master Siswa</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Master Siswa</h4>
                        <a href="/mastersiswa/create" class="btn btn-primary btn-sm ms-auto">Tambah Siswa</a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <form method="GET" action="/mastersiswa" id="searchForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-3">
                                    <input type="text" class="form-control" name="search" placeholder="Cari NISN, NIK, nama, email..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-12 col-md-3">
                                    <select name="filter_tahunajaran" id="filter_tahunajaran" class="form-select" onchange="loadKelasFilter(); this.form.submit();">
                                        <option value="">Semua Tahun Ajaran</option>
                                        <?php foreach ($tahunAjaranList as $ta): ?>
                                            <option value="<?= $ta['id'] ?>" <?= ($filterTahunAjaran == $ta['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($ta['tahunajaran']) ?>
                                                <?= ($ta['status'] == 'aktif') ? ' (Aktif)' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <select name="filter_kelas" id="filter_kelas" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Kelas</option>
                                        <?php foreach ($kelasList as $kelas): ?>
                                            <option value="<?= $kelas['idkelas'] ?>" <?= ($filterKelas == $kelas['idkelas']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kelas['namakelas']) ?> (<?= htmlspecialchars($kelas['kelas']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4 col-md-1">
                                    <select name="per_page" id="per_page" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ([10, 25, 50, 100, 200, 500, 1000] as $pp): ?>
                                        <option value="<?= $pp ?>" <?= $perPage == $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4 col-md-1">
                                    <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                                </div>
                                <div class="col-4 col-md-1">
                                    <a href="/mastersiswa?page=1&per_page=10&sort_by=<?= htmlspecialchars($sortBy) ?>&sort_order=<?= htmlspecialchars($sortOrder) ?><?= $activeTahunAjaran ? '&filter_tahunajaran=' . $activeTahunAjaran['id'] : '' ?>" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
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
                                    <th>Foto</th>
                                    <th>NISN</th>
                                    <th>No. Absensi</th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('namasiswa', $sortBy, $sortOrder, $search, $perPage, $filterTahunAjaran, $filterKelas) ?>">Nama Siswa</a></th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Kelas</th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('status', $sortBy, $sortOrder, $search, $perPage, $filterTahunAjaran, $filterKelas) ?>">Status</a></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($mastersiswaList)): ?>
                                <tr>
                                    <td colspan="12" class="text-center">Tidak ada data</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $no = (max(1, (int)$page) - 1) * max(1, (int)$perPage) + 1;
                                foreach ($mastersiswaList as $siswa): 
                                ?>
                                <tr>
                                    <td align="center">
                                        <?php if ($siswa['foto'] && file_exists(__DIR__ . '/../../uploads/' . $siswa['foto'])): ?>
                                        <img src="<?= htmlspecialchars($baseUrl) ?>/uploads/<?= htmlspecialchars($siswa['foto']) ?>" alt="Foto" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                        <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="text-white fw-bold"><?= strtoupper(substr($siswa['namasiswa'], 0, 1)) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($siswa['nisn'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($siswa['noabsensi'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($siswa['namasiswa']) ?></td>
                                    <td><?= htmlspecialchars($siswa['jeniskelamin'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($siswa['tahunajaran_nama'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($siswa['kelas_nama'] ?? '-') ?></td>
                                    <td align="center">
                                        <span class="badge bg-<?= $siswa['status'] == 'aktif' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($siswa['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="/mastersiswa/edit/<?= $siswa['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <?= icon('pen-to-square', 'me-0 mb-1', 16) ?>
                                            </a>
                                            <a href="/mastersiswa/delete/<?= $siswa['id'] ?>" class="btn btn-sm btn-danger" onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus siswa <?= htmlspecialchars($siswa['namasiswa']) ?>?', this.href); return false;" title="Hapus">
                                                <?= icon('trash-can', 'me-0 mb-1', 16) ?>
                                            </a>
                                        </div>
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
                    
                    $buildLink = function ($p) use ($perPage, $search, $sortBy, $sortOrder, $filterTahunAjaran, $filterKelas) {
                        $params = [
                            'page' => $p,
                            'per_page' => $perPage,
                            'search' => $search,
                            'sort_by' => $sortBy,
                            'sort_order' => $sortOrder
                        ];
                        if ($filterTahunAjaran !== null && $filterTahunAjaran > 0) {
                            $params['filter_tahunajaran'] = $filterTahunAjaran;
                        }
                        if ($filterKelas !== null && $filterKelas > 0) {
                            $params['filter_kelas'] = $filterKelas;
                        }
                        return '?' . http_build_query($params);
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
                                <?php $prevPage = (int)max(1, (int)$page - 1); ?>
                                <a class="page-link" href="/mastersiswa<?php echo $buildLink($prevPage); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/mastersiswa' . $buildLink(1) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/mastersiswa' . $buildLink($i) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="/mastersiswa' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <?php $nextPage = $page + 1; if ($nextPage > $totalPages) $nextPage = $totalPages; $nextPage = (int)$nextPage; ?>
                                <a class="page-link" href="/mastersiswa<?php echo $buildLink($nextPage); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadKelasFilter() {
    const tahunAjaranId = document.getElementById('filter_tahunajaran').value;
    const kelasSelect = document.getElementById('filter_kelas');
    
    // Clear existing options except first
    kelasSelect.innerHTML = '<option value="">Semua Kelas</option>';
    
    if (!tahunAjaranId || tahunAjaranId === '') {
        return;
    }
    
    // Fetch kelas from API
    fetch(`/mastersiswa/api/getkelas?idtahunajaran=${tahunAjaranId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(kelas => {
                const option = document.createElement('option');
                option.value = kelas.idkelas;
                option.textContent = `${kelas.namakelas} (${kelas.kelas})`;
                kelasSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading kelas:', error);
        });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

