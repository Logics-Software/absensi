<?php
// Ensure all required variables are available
if (!isset($holidayList)) $holidayList = [];
if (!isset($search)) $search = '';
if (!isset($perPage)) $perPage = 10;
if (!isset($sortBy)) $sortBy = 'holiday_date';
if (!isset($sortOrder)) $sortOrder = 'ASC';
if (!isset($year)) $year = (int)date('Y');
if (!isset($page)) $page = 1;
if (!isset($totalPages)) $totalPages = 1;

// Helper function to generate sort URL
if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $perPage, $year) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = http_build_query([
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'sort_by' => $column,
            'sort_order' => $newSortOrder,
            'year' => $year,
            'view' => 'list'
        ]);
        return '/holiday?' . $params;
    }
}
?>

<!-- List View -->
<div class="row mb-3">
    <form method="GET" action="/holiday" id="searchForm">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Cari nama hari libur..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="col-12 col-md-2">
                <label for="year" class="form-label small mb-1">Tahun</label>
                <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                    <?php 
                    $currentYear = (int)date('Y');
                    for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++): 
                    ?>
                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
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
            <div class="col-4 col-md-2">
                <label class="form-label small mb-1 d-block">&nbsp;</label>
                <button type="submit" class="btn btn-filter btn-secondary w-100">Filter</button>
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label small mb-1 d-block">&nbsp;</label>
                <a href="/holiday?view=list&page=1&per_page=10&sort_by=holiday_date&sort_order=ASC" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
            </div>
        </div>
        <input type="hidden" name="view" value="list">
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
                <th class="th-sortable"><a href="<?= getSortUrl('holiday_date', $sortBy, $sortOrder, $search ?? '', $perPage, $year) ?>">Tanggal</a></th>
                <th class="th-sortable"><a href="<?= getSortUrl('holiday_name', $sortBy, $sortOrder, $search ?? '', $perPage, $year) ?>">Nama Hari Libur</a></th>
                <th class="th-sortable"><a href="<?= getSortUrl('is_national', $sortBy, $sortOrder, $search ?? '', $perPage, $year) ?>">Jenis</a></th>
                <th class="th-sortable"><a href="<?= getSortUrl('is_recurring_yearly', $sortBy, $sortOrder, $search ?? '', $perPage, $year) ?>">Berulang</a></th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($holidayList)): ?>
            <tr>
                <td colspan="6" class="text-center">Tidak ada data</td>
            </tr>
            <?php else: ?>
            <?php 
            $no = (max(1, (int)$page) - 1) * max(1, (int)$perPage) + 1;
            foreach ($holidayList as $holiday): 
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= date('d/m/Y', strtotime($holiday['holiday_date'])) ?></td>
                <td><?= htmlspecialchars($holiday['holiday_name']) ?></td>
                <td>
                    <span class="badge bg-<?= $holiday['is_national'] == 1 ? 'danger' : 'warning' ?>">
                        <?= $holiday['is_national'] == 1 ? 'Nasional' : 'Lokal' ?>
                    </span>
                </td>
                <td>
                    <?php if ($holiday['is_recurring_yearly'] == 1): ?>
                        <span class="badge bg-info"><i class="fas fa-sync-alt"></i> Berulang</span>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="/holiday/edit/<?= $holiday['holiday_id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                            <?= icon('pen-to-square', 'me-0 mb-1', 16) ?>
                        </a>
                        <a href="/holiday/delete/<?= $holiday['holiday_id'] ?>" class="btn btn-sm btn-danger" onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus hari libur <?= htmlspecialchars($holiday['holiday_name']) ?>?', this.href); return false;" title="Hapus">
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

<?php if (!empty($totalPages) && $totalPages > 1): ?>
<?php
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : (isset($page) ? (int)$page : 1);
if ($currentPage < 1) {
    $currentPage = 1;
}
$page = $currentPage;
$totalPages = isset($totalPages) ? (int)$totalPages : 1;
$perPage = isset($perPage) ? (int)$perPage : 10;

$buildLink = function ($p) use ($perPage, $search, $sortBy, $sortOrder, $year) {
    $params = [
        'page' => $p,
        'per_page' => $perPage,
        'search' => $search,
        'sort_by' => $sortBy,
        'sort_order' => $sortOrder,
        'year' => $year,
        'view' => 'list'
    ];
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
            <a class="page-link" href="/holiday<?php echo $buildLink($prevPage); ?>">Previous</a>
        </li>
        <?php
        if ($start > 1) {
            echo '<li class="page-item"><a class="page-link" href="/holiday' . $buildLink(1) . '">1</a></li>';
            if ($start > 2) {
                echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
            }
        }
        for ($i = $start; $i <= $end; $i++) {
            echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/holiday' . $buildLink($i) . '">' . $i . '</a></li>';
        }
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
            }
            echo '<li class="page-item"><a class="page-link" href="/holiday' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
        }
        ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <?php $nextPage = $page + 1; if ($nextPage > $totalPages) $nextPage = $totalPages; $nextPage = (int)$nextPage; ?>
            <a class="page-link" href="/holiday<?php echo $buildLink($nextPage); ?>">Next</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

