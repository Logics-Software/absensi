<?php
$title = 'Tambah Tahun Ajaran';
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
                    <li class="breadcrumb-item"><a href="/tahunajaran">Setting Tahun Ajaran</a></li>
                    <li class="breadcrumb-item active">Tambah Tahun Ajaran</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><?= $hasData ? 'Tambah Tahun Ajaran' : 'Tambah Tahun Ajaran (Wajib)' ?></h4>
                </div>
                <div class="card-body">
                    <?php if (!$hasData): ?>
                    <div class="alert alert-warning">
                        <strong>Perhatian!</strong> Data tahun ajaran masih kosong. Anda wajib mengisi tahun ajaran baru sebagai tahun ajaran aktif.
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="/tahunajaran/create">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tahunajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tahunajaran" name="tahunajaran" 
                                       value="<?= htmlspecialchars($_POST['tahunajaran'] ?? '') ?>" 
                                       placeholder="Contoh: 2024/2025" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <?php if (!$hasData): ?>
                                <input type="text" class="form-control" value="Aktif" readonly>
                                <input type="hidden" name="status" value="aktif">
                                <small class="text-muted">Tahun ajaran pertama harus berstatus aktif</small>
                                <?php else: ?>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="aktif" <?= ($_POST['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="selesai" <?= ($_POST['status'] ?? '') === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggalawal" class="form-label">Tanggal Awal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggalawal" name="tanggalawal" 
                                       value="<?= htmlspecialchars($_POST['tanggalawal'] ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tanggalakhir" class="form-label">Tanggal Akhir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggalakhir" name="tanggalakhir" 
                                       value="<?= htmlspecialchars($_POST['tanggalakhir'] ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="/tahunajaran" class="btn btn-secondary">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

