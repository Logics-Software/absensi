<?php
$title = 'Tambah Master Siswa';
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
                    <li class="breadcrumb-item active">Tambah Siswa</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Tambah Data Master Siswa</h4>
                    </div>
                </div>
                <form method="POST" action="/mastersiswa/create" id="formSiswa" enctype="multipart/form-data">
                <div class="card-body">
                        <!-- Data Siswa -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="nisn" class="form-label">NISN</label>
                                <input type="text" class="form-control" id="nisn" name="nisn" placeholder="Nomor Induk Siswa Nasional" maxlength="50">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" placeholder="Nomor Induk Kependudukan" maxlength="50">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="noabsensi" class="form-label">No. Absensi</label>
                                <input type="text" class="form-control" id="noabsensi" name="noabsensi" placeholder="Nomor Absensi" maxlength="20">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="namasiswa" class="form-label">Nama Siswa <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namasiswa" name="namasiswa" required placeholder="Masukkan nama siswa" maxlength="100">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="jeniskelamin" class="form-label">Jenis Kelamin</label>
                                <select class="form-select" id="jeniskelamin" name="jeniskelamin">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="laki-laki">Laki-laki</option>
                                    <option value="perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tempatlahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempatlahir" name="tempatlahir" placeholder="Masukkan tempat lahir" maxlength="100">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tanggallahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggallahir" name="tanggallahir">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="contoh@email.com" maxlength="255">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nomorhp" class="form-label">Nomor HP</label>
                                <input type="text" class="form-control" id="nomorhp" name="nomorhp" placeholder="+62xxxxxxxxxx" maxlength="50">
                            </div>
                        </div>
                        
                        <!-- Alamat Siswa -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idprovinsi" class="form-label">Provinsi</label>
                                <select class="form-select" id="idprovinsi" name="idprovinsi" onchange="loadKabupaten();">
                                    <option value="">Pilih Provinsi</option>
                                    <?php foreach ($provinsiList as $p): ?>
                                        <option value="<?= $p['id'] ?>" data-nama="<?= htmlspecialchars($p['nama']) ?>"><?= htmlspecialchars($p['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="idkabupaten" class="form-label">Kabupaten/Kota</label>
                                <select class="form-select" id="idkabupaten" name="idkabupaten" onchange="loadKecamatan();">
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idkecamatan" class="form-label">Kecamatan</label>
                                <select class="form-select" id="idkecamatan" name="idkecamatan" onchange="loadKelurahan();">
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="idkelurahan" class="form-label">Kelurahan/Desa</label>
                                <select class="form-select" id="idkelurahan" name="idkelurahan" onchange="updateAlamatSiswa();">
                                    <option value="">Pilih Kelurahan/Desa</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamatsiswa" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamatsiswa" name="alamatsiswa" rows="2" placeholder="Masukkan alamat lengkap siswa" maxlength="255"></textarea>
                        </div>
                        
                        <!-- Tahun Ajaran dan Kelas -->
                        <h5 class="mb-3 mt-4 bg-secondary text-white text-center p-3 rounded">Tahun Ajaran & Kelas (Terakhir)</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idtahunajaran" class="form-label">Tahun Ajaran</label>
                                <select class="form-select" id="idtahunajaran" name="idtahunajaran" onchange="loadKelas()">
                                    <option value="">Pilih Tahun Ajaran</option>
                                    <?php foreach ($tahunAjaranList as $ta): ?>
                                        <option value="<?= $ta['id'] ?>" <?= ($activeTahunAjaran && $activeTahunAjaran['id'] == $ta['id']) ? 'selected' : '' ?>>
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
                                        <option value="<?= $kelas['idkelas'] ?>">
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
                                <input type="text" class="form-control" id="namawali" name="namawali" placeholder="Masukkan nama wali" maxlength="100">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="hubungan" class="form-label">Hubungan</label>
                                <select class="form-select" id="hubungan" name="hubungan">
                                    <option value="">Pilih Hubungan</option>
                                    <option value="orangtua">Orang Tua</option>
                                    <option value="saudara">Saudara</option>
                                    <option value="lainlain">Lain-lain</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="nomorhpwali" class="form-label">Nomor HP Wali</label>
                                <input type="text" class="form-control" id="nomorhpwali" name="nomorhpwali" placeholder="+62xxxxxxxxxx" maxlength="50">
                            </div>
                        </div>
                        
                        <!-- Alamat Wali -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idprovinsiwali" class="form-label">Provinsi Wali</label>
                                <select class="form-select" id="idprovinsiwali" name="idprovinsiwali" onchange="loadKabupatenWali();">
                                    <option value="">Pilih Provinsi</option>
                                    <?php foreach ($provinsiList as $p): ?>
                                        <option value="<?= $p['id'] ?>" data-nama="<?= htmlspecialchars($p['nama']) ?>"><?= htmlspecialchars($p['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="idkabupatenwali" class="form-label">Kabupaten/Kota Wali</label>
                                <select class="form-select" id="idkabupatenwali" name="idkabupatenwali" onchange="loadKecamatanWali();">
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idkecamatanwali" class="form-label">Kecamatan Wali</label>
                                <select class="form-select" id="idkecamatanwali" name="idkecamatanwali" onchange="loadKelurahanWali();">
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="idkelurahanwali" class="form-label">Kelurahan/Desa Wali</label>
                                <select class="form-select" id="idkelurahanwali" name="idkelurahanwali" onchange="updateAlamatWali();">
                                    <option value="">Pilih Kelurahan/Desa</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamatwali" class="form-label">Alamat Lengkap Wali</label>
                            <textarea class="form-control" id="alamatwali" name="alamatwali" rows="2" placeholder="Masukkan alamat lengkap wali" maxlength="255"></textarea>
                        </div>
                        
                        <!-- Foto -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="foto" class="form-label">Foto Siswa</label>
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
                                    <option value="aktif" selected>Aktif</option>
                                    <option value="non aktif">Non Aktif</option>
                                </select>
                            </div>
                        </div>
                        
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/mastersiswa" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                    <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Simpan Siswa</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function loadKabupaten() {
    const provinsiId = document.getElementById('idprovinsi').value;
    const provinsiSelect = document.getElementById('idprovinsi');
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
        })
        .catch(error => console.error('Error loading kelas:', error));
}

// Load kelas on page load if tahun ajaran is selected
document.addEventListener('DOMContentLoaded', function() {
    const tahunAjaranSelect = document.getElementById('idtahunajaran');
    if (tahunAjaranSelect && tahunAjaranSelect.value) {
        loadKelas();
    }
    
    // Phone number formatting for siswa
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
    }
    
    // Phone number formatting for wali
    const nomorHpWaliInput = document.getElementById('nomorhpwali');
    if (nomorHpWaliInput) {
        nomorHpWaliInput.addEventListener('blur', function() {
            let value = this.value.replace(/[^0-9]/g, ''); // Remove non-digits
            if (value.startsWith('0')) {
                value = value.substring(1); // Remove leading zero
            }
            if (!value.startsWith('62')) {
                value = '62' + value; // Add 62 prefix
            }
            this.value = '+' + value;
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

