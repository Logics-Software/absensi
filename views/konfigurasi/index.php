<?php
$title = 'Konfigurasi';
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
                    <li class="breadcrumb-item active">Konfigurasi</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0"><?= $konfigurasi ? 'Edit' : 'Tambah' ?> Konfigurasi Sekolah</h4>
                    </div>
                </div>

                <form method="POST" action="/konfigurasi" enctype="multipart/form-data">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="npsn" class="form-label">NPSN</label>
                                <input type="text" class="form-control" id="npsn" name="npsn" 
                                       value="<?= htmlspecialchars($konfigurasi['npsn'] ?? '') ?>" 
                                       placeholder="Masukkan NPSN">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="namasekolah" class="form-label">Nama Sekolah</label>
                                <input type="text" class="form-control" id="namasekolah" name="namasekolah" 
                                       value="<?= htmlspecialchars($konfigurasi['namasekolah'] ?? '') ?>" 
                                       placeholder="Masukkan nama sekolah">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamatsekolah" class="form-label">Alamat Sekolah</label>
                            <textarea class="form-control" id="alamatsekolah" name="alamatsekolah" rows="1" 
                                      placeholder="Masukkan alamat sekolah"><?= htmlspecialchars($konfigurasi['alamatsekolah'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="skpendirian" class="form-label">SK Pendirian</label>
                                <input type="text" class="form-control" id="skpendirian" name="skpendirian" 
                                       value="<?= htmlspecialchars($konfigurasi['skpendirian'] ?? '') ?>" 
                                       placeholder="Masukkan SK Pendirian">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tanggalskpendirian" class="form-label">Tanggal SK Pendirian</label>
                                <input type="date" class="form-control" id="tanggalskpendirian" name="tanggalskpendirian" 
                                       value="<?= !empty($konfigurasi['tanggalskpendirian']) ? date('Y-m-d', strtotime($konfigurasi['tanggalskpendirian'])) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="skoperasional" class="form-label">SK Operasional</label>
                                <input type="text" class="form-control" id="skoperasional" name="skoperasional" 
                                       value="<?= htmlspecialchars($konfigurasi['skoperasional'] ?? '') ?>" 
                                       placeholder="Masukkan SK Operasional">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tanggalskoperasional" class="form-label">Tanggal SK Operasional</label>
                                <input type="date" class="form-control" id="tanggalskoperasional" name="tanggalskoperasional" 
                                       value="<?= !empty($konfigurasi['tanggalskoperasional']) ? date('Y-m-d', strtotime($konfigurasi['tanggalskoperasional'])) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="idkepalasekolah" class="form-label">Kepala Sekolah</label>
                            <select class="form-select" id="idkepalasekolah" name="idkepalasekolah">
                                <option value="">Pilih Kepala Sekolah</option>
                                <?php foreach ($kepalaSekolahList as $kepala): ?>
                                    <option value="<?= $kepala['id'] ?>" 
                                            <?= ($konfigurasi['idkepalasekolah'] ?? null) == $kepala['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kepala['namalengkap']) ?> 
                                        <?php if (!empty($kepala['email'])): ?>
                                            (<?= htmlspecialchars($kepala['email']) ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo Sekolah</label>
                            <?php if ($konfigurasi && !empty($konfigurasi['logo']) && file_exists(__DIR__ . '/../../uploads/' . $konfigurasi['logo'])): ?>
                            <div class="mb-3">
                                <img src="<?= htmlspecialchars($baseUrl) ?>/uploads/<?= htmlspecialchars($konfigurasi['logo']) ?>" 
                                     alt="Logo Sekolah" class="img-thumbnail rounded" style="max-width: 200px; max-height: 200px;">
                            </div>
                            <?php else: ?>
                            <div class="mb-3">
                                <p class="mb-2 text-muted"><em>Belum ada logo</em></p>
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB). Kosongkan jika tidak ingin mengubah logo.</small>
                        </div>
                    </div>
                    
                    <div class="card-footer d-flex justify-content-between">
                        <a href="/dashboard" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Simpan Konfigurasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const alamatTextarea = document.getElementById('alamatsekolah');
    if (!alamatTextarea) return;
    
    function updateTextareaRows() {
        if (window.innerWidth < 768) {
            // Mobile: rows = 3
            alamatTextarea.setAttribute('rows', '3');
        } else {
            // Desktop: rows = 1
            alamatTextarea.setAttribute('rows', '1');
        }
    }
    
    // Set initial rows
    updateTextareaRows();
    
    // Update on window resize
    window.addEventListener('resize', updateTextareaRows);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

