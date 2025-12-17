<?php
$title = 'Edit Master Guru';
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
                    <li class="breadcrumb-item"><a href="/masterguru">Master Guru</a></li>
                    <li class="breadcrumb-item active">Edit Guru</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Master Guru</h4>
                    </div>
                </div>
                <form method="POST" action="/masterguru/edit/<?= $masterGuru['id'] ?>" enctype="multipart/form-data">
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nip" class="form-label">NIP</label>
                                <input type="text" class="form-control" id="nip" name="nip" 
                                       value="<?= htmlspecialchars($masterGuru['nip'] ?? '') ?>" 
                                       placeholder="Masukkan NIP">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="noabsensi" class="form-label">Pin Absensi</label>
                                <input type="text" class="form-control" id="noabsensi" name="noabsensi" 
                                       value="<?= htmlspecialchars($masterGuru['noabsensi'] ?? '') ?>" 
                                       placeholder="Masukkan nomor absensi" maxlength="20">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="namaguru" class="form-label">Nama Guru <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namaguru" name="namaguru" 
                                       value="<?= htmlspecialchars($masterGuru['namaguru'] ?? '') ?>" 
                                       required placeholder="Masukkan nama guru">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jeniskelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-select" id="jeniskelamin" name="jeniskelamin" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="laki-laki" <?= ($masterGuru['jeniskelamin'] ?? '') == 'laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="perempuan" <?= ($masterGuru['jeniskelamin'] ?? '') == 'perempuan' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tempatlahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempatlahir" name="tempatlahir" 
                                       value="<?= htmlspecialchars($masterGuru['tempatlahir'] ?? '') ?>" 
                                       placeholder="Masukkan tempat lahir">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggallahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggallahir" name="tanggallahir" 
                                       value="<?= !empty($masterGuru['tanggallahir']) ? date('Y-m-d', strtotime($masterGuru['tanggallahir'])) : '' ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nomorhp" class="form-label">Nomor HP</label>
                                <input type="text" class="form-control" id="nomorhp" name="nomorhp" 
                                       value="<?= htmlspecialchars($masterGuru['nomorhp'] ?? '') ?>" 
                                       placeholder="+62xxxxxxxxxx">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamatguru" class="form-label">Alamat Guru</label>
                            <textarea class="form-control" id="alamatguru" name="alamatguru" rows="1" 
                                      placeholder="Masukkan alamat lengkap guru"><?= htmlspecialchars($masterGuru['alamatguru'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idprovinsi" class="form-label">Provinsi</label>
                                <select class="form-select" id="idprovinsi" name="idprovinsi" onchange="loadKabupaten()">
                                    <option value="">Pilih Provinsi</option>
                                    <?php foreach ($provinsiList as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= ($masterGuru['idprovinsi'] ?? null) == $p['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="idkabupaten" class="form-label">Kabupaten/Kota</label>
                                <select class="form-select" id="idkabupaten" name="idkabupaten" onchange="loadKecamatan()">
                                    <option value="">Pilih Kabupaten/Kota</option>
                                    <?php foreach ($kabupatenList as $k): ?>
                                        <option value="<?= $k['id'] ?>" <?= ($masterGuru['idkabupaten'] ?? null) == $k['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idkecamatan" class="form-label">Kecamatan</label>
                                <select class="form-select" id="idkecamatan" name="idkecamatan" onchange="loadKelurahan()">
                                    <option value="">Pilih Kecamatan</option>
                                    <?php foreach ($kecamatanList as $kc): ?>
                                        <option value="<?= $kc['id'] ?>" <?= ($masterGuru['idkecamatan'] ?? null) == $kc['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kc['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="idkelurahan" class="form-label">Kelurahan/Desa</label>
                                <select class="form-select" id="idkelurahan" name="idkelurahan">
                                    <option value="">Pilih Kelurahan/Desa</option>
                                    <?php foreach ($kelurahanList as $kl): ?>
                                        <option value="<?= $kl['id'] ?>" <?= ($masterGuru['idkelurahan'] ?? null) == $kl['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kl['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="kodepos" class="form-label">Kode Pos</label>
                                <input type="text" class="form-control" id="kodepos" name="kodepos" 
                                       value="<?= htmlspecialchars($masterGuru['kodepos'] ?? '') ?>" 
                                       placeholder="Masukkan kode pos">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($masterGuru['email'] ?? '') ?>" 
                                       placeholder="contoh@email.com">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="iduser" class="form-label">User (Guru)</label>
                                <select class="form-select" id="iduser" name="iduser">
                                    <option value="">Pilih User</option>
                                    <?php foreach ($guruList as $guru): ?>
                                        <option value="<?= $guru['id'] ?>" <?= ($masterGuru['iduser'] ?? null) == $guru['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($guru['namalengkap']) ?>
                                            <?php if (!empty($guru['email'])): ?>
                                                (<?= htmlspecialchars($guru['email']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif" <?= ($masterGuru['status'] ?? 'aktif') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= ($masterGuru['status'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>Non Aktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto Guru</label>
                            <?php if ($masterGuru['foto'] && file_exists(__DIR__ . '/../../uploads/' . $masterGuru['foto'])): ?>
                            <div class="mb-3">
                                <img src="<?= htmlspecialchars($baseUrl) ?>/uploads/<?= htmlspecialchars($masterGuru['foto']) ?>" 
                                     alt="Foto Guru" class="img-thumbnail rounded" style="max-width: 200px; max-height: 200px;">
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB). Kosongkan jika tidak ingin mengubah foto.</small>
                        </div>
                        
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/masterguru" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                    <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Simpan Perubahan</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function loadKabupaten() {
    const provinsiId = document.getElementById('idprovinsi').value;
    const kabupatenSelect = document.getElementById('idkabupaten');
    const kecamatanSelect = document.getElementById('idkecamatan');
    const kelurahanSelect = document.getElementById('idkelurahan');
    
    const selectedKabupatenId = <?= json_encode($masterGuru['idkabupaten'] ?? null) ?>;
    
    kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
    
    if (!provinsiId) return;
    
    fetch(`/wilayah/api/kabupaten?provinsi_id=${provinsiId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama;
                if (selectedKabupatenId && item.id == selectedKabupatenId) {
                    option.selected = true;
                }
                kabupatenSelect.appendChild(option);
            });
            if (selectedKabupatenId) {
                loadKecamatan();
            }
        })
        .catch(error => {
            console.error('Error loading kabupaten:', error);
        });
}

function loadKecamatan() {
    const kabupatenId = document.getElementById('idkabupaten').value;
    const kecamatanSelect = document.getElementById('idkecamatan');
    const kelurahanSelect = document.getElementById('idkelurahan');
    
    const selectedKecamatanId = <?= json_encode($masterGuru['idkecamatan'] ?? null) ?>;
    
    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
    
    if (!kabupatenId) return;
    
    fetch(`/wilayah/api/kecamatan?kabupaten_id=${kabupatenId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama;
                if (selectedKecamatanId && item.id == selectedKecamatanId) {
                    option.selected = true;
                }
                kecamatanSelect.appendChild(option);
            });
            if (selectedKecamatanId) {
                loadKelurahan();
            }
        })
        .catch(error => {
            console.error('Error loading kecamatan:', error);
        });
}

function loadKelurahan() {
    const kecamatanId = document.getElementById('idkecamatan').value;
    const kelurahanSelect = document.getElementById('idkelurahan');
    
    const selectedKelurahanId = <?= json_encode($masterGuru['idkelurahan'] ?? null) ?>;
    
    kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
    
    if (!kecamatanId) return;
    
    fetch(`/wilayah/api/kelurahan?kecamatan_id=${kecamatanId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama;
                if (selectedKelurahanId && item.id == selectedKelurahanId) {
                    option.selected = true;
                }
                kelurahanSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading kelurahan:', error);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    // Adjust textarea rows for alamatguru
    const alamatGuruTextarea = document.getElementById('alamatguru');
    function adjustTextareaRows() {
        if (window.innerWidth < 768) { // Mobile
            alamatGuruTextarea.rows = 3;
        } else { // Desktop
            alamatGuruTextarea.rows = 1;
        }
    }
    adjustTextareaRows();
    window.addEventListener('resize', adjustTextareaRows);
    
    // Phone number formatting
    const nomorHpInput = document.getElementById('nomorhp');
    if (nomorHpInput) {
        nomorHpInput.addEventListener('blur', function() {
            let value = this.value.replace(/[^0-9]/g, ''); // Remove non-digits
            if (value.startsWith('0')) {
                value = value.substring(1); // Remove leading zero
            }
            if (!value.startsWith('62')) {
                value = '62' + value; // Add 62 prefix
            }
            this.value = '+' + value;
        });
        // Initial format if value exists
        if (nomorHpInput.value) {
            let value = nomorHpInput.value.replace(/[^0-9]/g, '');
            if (value.startsWith('0')) {
                value = value.substring(1);
            }
            if (!value.startsWith('62')) {
                value = '62' + value;
            }
            nomorHpInput.value = '+' + value;
        }
    }
    
    // Load dependent dropdowns on page load if parent is selected
    const provinsiId = document.getElementById('idprovinsi').value;
    if (provinsiId) {
        loadKabupaten();
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

