<?php
$title = 'Tambah Jurusan';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/jurusan">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/jurusan">Jurusan</a></li>
                    <li class="breadcrumb-item active">Tambah Jurusan</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Tambah Data Jurusan</h4>
                    </div>
                </div>
                <form method="POST" action="/jurusan/create">
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="namajurusan" class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namajurusan" name="namajurusan" required placeholder="Masukkan nama jurusan" maxlength="100">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
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
                    <a href="/jurusan" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                    <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Simpan Jurusan</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

