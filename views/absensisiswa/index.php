<?php
$title = 'Absensi Siswa';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Helper function to generate sort URL
if (!function_exists('getSortUrl')) {
    function getSortUrl($column, $currentSortBy, $currentSortOrder, $search, $perPage, $period, $dateFrom, $dateTo) {
        $newSortOrder = ($currentSortBy == $column && $currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
        $params = [
            'page' => 1,
            'per_page' => $perPage,
            'search' => $search,
            'sort_by' => $column,
            'sort_order' => $newSortOrder
        ];
        if ($period !== null && $period !== '') {
            $params['period'] = $period;
        }
        if ($dateFrom !== null && $dateFrom !== '') {
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $params['date_to'] = $dateTo;
        }
        return '/absensisiswa?' . http_build_query($params);
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
                    <li class="breadcrumb-item active">Absensi Siswa</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Daftar Absensi Siswa</h4>
                        <a href="/absensisiswa/create" class="btn btn-primary btn-sm ms-auto">Tambah Absensi</a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <form method="GET" action="/absensisiswa" id="searchForm">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-3">
                                    <input type="text" class="form-control" name="search" placeholder="Cari NISN, nama siswa, keterangan..." value="<?= htmlspecialchars($search) ?>">
                                </div>
                                <div class="col-12 col-md-2">
                                    <select name="period" id="period" class="form-select" onchange="toggleCustomDate(); this.form.submit();">
                                        <option value="">Semua Periode</option>
                                        <option value="today" <?= ($period == 'today') ? 'selected' : '' ?>>Hari Ini</option>
                                        <option value="this_week" <?= ($period == 'this_week') ? 'selected' : '' ?>>Minggu Ini</option>
                                        <option value="last_week" <?= ($period == 'last_week') ? 'selected' : '' ?>>Minggu Lalu</option>
                                        <option value="this_month" <?= ($period == 'this_month') ? 'selected' : '' ?>>Bulan Ini</option>
                                        <option value="last_month" <?= ($period == 'last_month') ? 'selected' : '' ?>>Bulan Lalu</option>
                                        <option value="custom" <?= ($period == 'custom') ? 'selected' : '' ?>>Custom</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2" id="dateFromContainer" style="display: <?= ($period == 'custom') ? 'block' : 'none' ?>;">
                                    <input type="date" class="form-control" name="date_from" id="date_from" placeholder="Dari Tanggal" value="<?= htmlspecialchars($dateFrom ?? '') ?>" onchange="this.form.submit();">
                                </div>
                                <div class="col-12 col-md-2" id="dateToContainer" style="display: <?= ($period == 'custom') ? 'block' : 'none' ?>;">
                                    <input type="date" class="form-control" name="date_to" id="date_to" placeholder="Sampai Tanggal" value="<?= htmlspecialchars($dateTo ?? '') ?>" onchange="this.form.submit();">
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
                                    <a href="/absensisiswa?page=1&per_page=10&sort_by=<?= htmlspecialchars($sortBy) ?>&sort_order=<?= htmlspecialchars($sortOrder) ?>" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
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
                                    <th class="th-sortable"><a href="<?= getSortUrl('id', $sortBy, $sortOrder, $search, $perPage, $period, $dateFrom, $dateTo) ?>">ID</a></th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('nisn', $sortBy, $sortOrder, $search, $perPage, $period, $dateFrom, $dateTo) ?>">NISN</a></th>
                                    <th>Nama Siswa</th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('tanggalabsen', $sortBy, $sortOrder, $search, $perPage, $period, $dateFrom, $dateTo) ?>">Tanggal Absen</a></th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('jammasuk', $sortBy, $sortOrder, $search, $perPage, $period, $dateFrom, $dateTo) ?>">Jam Masuk</a></th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('jamkeluar', $sortBy, $sortOrder, $search, $perPage, $period, $dateFrom, $dateTo) ?>">Jam Pulang</a></th>
                                    <th>Durasi</th>
                                    <th class="th-sortable"><a href="<?= getSortUrl('status', $sortBy, $sortOrder, $search, $perPage, $period, $dateFrom, $dateTo) ?>">Status</a></th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($absensiList)): ?>
                                <tr>
                                    <td colspan="10" class="text-center">Tidak ada data</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                $no = (max(1, (int)$page) - 1) * max(1, (int)$perPage) + 1;
                                foreach ($absensiList as $absensi): 
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($absensi['id']) ?></td>
                                    <td><?= htmlspecialchars($absensi['nisn'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($absensi['namasiswa'] ?? '-') ?></td>
                                    <td><?= !empty($absensi['tanggalabsen']) ? date('d/m/Y', strtotime($absensi['tanggalabsen'])) : '-' ?></td>
                                    <td><?= !empty($absensi['jammasuk']) ? date('H:i', strtotime($absensi['jammasuk'])) : '-' ?></td>
                                    <td><?= !empty($absensi['jamkeluar']) ? date('H:i', strtotime($absensi['jamkeluar'])) : '-' ?></td>
                                    <td>
                                        <?php if (!empty($absensi['jammasuk']) && !empty($absensi['jamkeluar'])): ?>
                                            <?= sprintf('%02d:%02d:%02d', $absensi['durasijam'], $absensi['durasimenit'], $absensi['durasidetik']) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td align="center">
                                        <?php
                                        $statusColors = [
                                            'hadir' => 'success',
                                            'alpha' => 'danger',
                                            'ijin' => 'warning',
                                            'sakit' => 'info'
                                        ];
                                        $statusLabels = [
                                            'hadir' => 'Hadir',
                                            'alpha' => 'Alpha',
                                            'ijin' => 'Ijin',
                                            'sakit' => 'Sakit'
                                        ];
                                        $statusColor = $statusColors[$absensi['status']] ?? 'secondary';
                                        $statusLabel = $statusLabels[$absensi['status']] ?? ucfirst($absensi['status']);
                                        ?>
                                        <span class="badge bg-<?= $statusColor ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($absensi['keterangan'] ?? '-') ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="/absensisiswa/edit/<?= $absensi['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <?= icon('pen-to-square', 'me-0 mb-1', 16) ?>
                                            </a>
                                            <a href="/absensisiswa/delete/<?= $absensi['id'] ?>" class="btn btn-sm btn-danger" onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus absensi ini?', this.href); return false;" title="Hapus">
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
                    
                    $buildLink = function ($p) use ($perPage, $search, $sortBy, $sortOrder, $period, $dateFrom, $dateTo) {
                        $params = [
                            'page' => $p,
                            'per_page' => $perPage,
                            'search' => $search,
                            'sort_by' => $sortBy,
                            'sort_order' => $sortOrder
                        ];
                        if ($period !== null && $period !== '') {
                            $params['period'] = $period;
                        }
                        if ($dateFrom !== null && $dateFrom !== '') {
                            $params['date_from'] = $dateFrom;
                        }
                        if ($dateTo !== null && $dateTo !== '') {
                            $params['date_to'] = $dateTo;
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
                                <a class="page-link" href="/absensisiswa<?php echo $buildLink($prevPage); ?>">Previous</a>
                            </li>
                            <?php
                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="/absensisiswa' . $buildLink(1) . '">1</a></li>';
                                if ($start > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                            }
                            for ($i = $start; $i <= $end; $i++) {
                                echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="/absensisiswa' . $buildLink($i) . '">' . $i . '</a></li>';
                            }
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="/absensisiswa' . $buildLink($totalPages) . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <?php $nextPage = $page + 1; if ($nextPage > $totalPages) $nextPage = $totalPages; $nextPage = (int)$nextPage; ?>
                                <a class="page-link" href="/absensisiswa<?php echo $buildLink($nextPage); ?>">Next</a>
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
function toggleCustomDate() {
    const period = document.getElementById('period').value;
    const dateFromContainer = document.getElementById('dateFromContainer');
    const dateToContainer = document.getElementById('dateToContainer');
    
    if (period === 'custom') {
        dateFromContainer.style.display = 'block';
        dateToContainer.style.display = 'block';
    } else {
        dateFromContainer.style.display = 'none';
        dateToContainer.style.display = 'none';
    }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

