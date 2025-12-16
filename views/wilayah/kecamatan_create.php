<?php
$title = 'Tambah Kecamatan';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/wilayah/kecamatan">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/wilayah/kecamatan">Kecamatan</a></li>
                    <li class="breadcrumb-item active">Tambah Kecamatan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Tambah Data Kecamatan</h4>
                    </div>
                </div>
                <form method="POST" action="/wilayah/kecamatan/create">
                <div class="card-body">
                        <div class="mb-3">
                            <label for="kode" class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode" name="kode" required placeholder="Contoh: 1101010">
                            <small class="form-text text-muted">Kode kecamatan sesuai format Kemendagri (8 digit)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="provinsi_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                            <select class="form-select" id="provinsi_id" name="provinsi_id" required onchange="loadKabupaten()">
                                <option value="">Pilih Provinsi</option>
                                <?php foreach ($provinsiList as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="kabupaten_kota_id" class="form-label">Kabupaten/Kota <span class="text-danger">*</span></label>
                            <select class="form-select" id="kabupaten_kota_id" name="kabupaten_kota_id" required>
                                <option value="">Pilih Kabupaten/Kota</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama kecamatan">
                        </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/wilayah/kecamatan" class="btn btn-secondary"><?= icon('back', 'me-1 mb-1', 18) ?>Kembali</a>
                    <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Simpan</button>
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
    
    kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
    
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
        })
        .catch(error => {
            console.error('Error loading kabupaten:', error);
        });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

