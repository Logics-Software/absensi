<?php
$title = 'Konfigurasi Fonnte';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Simpan data konfigurasi fonnte sebelum header.php overwrite variabel $konfigurasi
$konfigurasiFonnteData = $konfigurasi ?? null;

require __DIR__ . '/../layouts/header.php';

// Restore data konfigurasi fonnte setelah header.php
$konfigurasi = $konfigurasiFonnteData ?? [];
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/konfigurasi">Konfigurasi</a></li>
                    <li class="breadcrumb-item active">Konfigurasi Fonnte</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">Konfigurasi Fonnte WhatsApp API</h4>
                        <a href="/konfigurasi-fonnte/test" class="btn btn-sm btn-warning">
                            <?= icon('check-circle', 'me-1', 16) ?>Test Koneksi
                        </a>
                    </div>
                </div>

                <form method="POST" action="/konfigurasi-fonnte">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="api_key" class="form-label">API Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="api_key" name="api_key" 
                                   value="<?= htmlspecialchars($konfigurasi['api_key'] ?? '') ?>" 
                                   placeholder="Masukkan API Key dari Fonnte"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="api_url" class="form-label">API URL</label>
                            <input type="text" class="form-control" id="api_url" name="api_url" 
                                   value="<?= htmlspecialchars($konfigurasi['api_url'] ?? 'https://api.fonnte.com') ?>" 
                                   placeholder="https://api.fonnte.com">
                        </div>

                        <div class="mb-3">
                            <label for="device_id" class="form-label">Device ID</label>
                            <input type="text" class="form-control" id="device_id" name="device_id" 
                                   value="<?= htmlspecialchars($konfigurasi['device_id'] ?? '') ?>" 
                                   placeholder="Masukkan Device ID (opsional)">
                        </div>

                        <div class="mb-3">
                            <label for="webhook_url" class="form-label">Webhook URL</label>
                            <input type="text" class="form-control" id="webhook_url" name="webhook_url" 
                                   value="<?= htmlspecialchars($konfigurasi['webhook_url'] ?? '') ?>" 
                                   placeholder="https://yourdomain.com/wablast/webhook">
                        </div>

                    </div>
                    
                    <div class="card-footer d-flex justify-content-between">
                        <a href="/dashboard" class="btn btn-secondary"><?= icon('arrow-left', 'me-1 mb-1', 18) ?>Kembali</a>
                        <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Simpan Konfigurasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
