<?php
$title = 'Setting Tahun Ajaran';
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
                    <li class="breadcrumb-item active">Setting Tahun Ajaran</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Setting Tahun Ajaran</h4>
                        <a href="/tahunajaran/create" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> Tambah Tahun Ajaran
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    
                    <!-- Search Form -->
                    <form method="GET" action="/tahunajaran" class="mb-3">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-2 mb-md-0">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Cari tahun ajaran..." 
                                       value="<?= htmlspecialchars($search ?? '') ?>">
                            </div>
                            <div class="col-6 col-md-3 mb-2 mb-md-0">
                                <button type="submit" class="btn btn-filter btn-primary w-100">Cari</button>
                            </div>
                            <div class="col-6 col-md-3">
                                <a href="/tahunajaran" class="btn btn-filter btn-outline-secondary w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Tahun Ajaran</th>
                                    <th>Tanggal Awal</th>
                                    <th>Tanggal Akhir</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tahunAjaranList)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data</td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    // Pastikan page dan perPage selalu integer dan valid
                                    $currentPage = isset($page) ? max(1, (int)$page) : 1;
                                    $currentPerPage = isset($perPage) ? max(1, (int)$perPage) : 10;
                                    $no = ($currentPage - 1) * $currentPerPage + 1;
                                    foreach ($tahunAjaranList as $ta): 
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= htmlspecialchars($ta['tahunajaran']) ?></td>
                                            <td class="text-center"><?= date('d/m/Y', strtotime($ta['tanggalawal'])) ?></td>
                                            <td class="text-center"><?= date('d/m/Y', strtotime($ta['tanggalakhir'])) ?></td>
                                            <td>
                                                <?php if ($ta['status'] === 'aktif'): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Selesai</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="/tahunajaran/edit/<?= $ta['id'] ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <?php if ($ta['status'] !== 'aktif'): ?>
                                                <a href="#" onclick="event.preventDefault(); confirmDelete('Apakah Anda yakin ingin menghapus tahun ajaran <?= htmlspecialchars($ta['tahunajaran']) ?>?', '/tahunajaran/delete/<?= $ta['id'] ?>'); return false;" 
                                                   class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
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
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search ?? '') ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search ?? '') ?>">Next</a>
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

