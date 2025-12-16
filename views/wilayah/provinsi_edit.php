<?php
$title = 'Edit Provinsi';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/wilayah/provinsi">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/wilayah/provinsi">Provinsi</a></li>
                    <li class="breadcrumb-item active">Edit Provinsi</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Provinsi</h4>
                    </div>
                </div>
                <form method="POST" action="/wilayah/provinsi/edit/<?= $provinsi['id'] ?>">
                <div class="card-body">
                        <div class="mb-3">
                            <label for="kode" class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode" name="kode" required value="<?= htmlspecialchars($provinsi['kode']) ?>">
                            <small class="form-text text-muted">Kode provinsi sesuai format Kemendagri (2 digit)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required value="<?= htmlspecialchars($provinsi['nama']) ?>">
                        </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/wilayah/provinsi" class="btn btn-secondary"><?= icon('back', 'me-1 mb-1', 18) ?>Kembali</a>
                    <button type="submit" class="btn btn-primary"><?= icon('update', 'me-1 mb-1', 18) ?>Update</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

