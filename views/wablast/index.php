<?php
$title = 'WA Blast';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">WA Blast</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">WA Blast Campaigns</h4>
                        <a href="/wablast/create" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="margin-right: 0.5rem;">
                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                            </svg>
                            Buat Campaign Baru
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" action="/wablast" class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari campaign..." 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="per_page" class="form-select" onchange="this.form.submit();">
                                    <option value="20" <?= $perPage == 20 ? 'selected' : '' ?>>20 per halaman</option>
                                    <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50 per halaman</option>
                                    <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100 per halaman</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-filter btn-primary w-100">Cari</button>
                            </div>
                            <div class="col-md-4 text-end">
                                <small class="text-muted">Total: <?= number_format($totalCampaigns) ?> campaign</small>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Campaigns Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Campaign</th>
                                    <th>Template</th>
                                    <th>Tipe Penerima</th>
                                    <th>Total Penerima</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($campaigns)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada campaign</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($campaigns as $campaign): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($campaign['nama']) ?></strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($campaign['template_nama'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?php
                                        $tipeLabels = [
                                            'siswa' => 'Siswa',
                                            'wali' => 'Wali Siswa',
                                            'guru' => 'Guru',
                                            'custom' => 'Custom'
                                        ];
                                        echo $tipeLabels[$campaign['tipe_recipient']] ?? $campaign['tipe_recipient'];
                                        ?>
                                    </td>
                                    <td><?= number_format($campaign['total_recipient']) ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'scheduled' => 'info',
                                            'sending' => 'warning',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                            'cancelled' => 'dark'
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Draft',
                                            'scheduled' => 'Terjadwal',
                                            'sending' => 'Mengirim',
                                            'completed' => 'Selesai',
                                            'failed' => 'Gagal',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        $color = $statusColors[$campaign['status']] ?? 'secondary';
                                        $label = $statusLabels[$campaign['status']] ?? $campaign['status'];
                                        ?>
                                        <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $sent = $campaign['total_sent'] ?? 0;
                                        $total = $campaign['total_recipient'] ?? 0;
                                        $percentage = $total > 0 ? ($sent / $total) * 100 : 0;
                                        ?>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?= $percentage ?>%" 
                                                 aria-valuenow="<?= $percentage ?>" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                <?= number_format($percentage, 1) ?>%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?= number_format($sent) ?> / <?= number_format($total) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($campaign['created_at'])) ?>
                                        <br>
                                        <small class="text-muted">oleh <?= htmlspecialchars($campaign['created_by_name'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <a href="/wablast/view/<?= $campaign['id'] ?>" class="btn btn-sm btn-info">Detail</a>
                                        <?php if ($campaign['status'] === 'draft' || $campaign['status'] === 'failed'): ?>
                                        <form method="POST" action="/wablast/send/<?= $campaign['id'] ?>" style="display: inline;" 
                                              onsubmit="return confirm('Yakin ingin mengirim campaign ini?');">
                                            <button type="submit" class="btn btn-sm btn-success">Kirim</button>
                                        </form>
                                        <?php endif; ?>
                                        <?php if ($campaign['status'] === 'draft' && $_SESSION['role'] === 'admin'): ?>
                                        <form method="POST" action="/wablast/delete/<?= $campaign['id'] ?>" style="display: inline;" 
                                              onsubmit="return confirm('Yakin ingin menghapus campaign ini?');">
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                        <?php endif; ?>
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
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&per_page=<?= $perPage ?>">Previous</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&per_page=<?= $perPage ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&per_page=<?= $perPage ?>">Next</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

