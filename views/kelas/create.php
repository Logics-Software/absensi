<?php
$title = 'Tambah Kelas';
require __DIR__ . '/../layouts/header.php';

// Define kelas options based on jenjang (case-insensitive)
$kelasOptions = [];
$jenjangUpper = strtoupper(trim($jenjang ?? ''));
if ($jenjangUpper == 'SD') {
    $kelasOptions = ['I', 'II', 'III', 'IV', 'V', 'VI'];
} elseif ($jenjangUpper == 'SMP') {
    $kelasOptions = ['VII', 'VIII', 'IX'];
} elseif ($jenjangUpper == 'SMA') {
    $kelasOptions = ['X', 'XI', 'XII'];
} else {
    // Default to SD
    $kelasOptions = ['I', 'II', 'III', 'IV', 'V', 'VI'];
}
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/kelas">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/kelas">Kelas</a></li>
                    <li class="breadcrumb-item active">Tambah Kelas</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Tambah Data Kelas</h4>
                    </div>
                </div>
                <form method="POST" action="/kelas/create">
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idtahunajaran" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                                <select class="form-select" id="idtahunajaran" name="idtahunajaran" required>
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
                                <label for="kelas" class="form-label">Kelas <span class="text-danger">*</span></label>
                                <select class="form-select" id="kelas" name="kelas" required>
                                    <option value="">Pilih Kelas</option>
                                    <?php foreach ($kelasOptions as $opt): ?>
                                        <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Jenjang: <?= htmlspecialchars($jenjang) ?></small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idjurusan" class="form-label">Jurusan</label>
                                <select class="form-select" id="idjurusan" name="idjurusan">
                                    <option value="">Pilih Jurusan (Opsional)</option>
                                    <?php foreach ($jurusanList as $jurusan): ?>
                                        <?php if ($jurusan['status'] == 'aktif'): ?>
                                        <option value="<?= $jurusan['idjurusan'] ?>">
                                            <?= htmlspecialchars($jurusan['namajurusan']) ?>
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="namakelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namakelas" name="namakelas" required placeholder="Contoh: VII-A, X IPA 1" maxlength="100">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="idguru" class="form-label">Wali Kelas</label>
                                <select class="form-select" id="idguru" name="idguru">
                                    <option value="">Pilih Wali Kelas (Opsional)</option>
                                    <?php foreach ($guruList as $guru): ?>
                                        <option value="<?= $guru['id'] ?>">
                                            <?= htmlspecialchars($guru['namaguru']) ?>
                                            <?php if (!empty($guru['nip'])): ?>
                                                (<?= htmlspecialchars($guru['nip']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
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
                    <a href="/kelas" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                    <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Simpan Kelas</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

