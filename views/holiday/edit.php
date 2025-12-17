<?php
$title = 'Edit Hari Libur';
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
                    <li class="breadcrumb-item"><a href="/holiday">Hari Libur</a></li>
                    <li class="breadcrumb-item active">Edit Hari Libur</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Hari Libur</h4>
                    </div>
                </div>
                <form method="POST" action="/holiday/edit/<?= $holiday['holiday_id'] ?>">
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="holiday_date" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="holiday_date" name="holiday_date" 
                                       value="<?= date('Y-m-d', strtotime($holiday['holiday_date'])) ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="holiday_name" class="form-label">Nama Hari Libur <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="holiday_name" name="holiday_name" 
                                       value="<?= htmlspecialchars($holiday['holiday_name']) ?>" 
                                       placeholder="Contoh: Hari Raya Idul Fitri" required maxlength="100">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_national" name="is_national" value="1" 
                                           <?= $holiday['is_national'] == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_national">Hari Libur Nasional</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_recurring_yearly" name="is_recurring_yearly" value="1"
                                           <?= $holiday['is_recurring_yearly'] == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_recurring_yearly">Berulang Setiap Tahun</label>
                                </div>
                            </div>
                        </div>
                        
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/holiday" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                    <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Update</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.form-check {
    display: flex;
    align-items: center;
    min-height: 1.5em;
}

.form-check-input {
    border-radius: 4px !important;
    width: 1.25em !important;
    height: 1.25em !important;
    margin-top: 0 !important;
    margin-right: 0.5em;
    cursor: pointer;
    flex-shrink: 0;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.form-check-input:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.form-check-label {
    margin-left: 0;
    cursor: pointer;
    user-select: none;
    line-height: 1.5;
    display: flex;
    align-items: center;
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

