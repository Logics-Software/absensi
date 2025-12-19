<?php
$title = 'Edit Master Siswa';
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
                    <li class="breadcrumb-item"><a href="/mastersiswa">Master Siswa</a></li>
                    <li class="breadcrumb-item active">Edit Siswa</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Master Siswa</h4>
                    </div>
                </div>
                <form method="POST" action="/mastersiswa/edit/<?= $mastersiswa['id'] ?>" id="formSiswa" enctype="multipart/form-data">
                <div class="card-body">
                        <!-- Data Siswa -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="nisn" class="form-label">NISN</label>
                                <input type="text" class="form-control" id="nisn" name="nisn" 
                                       value="<?= htmlspecialchars($mastersiswa['nisn'] ?? '') ?>" 
                                       placeholder="Nomor Induk Siswa Nasional" maxlength="50">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" 
                                       value="<?= htmlspecialchars($mastersiswa['nik'] ?? '') ?>" 
                                       placeholder="Nomor Induk Kependudukan" maxlength="50">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="noabsensi" class="form-label">Pin Absensi</label>
                                <input type="text" class="form-control" id="noabsensi" name="noabsensi" 
                                       value="<?= htmlspecialchars($mastersiswa['noabsensi'] ?? '') ?>" 
                                       placeholder="Nomor Absensi" maxlength="20">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="namasiswa" class="form-label">Nama Siswa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namasiswa" name="namasiswa" 
                                       value="<?= htmlspecialchars($mastersiswa['namasiswa'] ?? '') ?>" 
                                       required placeholder="Masukkan nama siswa" maxlength="100">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="jeniskelamin" class="form-label">Jenis Kelamin</label>
                                <select class="form-select" id="jeniskelamin" name="jeniskelamin">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="laki-laki" <?= ($mastersiswa['jeniskelamin'] ?? '') == 'laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="perempuan" <?= ($mastersiswa['jeniskelamin'] ?? '') == 'perempuan' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tempatlahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempatlahir" name="tempatlahir" 
                                       value="<?= htmlspecialchars($mastersiswa['tempatlahir'] ?? '') ?>" 
                                       placeholder="Masukkan tempat lahir" maxlength="100">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tanggallahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggallahir" name="tanggallahir" 
                                       value="<?= !empty($mastersiswa['tanggallahir']) ? date('Y-m-d', strtotime($mastersiswa['tanggallahir'])) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($mastersiswa['email'] ?? '') ?>" 
                                       placeholder="contoh@email.com" maxlength="255">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nomorhp" class="form-label">Nomor HP</label>
                                <div class="phone-input-wrapper">
                                    <div class="phone-input-container">
                                        <div class="country-code-selector" id="countryCodeSelector_nomorhp">
                                            <span class="country-flag">🇮🇩</span>
                                            <span class="country-code">62</span>
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" class="dropdown-icon">
                                                <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <?php 
                                        $nomorhp = $mastersiswa['nomorhp'] ?? '';
                                        $nomorhpValue = '';
                                        if (!empty($nomorhp)) {
                                            // Remove country code if exists
                                            $nomorhpValue = preg_replace('/^\+?62\s?/', '', $nomorhp);
                                            $nomorhpValue = preg_replace('/[^0-9]/', '', $nomorhpValue);
                                        }
                                        ?>
                                        <input type="text" class="form-control phone-number-input" id="nomorhp" name="nomorhp" 
                                               value="<?= htmlspecialchars($nomorhpValue) ?>" 
                                               placeholder="8971234567" maxlength="15" inputmode="numeric">
                                        <input type="hidden" id="nomorhp_full" name="nomorhp_full" value="<?= htmlspecialchars($nomorhp) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alamat Siswa -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idprovinsi" class="form-label">Provinsi</label>
                                <select class="form-select" id="idprovinsi" name="idprovinsi" onchange="loadKabupaten();">
                                    <option value="">Pilih Provinsi</option>
                                    <?php foreach ($provinsiList as $p): ?>
                                        <option value="<?= $p['id'] ?>" data-nama="<?= htmlspecialchars($p['nama']) ?>" <?= ($mastersiswa['idprovinsi'] ?? null) == $p['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="idkabupaten" class="form-label">Kabupaten/Kota</label>
                                <select class="form-select" id="idkabupaten" name="idkabupaten" onchange="loadKecamatan();">
                                    <option value="">Pilih Kabupaten/Kota</option>
                                    <?php foreach ($kabupatenList as $k): ?>
                                        <option value="<?= $k['id'] ?>" data-nama="<?= htmlspecialchars($k['nama']) ?>" <?= ($mastersiswa['idkabupaten'] ?? null) == $k['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idkecamatan" class="form-label">Kecamatan</label>
                                <select class="form-select" id="idkecamatan" name="idkecamatan" onchange="loadKelurahan();">
                                    <option value="">Pilih Kecamatan</option>
                                    <?php foreach ($kecamatanList as $kc): ?>
                                        <option value="<?= $kc['id'] ?>" data-nama="<?= htmlspecialchars($kc['nama']) ?>" <?= ($mastersiswa['idkecamatan'] ?? null) == $kc['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kc['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="idkelurahan" class="form-label">Kelurahan/Desa</label>
                                <select class="form-select" id="idkelurahan" name="idkelurahan" onchange="updateAlamatSiswa();">
                                    <option value="">Pilih Kelurahan/Desa</option>
                                    <?php foreach ($kelurahanList as $kl): ?>
                                        <option value="<?= $kl['id'] ?>" data-nama="<?= htmlspecialchars($kl['nama']) ?>" <?= ($mastersiswa['idkelurahan'] ?? null) == $kl['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kl['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamatsiswa" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamatsiswa" name="alamatsiswa" rows="2" 
                                      placeholder="Masukkan alamat lengkap siswa" maxlength="255"><?= htmlspecialchars($mastersiswa['alamatsiswa'] ?? '') ?></textarea>
                        </div>
                        
                        <!-- Tahun Ajaran dan Kelas -->
                        <h5 class="mb-3 mt-4 bg-secondary text-white text-center p-3 rounded">Tahun Ajaran & Kelas (Terakhir)</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idtahunajaran" class="form-label">Tahun Ajaran</label>
                                <select class="form-select" id="idtahunajaran" name="idtahunajaran" onchange="loadKelas()">
                                    <option value="">Pilih Tahun Ajaran</option>
                                    <?php foreach ($tahunAjaranList as $ta): ?>
                                        <option value="<?= $ta['id'] ?>" <?= ($mastersiswa['idtahunajaran'] ?? null) == $ta['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ta['tahunajaran']) ?>
                                            <?= ($ta['status'] == 'aktif') ? ' (Aktif)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="idkelas" class="form-label">Kelas</label>
                                <select class="form-select" id="idkelas" name="idkelas">
                                    <option value="">Pilih Kelas</option>
                                    <?php foreach ($kelasList as $kelas): ?>
                                        <option value="<?= $kelas['idkelas'] ?>" <?= ($mastersiswa['idkelas'] ?? null) == $kelas['idkelas'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kelas['namakelas']) ?> (<?= htmlspecialchars($kelas['kelas']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Data Wali -->
                        <h5 class="mb-3 mt-4 bg-secondary text-white text-center p-3 rounded">Data Wali Siswa</h5>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="namawali" class="form-label">Nama Wali</label>
                                <input type="text" class="form-control" id="namawali" name="namawali" 
                                       value="<?= htmlspecialchars($mastersiswa['namawali'] ?? '') ?>" 
                                       placeholder="Masukkan nama wali" maxlength="100">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="hubungan" class="form-label">Hubungan</label>
                                <select class="form-select" id="hubungan" name="hubungan">
                                    <option value="">Pilih Hubungan</option>
                                    <option value="orangtua" <?= ($mastersiswa['hubungan'] ?? '') == 'orangtua' ? 'selected' : '' ?>>Orang Tua</option>
                                    <option value="saudara" <?= ($mastersiswa['hubungan'] ?? '') == 'saudara' ? 'selected' : '' ?>>Saudara</option>
                                    <option value="lainlain" <?= ($mastersiswa['hubungan'] ?? '') == 'lainlain' ? 'selected' : '' ?>>Lain-lain</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="nomorhpwali" class="form-label">Nomor HP Wali</label>
                                <div class="phone-input-wrapper">
                                    <div class="phone-input-container">
                                        <div class="country-code-selector" id="countryCodeSelector_nomorhpwali">
                                            <span class="country-flag">🇮🇩</span>
                                            <span class="country-code">62</span>
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" class="dropdown-icon">
                                                <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <?php 
                                        $nomorhpwali = $mastersiswa['nomorhpwali'] ?? '';
                                        $nomorhpwaliValue = '';
                                        if (!empty($nomorhpwali)) {
                                            // Remove country code if exists
                                            $nomorhpwaliValue = preg_replace('/^\+?62\s?/', '', $nomorhpwali);
                                            $nomorhpwaliValue = preg_replace('/[^0-9]/', '', $nomorhpwaliValue);
                                        }
                                        ?>
                                        <input type="text" class="form-control phone-number-input" id="nomorhpwali" name="nomorhpwali" 
                                               value="<?= htmlspecialchars($nomorhpwaliValue) ?>" 
                                               placeholder="8971234567" maxlength="15" inputmode="numeric">
                                        <input type="hidden" id="nomorhpwali_full" name="nomorhpwali_full" value="<?= htmlspecialchars($nomorhpwali) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alamat Wali -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idprovinsiwali" class="form-label">Provinsi Wali</label>
                                <select class="form-select" id="idprovinsiwali" name="idprovinsiwali" onchange="loadKabupatenWali();">
                                    <option value="">Pilih Provinsi</option>
                                    <?php foreach ($provinsiList as $p): ?>
                                        <option value="<?= $p['id'] ?>" data-nama="<?= htmlspecialchars($p['nama']) ?>" <?= ($mastersiswa['idprovinsiwali'] ?? null) == $p['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="idkabupatenwali" class="form-label">Kabupaten/Kota Wali</label>
                                <select class="form-select" id="idkabupatenwali" name="idkabupatenwali" onchange="loadKecamatanWali();">
                                    <option value="">Pilih Kabupaten/Kota</option>
                                    <?php foreach ($kabupatenWaliList as $k): ?>
                                        <option value="<?= $k['id'] ?>" data-nama="<?= htmlspecialchars($k['nama']) ?>" <?= ($mastersiswa['idkabupatenwali'] ?? null) == $k['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idkecamatanwali" class="form-label">Kecamatan Wali</label>
                                <select class="form-select" id="idkecamatanwali" name="idkecamatanwali" onchange="loadKelurahanWali();">
                                    <option value="">Pilih Kecamatan</option>
                                    <?php foreach ($kecamatanWaliList as $kc): ?>
                                        <option value="<?= $kc['id'] ?>" data-nama="<?= htmlspecialchars($kc['nama']) ?>" <?= ($mastersiswa['idkecamatanwali'] ?? null) == $kc['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kc['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="idkelurahanwali" class="form-label">Kelurahan/Desa Wali</label>
                                <select class="form-select" id="idkelurahanwali" name="idkelurahanwali" onchange="updateAlamatWali();">
                                    <option value="">Pilih Kelurahan/Desa</option>
                                    <?php foreach ($kelurahanWaliList as $kl): ?>
                                        <option value="<?= $kl['id'] ?>" data-nama="<?= htmlspecialchars($kl['nama']) ?>" <?= ($mastersiswa['idkelurahanwali'] ?? null) == $kl['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kl['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamatwali" class="form-label">Alamat Lengkap Wali</label>
                            <textarea class="form-control" id="alamatwali" name="alamatwali" rows="2" 
                                      placeholder="Masukkan alamat lengkap wali" maxlength="255"><?= htmlspecialchars($mastersiswa['alamatwali'] ?? '') ?></textarea>
                        </div>
                        
                        <!-- Foto -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="foto" class="form-label">Foto Siswa</label>
                                <?php if ($mastersiswa['foto'] && file_exists(__DIR__ . '/../../uploads/' . $mastersiswa['foto'])): ?>
                                <div class="mb-3">
                                    <img src="<?= htmlspecialchars($baseUrl) ?>/uploads/<?= htmlspecialchars($mastersiswa['foto']) ?>" 
                                         alt="Foto Siswa" class="img-thumbnail rounded" style="max-width: 200px; max-height: 200px;">
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB)</small>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif" <?= ($mastersiswa['status'] ?? 'aktif') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="non aktif" <?= ($mastersiswa['status'] ?? '') == 'non aktif' ? 'selected' : '' ?>>Non Aktif</option>
                                </select>
                            </div>
                        </div>
                        
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/mastersiswa" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                    <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Update Siswa</button>
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
    
    kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
    
    if (!provinsiId) return;
    
    fetch(`/wilayah/api/kabupaten?provinsi_id=${provinsiId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(kab => {
                const option = document.createElement('option');
                option.value = kab.id;
                option.textContent = kab.nama;
                option.setAttribute('data-nama', kab.nama);
                kabupatenSelect.appendChild(option);
            });
            // Restore selected value if exists
            const selectedKab = '<?= $mastersiswa['idkabupaten'] ?? '' ?>';
            if (selectedKab) {
                kabupatenSelect.value = selectedKab;
                loadKecamatan();
            }
        })
        .catch(error => console.error('Error loading kabupaten:', error));
}

function loadKecamatan() {
    const kabupatenId = document.getElementById('idkabupaten').value;
    const kecamatanSelect = document.getElementById('idkecamatan');
    const kelurahanSelect = document.getElementById('idkelurahan');
    
    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
    
    if (!kabupatenId) return;
    
    fetch(`/wilayah/api/kecamatan?kabupaten_id=${kabupatenId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(kec => {
                const option = document.createElement('option');
                option.value = kec.id;
                option.textContent = kec.nama;
                option.setAttribute('data-nama', kec.nama);
                kecamatanSelect.appendChild(option);
            });
            // Restore selected value if exists
            const selectedKec = '<?= $mastersiswa['idkecamatan'] ?? '' ?>';
            if (selectedKec) {
                kecamatanSelect.value = selectedKec;
                loadKelurahan();
            }
        })
        .catch(error => console.error('Error loading kecamatan:', error));
}

function loadKelurahan() {
    const kecamatanId = document.getElementById('idkecamatan').value;
    const kelurahanSelect = document.getElementById('idkelurahan');
    
    kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
    
    if (!kecamatanId) return;
    
    fetch(`/wilayah/api/kelurahan?kecamatan_id=${kecamatanId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(kel => {
                const option = document.createElement('option');
                option.value = kel.id;
                option.textContent = kel.nama;
                option.setAttribute('data-nama', kel.nama);
                kelurahanSelect.appendChild(option);
            });
            // Restore selected value if exists
            const selectedKel = '<?= $mastersiswa['idkelurahan'] ?? '' ?>';
            if (selectedKel) {
                kelurahanSelect.value = selectedKel;
            }
        })
        .catch(error => console.error('Error loading kelurahan:', error));
}

function updateAlamatSiswa() {
    const alamatTextarea = document.getElementById('alamatsiswa');
    // Hanya update jika alamat masih kosong
    if (alamatTextarea.value.trim() !== '') {
        return;
    }
    
    const kelurahanSelect = document.getElementById('idkelurahan');
    const kecamatanSelect = document.getElementById('idkecamatan');
    const kabupatenSelect = document.getElementById('idkabupaten');
    const provinsiSelect = document.getElementById('idprovinsi');
    
    const parts = [];
    
    // Urutan: kelurahan, kecamatan, kabupaten, provinsi
    if (kelurahanSelect.value) {
        const selectedOption = kelurahanSelect.options[kelurahanSelect.selectedIndex];
        parts.push(selectedOption.textContent);
    }
    if (kecamatanSelect.value) {
        const selectedOption = kecamatanSelect.options[kecamatanSelect.selectedIndex];
        parts.push(selectedOption.textContent);
    }
    if (kabupatenSelect.value) {
        const selectedOption = kabupatenSelect.options[kabupatenSelect.selectedIndex];
        parts.push(selectedOption.textContent);
    }
    if (provinsiSelect.value) {
        const selectedOption = provinsiSelect.options[provinsiSelect.selectedIndex];
        parts.push(selectedOption.textContent);
    }
    
    if (parts.length > 0) {
        alamatTextarea.value = parts.join(', ');
    }
}

function loadKabupatenWali() {
    const provinsiId = document.getElementById('idprovinsiwali').value;
    const kabupatenSelect = document.getElementById('idkabupatenwali');
    const kecamatanSelect = document.getElementById('idkecamatanwali');
    const kelurahanSelect = document.getElementById('idkelurahanwali');
    
    kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
    
    if (!provinsiId) return;
    
    fetch(`/wilayah/api/kabupaten?provinsi_id=${provinsiId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(kab => {
                const option = document.createElement('option');
                option.value = kab.id;
                option.textContent = kab.nama;
                option.setAttribute('data-nama', kab.nama);
                kabupatenSelect.appendChild(option);
            });
            // Restore selected value if exists
            const selectedKab = '<?= $mastersiswa['idkabupatenwali'] ?? '' ?>';
            if (selectedKab) {
                kabupatenSelect.value = selectedKab;
                loadKecamatanWali();
            }
        })
        .catch(error => console.error('Error loading kabupaten wali:', error));
}

function loadKecamatanWali() {
    const kabupatenId = document.getElementById('idkabupatenwali').value;
    const kecamatanSelect = document.getElementById('idkecamatanwali');
    const kelurahanSelect = document.getElementById('idkelurahanwali');
    
    kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
    
    if (!kabupatenId) return;
    
    fetch(`/wilayah/api/kecamatan?kabupaten_id=${kabupatenId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(kec => {
                const option = document.createElement('option');
                option.value = kec.id;
                option.textContent = kec.nama;
                option.setAttribute('data-nama', kec.nama);
                kecamatanSelect.appendChild(option);
            });
            // Restore selected value if exists
            const selectedKec = '<?= $mastersiswa['idkecamatanwali'] ?? '' ?>';
            if (selectedKec) {
                kecamatanSelect.value = selectedKec;
                loadKelurahanWali();
            }
        })
        .catch(error => console.error('Error loading kecamatan wali:', error));
}

function loadKelurahanWali() {
    const kecamatanId = document.getElementById('idkecamatanwali').value;
    const kelurahanSelect = document.getElementById('idkelurahanwali');
    
    kelurahanSelect.innerHTML = '<option value="">Pilih Kelurahan/Desa</option>';
    
    if (!kecamatanId) return;
    
    fetch(`/wilayah/api/kelurahan?kecamatan_id=${kecamatanId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(kel => {
                const option = document.createElement('option');
                option.value = kel.id;
                option.textContent = kel.nama;
                option.setAttribute('data-nama', kel.nama);
                kelurahanSelect.appendChild(option);
            });
            // Restore selected value if exists
            const selectedKel = '<?= $mastersiswa['idkelurahanwali'] ?? '' ?>';
            if (selectedKel) {
                kelurahanSelect.value = selectedKel;
            }
        })
        .catch(error => console.error('Error loading kelurahan wali:', error));
}

function updateAlamatWali() {
    const alamatTextarea = document.getElementById('alamatwali');
    // Hanya update jika alamat masih kosong
    if (alamatTextarea.value.trim() !== '') {
        return;
    }
    
    const kelurahanSelect = document.getElementById('idkelurahanwali');
    const kecamatanSelect = document.getElementById('idkecamatanwali');
    const kabupatenSelect = document.getElementById('idkabupatenwali');
    const provinsiSelect = document.getElementById('idprovinsiwali');
    
    const parts = [];
    
    // Urutan: kelurahan, kecamatan, kabupaten, provinsi
    if (kelurahanSelect.value) {
        const selectedOption = kelurahanSelect.options[kelurahanSelect.selectedIndex];
        parts.push(selectedOption.textContent);
    }
    if (kecamatanSelect.value) {
        const selectedOption = kecamatanSelect.options[kecamatanSelect.selectedIndex];
        parts.push(selectedOption.textContent);
    }
    if (kabupatenSelect.value) {
        const selectedOption = kabupatenSelect.options[kabupatenSelect.selectedIndex];
        parts.push(selectedOption.textContent);
    }
    if (provinsiSelect.value) {
        const selectedOption = provinsiSelect.options[provinsiSelect.selectedIndex];
        parts.push(selectedOption.textContent);
    }
    
    if (parts.length > 0) {
        alamatTextarea.value = parts.join(', ');
    }
}

function loadKelas() {
    const tahunAjaranId = document.getElementById('idtahunajaran').value;
    const kelasSelect = document.getElementById('idkelas');
    
    kelasSelect.innerHTML = '<option value="">Pilih Kelas</option>';
    
    if (!tahunAjaranId) return;
    
    fetch(`/mastersiswa/api/getkelas?idtahunajaran=${tahunAjaranId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(kelas => {
                const option = document.createElement('option');
                option.value = kelas.idkelas;
                option.textContent = `${kelas.namakelas} (${kelas.kelas})`;
                kelasSelect.appendChild(option);
            });
            // Restore selected value if exists
            const selectedKelas = '<?= $mastersiswa['idkelas'] ?? '' ?>';
            if (selectedKelas) {
                kelasSelect.value = selectedKelas;
            }
        })
        .catch(error => console.error('Error loading kelas:', error));
}

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load wilayah for siswa
    const provinsiId = document.getElementById('idprovinsi').value;
    if (provinsiId) {
        loadKabupaten();
    }
    
    // Load wilayah for wali
    const provinsiWaliId = document.getElementById('idprovinsiwali').value;
    if (provinsiWaliId) {
        loadKabupatenWali();
    }
    
    // Load kelas
    const tahunAjaranId = document.getElementById('idtahunajaran').value;
    if (tahunAjaranId) {
        loadKelas();
    }
    
    // Phone number input handler
    function initPhoneInput(inputId, hiddenId) {
        const phoneInput = document.getElementById(inputId);
        const hiddenInput = document.getElementById(hiddenId);
        
        if (!phoneInput || !hiddenInput) return;
        
        // Only allow numbers
        phoneInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Format and update hidden field on blur
        phoneInput.addEventListener('blur', function() {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value.startsWith('0')) {
                value = value.substring(1); // Remove leading zero
            }
            // Update hidden field with full format (+62xxxxxxxxxx)
            if (value) {
                hiddenInput.value = '+62' + value;
            } else {
                hiddenInput.value = '';
            }
        });
        
        // Update hidden field on form submit
        const form = phoneInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                let value = phoneInput.value.replace(/[^0-9]/g, '');
                if (value.startsWith('0')) {
                    value = value.substring(1);
                }
                if (value) {
                    hiddenInput.value = '+62' + value;
                } else {
                    hiddenInput.value = '';
                }
            });
        }
    }
    
    // Initialize phone inputs
    initPhoneInput('nomorhp', 'nomorhp_full');
    initPhoneInput('nomorhpwali', 'nomorhpwali_full');
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

