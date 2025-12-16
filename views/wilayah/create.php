<?php
$typeLabels = [
    'provinsi' => 'Provinsi',
    'kabupaten' => 'Kabupaten/Kota',
    'kecamatan' => 'Kecamatan',
    'kelurahan' => 'Kelurahan/Desa'
];

$typeRoutes = [
    'provinsi' => '/wilayah/provinsi',
    'kabupaten' => '/wilayah/kabupaten',
    'kecamatan' => '/wilayah/kecamatan',
    'kelurahan' => '/wilayah/kelurahan'
];

$title = 'Tambah ' . ($typeLabels[$type] ?? 'Wilayah');
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
                    <li class="breadcrumb-item"><a href="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>"><?= htmlspecialchars($typeLabels[$type] ?? 'Wilayah') ?></a></li>
                    <li class="breadcrumb-item active">Tambah</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?= $type === 'provinsi' ? 'active' : '' ?>" href="/wilayah/provinsi">Provinsi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $type === 'kabupaten' ? 'active' : '' ?>" href="/wilayah/kabupaten">Kabupaten/Kota</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $type === 'kecamatan' ? 'active' : '' ?>" href="/wilayah/kecamatan">Kecamatan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $type === 'kelurahan' ? 'active' : '' ?>" href="/wilayah/kelurahan">Kelurahan/Desa</a>
                </li>
            </ul>

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Tambah <?= htmlspecialchars($typeLabels[$type] ?? 'Wilayah') ?></h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>/create">
                        <div class="mb-3">
                            <label for="kode" class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode" name="kode" required 
                                   placeholder="<?= $type === 'provinsi' ? 'Contoh: 11' : ($type === 'kabupaten' ? 'Contoh: 1101' : ($type === 'kecamatan' ? 'Contoh: 1101010' : 'Contoh: 1101010001')) ?>">
                            <small class="form-text text-muted">Kode wilayah sesuai format Kemendagri</small>
                        </div>

                        <?php if ($type === 'kabupaten' || $type === 'kecamatan' || $type === 'kelurahan'): ?>
                        <div class="mb-3">
                            <label for="provinsi_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                            <select class="form-select" id="provinsi_id" name="provinsi_id" required onchange="loadKabupaten()">
                                <option value="">Pilih Provinsi</option>
                                <?php foreach ($provinsiList ?? [] as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <?php if ($type === 'kecamatan' || $type === 'kelurahan'): ?>
                        <div class="mb-3">
                            <label for="kabupaten_kota_id" class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
                            <select class="form-select" id="kabupaten_kota_id" name="kabupaten_kota_id" required onchange="loadKecamatan()">
                                <option value="">Pilih Kabupaten/Kota</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <?php if ($type === 'kelurahan'): ?>
                        <div class="mb-3">
                            <label for="kecamatan_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                            <select class="form-select" id="kecamatan_id" name="kecamatan_id" required>
                                <option value="">Pilih Kecamatan</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>

                        <?php if ($type === 'kabupaten'): ?>
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="Kabupaten">Kabupaten</option>
                                <option value="Kota">Kota</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <?php if ($type === 'kelurahan'): ?>
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="Kelurahan">Kelurahan</option>
                                <option value="Desa">Desa</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($type === 'kecamatan' || $type === 'kelurahan'): ?>
<script>
function loadKabupaten() {
    const provinsiId = document.getElementById('provinsi_id').value;
    const kabupatenSelect = document.getElementById('kabupaten_kota_id');
    const kecamatanSelect = document.getElementById('kecamatan_id');
    
    kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
    if (kecamatanSelect) {
        kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    }
    
    if (!provinsiId) return;
    
    fetch(`/wilayah/api/kabupaten?provinsi_id=${provinsiId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama;
                kabupatenSelect.appendChild(option);
            });
        });
}

<?php if ($type === 'kelurahan'): ?>
function loadKecamatan() {
    const kabupatenId = document.getElementById('kabupaten_kota_id').value;
    const kecamatanSelect = document.getElementById('kecamatan_id');
    
    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    
    if (!kabupatenId) return;
    
    fetch(`/wilayah/api/kecamatan?kabupaten_id=${kabupatenId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama;
                kecamatanSelect.appendChild(option);
            });
        });
}
<?php endif; ?>
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

