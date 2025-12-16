<?php
$title = 'Edit Kelurahan/Desa';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/wilayah/kelurahan">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/wilayah/kelurahan">Kelurahan/Desa</a></li>
                    <li class="breadcrumb-item active">Edit Kelurahan/Desa</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Kelurahan/Desa</h4>
                    </div>
                </div>
                <form method="POST" action="/wilayah/kelurahan/edit/<?= $kelurahan['id'] ?>">
                <div class="card-body">
                        <div class="mb-3">
                            <label for="kode" class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode" name="kode" required value="<?= htmlspecialchars($kelurahan['kode']) ?>">
                            <small class="form-text text-muted">Kode kelurahan sesuai format Kemendagri (13 digit)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="provinsi_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                            <select class="form-select" id="provinsi_id" name="provinsi_id" required onchange="loadKabupaten()">
                                <option value="">Pilih Provinsi</option>
                                <?php foreach ($provinsiList as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($kelurahan['provinsi_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="kabupaten_kota_id" class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
                            <select class="form-select" id="kabupaten_kota_id" name="kabupaten_kota_id" required onchange="loadKecamatan()">
                                <option value="">Pilih Kabupaten/Kota</option>
                                <?php foreach ($kabupatenList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($kelurahan['kabupaten_kota_id'] == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="kecamatan_id" class="form-label">Kecamatan <span class="text-danger">*</span></label>
                            <select class="form-select" id="kecamatan_id" name="kecamatan_id" required>
                                <option value="">Pilih Kecamatan</option>
                                <?php foreach ($kecamatanList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($kelurahan['kecamatan_id'] == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required value="<?= htmlspecialchars($kelurahan['nama']) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="Kelurahan" <?= ($kelurahan['tipe'] === 'Kelurahan') ? 'selected' : '' ?>>Kelurahan</option>
                                <option value="Desa" <?= ($kelurahan['tipe'] === 'Desa') ? 'selected' : '' ?>>Desa</option>
                            </select>
                        </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/wilayah/kelurahan" class="btn btn-secondary"><?= icon('back', 'me-1 mb-1', 18) ?>Kembali</a>
                    <button type="submit" class="btn btn-primary"><?= icon('update', 'me-1 mb-1', 18) ?>Update</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function loadKabupaten() {
    const provinsiId = document.getElementById('provinsi_id').value;
    const kabupatenSelect = document.getElementById('kabupaten_kota_id');
    const kecamatanSelect = document.getElementById('kecamatan_id');
    const currentKabupatenValue = kabupatenSelect.value;
    
    kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    
    if (!provinsiId) return;
    
    fetch(`/wilayah/api/kabupaten?provinsi_id=${provinsiId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama;
                if (item.id == currentKabupatenValue) {
                    option.selected = true;
                    loadKecamatan();
                }
                kabupatenSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading kabupaten:', error);
        });
}

function loadKecamatan() {
    const kabupatenId = document.getElementById('kabupaten_kota_id').value;
    const kecamatanSelect = document.getElementById('kecamatan_id');
    const currentKecamatanValue = kecamatanSelect.value;
    
    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    
    if (!kabupatenId) return;
    
    fetch(`/wilayah/api/kecamatan?kabupaten_id=${kabupatenId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama;
                if (item.id == currentKecamatanValue) {
                    option.selected = true;
                }
                kecamatanSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading kecamatan:', error);
        });
}

// Load kabupaten on page load if provinsi is selected
document.addEventListener('DOMContentLoaded', function() {
    const provinsiId = document.getElementById('provinsi_id').value;
    if (provinsiId) {
        loadKabupaten();
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

