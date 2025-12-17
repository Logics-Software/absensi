<?php
$title = 'Setting Kelas';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Helper function to generate sort URL
if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $perPage, $filterTahunAjaran = null) {
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
        return '/kelas?' . http_build_query($params);
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
                    <li class="breadcrumb-item active">Kelas</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Kelas</h4>
                        <a href="/kelas/create" class="btn btn-primary btn-sm ms-auto">Tambah Kelas</a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <form method="GET" action="/kelas" id="searchForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-4">
                                    <input type="text" class="form-control" name="search" placeholder="Cari nama kelas, kelas..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label for="filter_tahunajaran" class="form-label small mb-1">Tahun Ajaran</label>
                                    <select name="filter_tahunajaran" id="filter_tahunajaran" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Tahun Ajaran</option>
                                        <?php foreach ($tahunAjaranList as $ta): ?>
                                            <option value="<?= $ta['id'] ?>" <?= ($filterTahunAjaran == $ta['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($ta['tahunajaran']) ?>
                                                <?= ($ta['status'] == 'aktif') ? ' (Aktif)' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4 col-md-2">
                                    <label for="per_page" class="form-label small mb-1">Per Halaman</label>
                                    <select name="per_page" id="per_page" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ([10, 25, 50, 100, 200, 500, 1000] as $pp): ?>
                                        <option value="<?= $pp ?>" <?= $perPage == $pp ? 'selected' : '' ?>><?= $pp ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4 col-md-1">
                                    <label class="form-label small mb-1 d-block">&nbsp;</label>
                                    <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="form-label small mb-1 d-block">&nbsp;</label>
                                    <a href="/kelas?page=1&per_page=10&sort_by=<?= htmlspecialchars($sortBy) ?>&sort_order=<?= htmlspecialchars($sortOrder) ?><?= $activeTahunAjaran ? '&filter_tahunajaran=' . $activeTahunAjaran['id'] : '' ?>" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
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
                                    <th>No.</th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('tahunajaran', $sortBy, $sortOrder, $search, $perPage, $filterTahunAjaran) ?>">Tahun Ajaran</a></th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('kelas', $sortBy, $sortOrder, $search, $perPage, $filterTahunAjaran) ?>">Kelas</a></th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('namakelas', $sortBy, $sortOrder, $search, $perPage, $filterTahunAjaran) ?>">Nama Kelas</a></th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('jurusan', $sortBy, $sortOrder, $search, $perPage, $filterTahunAjaran) ?>">Jurusan</a></th>
                                    <th>Wali Kelas</th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('status', $sortBy, $sortOrder, $search, $perPage, $filterTahunAjaran) ?>">Status</a></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($kelasList)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $no = (max(1, (int)$page) - 1) * max(1, (int)$perPage) + 1;
                                foreach ($kelasList as $kelas): 
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($kelas['tahunajaran_nama'] ?? '-') ?></td>
                                    <td align="center"><?= htmlspecialchars($kelas['kelas']) ?></td>
                                    <td><?= htmlspecialchars($kelas['namakelas']) ?></td>
                                    <td><?= htmlspecialchars($kelas['jurusan_nama'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($kelas['guru_nama'] ?? '-') ?></td>
                                    <td align="center">
                                        <span class="badge bg-<?= $kelas['status'] == 'aktif' ? 'success' : 'danger' ?>">
                                            <?= ucfirst($kelas['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="/kelas/edit/<?= $kelas['idkelas'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <?= icon('pen-to-square', 'me-0 mb-1', 16) ?>
                                            </a>
                                            <a href="/kelas/delete/<?= $kelas['idkelas'] ?>" class="btn btn-sm btn-danger" onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus kelas <?= htmlspecialchars($kelas['namakelas']) ?>?', this.href); return false;" title="Hapus">
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
                    
                    $buildLink = function ($p) use ($perPage, $search, $sortBy, $sortOrder, $filterTahunAjaran) {
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
                                <a class="page-link" href="/kelas<?php echo $buildLink($prevPage); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/kelas' . $buildLink(1) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/kelas' . $buildLink($i) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="/kelas' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <?php $nextPage = $page + 1; if ($nextPage > $totalPages) $nextPage = $totalPages; $nextPage = (int)$nextPage; ?>
                                <a class="page-link" href="/kelas<?php echo $buildLink($nextPage); ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

