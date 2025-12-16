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

$title = 'Edit ' . ($typeLabels[$type] ?? 'Wilayah');
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Get item based on type
$item = null;
if ($type === 'provinsi' && isset($provinsi)) {
    $item = $provinsi;
} elseif ($type === 'kabupaten' && isset($kabupaten)) {
    $item = $kabupaten;
} elseif ($type === 'kecamatan' && isset($kecamatan)) {
    $item = $kecamatan;
} elseif ($type === 'kelurahan' && isset($kelurahan)) {
    $item = $kelurahan;
}

if (!$item) {
    header('Location: ' . ($typeRoutes[$type] ?? '/wilayah/provinsi'));
    exit;
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
                    <li class="breadcrumb-item active">Edit</li>
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
                    <h4 class="mb-0">Edit <?= htmlspecialchars($typeLabels[$type] ?? 'Wilayah') ?></h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= htmlspecialchars($typeRoutes[$type] ?? '/wilayah/provinsi') ?>/edit/<?= $item['id'] ?>">
                        <div class="mb-3">
                            <label for="kode" class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode" name="kode" required value="<?= htmlspecialchars($item['kode']) ?>">
                            <small class="form-text text-muted">Kode wilayah sesuai format Kemendagri</small>
                        </div>

                        <?php if ($type === 'kabupaten' || $type === 'kecamatan' || $type === 'kelurahan'): ?>
                        <div class="mb-3">
                            <label for="provinsi_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                            <select class="form-select" id="provinsi_id" name="provinsi_id" required onchange="loadKabupaten()">
                                <option value="">Pilih Provinsi</option>
                                <?php foreach ($provinsiList ?? [] as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= (isset($item['provinsi_id']) && $item['provinsi_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <?php if ($type === 'kecamatan' || $type === 'kelurahan'): ?>
                        <div class="mb-3">
                            <label for="kabupaten_kota_id" class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
                            <select class="form-select" id="kabupaten_kota_id" name="kabupaten_kota_id" required onchange="loadKecamatan()">
                                <option value="">Pilih Kabupaten/Kota</option>
                                <?php if (isset($kabupatenList)): ?>
                                <?php foreach ($kabupatenList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= (isset($item['kabupaten_kota_id']) && $item['kabupaten_kota_id'] == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama']) ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <?php if ($type === 'kelurahan'): ?>
                        <div class="mb-3">
                            <label for="kecamatan_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                            <select class="form-select" id="kecamatan_id" name="kecamatan_id" required>
                                <option value="">Pilih Kecamatan</option>
                                <?php if (isset($kecamatanList)): ?>
                                <?php foreach ($kecamatanList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= (isset($item['kecamatan_id']) && $item['kecamatan_id'] == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama']) ?>
                                </option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required value="<?= htmlspecialchars($item['nama']) ?>">
                        </div>

                        <?php if ($type === 'kabupaten'): ?>
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="Kabupaten" <?= ($item['tipe'] ?? '') === 'Kabupaten' ? 'selected' : '' ?>>Kabupaten</option>
                                <option value="Kota" <?= ($item['tipe'] ?? '') === 'Kota' ? 'selected' : '' ?>>Kota</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <?php if ($type === 'kelurahan'): ?>
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="Kelurahan" <?= ($item['tipe'] ?? '') === 'Kelurahan' ? 'selected' : '' ?>>Kelurahan</option>
                                <option value="Desa" <?= ($item['tipe'] ?? '') === 'Desa' ? 'selected' : '' ?>>Desa</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update</button>
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
    
    if (!provinsiId) {
        kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
        if (kecamatanSelect) {
            kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
        }
        return;
    }
    
    fetch(`/wilayah/api/kabupaten?provinsi_id=${provinsiId}`)
        .then(response => response.json())
        .then(data => {
            const currentValue = kabupatenSelect.value;
            kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama;
                if (item.id == currentValue) {
                    option.selected = true;
                }
                kabupatenSelect.appendChild(option);
            });
            if (kecamatanSelect) {
                loadKecamatan();
            }
        });
}

<?php if ($type === 'kelurahan'): ?>
function loadKecamatan() {
    const kabupatenId = document.getElementById('kabupaten_kota_id').value;
    const kecamatanSelect = document.getElementById('kecamatan_id');
    
    if (!kabupatenId) {
        kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
        return;
    }
    
    fetch(`/wilayah/api/kecamatan?kabupaten_id=${kabupatenId}`)
        .then(response => response.json())
        .then(data => {
            const currentValue = kecamatanSelect.value;
            kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama;
                if (item.id == currentValue) {
                    option.selected = true;
                }
                kecamatanSelect.appendChild(option);
            });
        });
}
<?php endif; ?>
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

