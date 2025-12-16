<?php
$title = 'Edit Kabupaten/Kota';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/wilayah/kabupaten">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/wilayah/kabupaten">Kabupaten/Kota</a></li>
                    <li class="breadcrumb-item active">Edit Kabupaten/Kota</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Kabupaten/Kota</h4>
                    </div>
                </div>
                <form method="POST" action="/wilayah/kabupaten/edit/<?= $kabupaten['id'] ?>">
                <div class="card-body">
                        <div class="mb-3">
                            <label for="kode" class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode" name="kode" required value="<?= htmlspecialchars($kabupaten['kode']) ?>">
                            <small class="form-text text-muted">Kode kabupaten/kota sesuai format Kemendagri (5 digit)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="provinsi_id" class="form-label">Provinsi <span class="text-danger">*</span></label>
                            <select class="form-select" id="provinsi_id" name="provinsi_id" required>
                                <option value="">Pilih Provinsi</option>
                                <?php foreach ($provinsiList as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($kabupaten['provinsi_id'] == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required value="<?= htmlspecialchars($kabupaten['nama']) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipe" class="form-label">Tipe <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="Kabupaten" <?= ($kabupaten['tipe'] === 'Kabupaten') ? 'selected' : '' ?>>Kabupaten</option>
                                <option value="Kota" <?= ($kabupaten['tipe'] === 'Kota') ? 'selected' : '' ?>>Kota</option>
                            </select>
                        </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/wilayah/kabupaten" class="btn btn-secondary"><?= icon('back', 'me-1 mb-1', 18) ?>Kembali</a>
                    <button type="submit" class="btn btn-primary"><?= icon('update', 'me-1 mb-1', 18) ?>Update</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

