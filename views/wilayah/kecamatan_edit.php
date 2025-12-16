<?php
$title = 'Edit Kecamatan';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/wilayah/kecamatan">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/wilayah/kecamatan">Kecamatan</a></li>
                    <li class="breadcrumb-item active">Edit Kecamatan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Kecamatan</h4>
                    </div>
                </div>
                <form method="POST" action="/wilayah/kecamatan/edit/<?= $kecamatan['id'] ?>">
                <div class="card-body">
                        <div class="mb-3">
                            <label for="kode" class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode" name="kode" required value="<?= htmlspecialchars($kecamatan['kode']) ?>">
                            <small class="form-text text-muted">Kode kecamatan sesuai format Kemendagri (8 digit)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="provinsi_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                            <select class="form-select" id="provinsi_id" name="provinsi_id" required onchange="loadKabupaten()">
                                <option value="">Pilih Provinsi</option>
                                <?php foreach ($provinsiList as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($kecamatan['provinsi_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="kabupaten_kota_id" class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
                            <select class="form-select" id="kabupaten_kota_id" name="kabupaten_kota_id" required>
                                <option value="">Pilih Kabupaten/Kota</option>
                                <?php foreach ($kabupatenList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($kecamatan['kabupaten_kota_id'] == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required value="<?= htmlspecialchars($kecamatan['nama']) ?>">
                        </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/wilayah/kecamatan" class="btn btn-secondary"><?= icon('back', 'me-1 mb-1', 18) ?>Kembali</a>
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
    const currentValue = kabupatenSelect.value;
    
    kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
    
    if (!provinsiId) return;
    
    fetch(`/wilayah/api/kabupaten?provinsi_id=${provinsiId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama;
                if (item.id == currentValue) {
                    option.selected = true;
                }
                kabupatenSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading kabupaten:', error);
        });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

