<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-excel me-2"></i>Upload Data Fingerprint Excel
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Success Message -->
                    <?php if (isset($success_message) && $success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success_message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Upload Form -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-upload me-2"></i>Upload File Excel</h6>
                        </div>
                        <div class="card-body">
                            <form action="/fingerimport/upload" method="POST" enctype="multipart/form-data" id="uploadForm">
                                <div class="mb-3">
                                    <label for="excel_file" class="form-label">Pilih File Excel (.xlsx atau .xls)</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="excel_file" name="excel_file" 
                                               accept=".xlsx,.xls" required>
                                        <button type="submit" class="btn btn-primary" id="uploadBtn">
                                            <i class="fas fa-upload me-2"></i>Upload & Proses
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        Format yang didukung: .xlsx, .xls (Maksimal 10MB)
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Error Message -->
                    <?php if (isset($error) && $error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle me-2"></i>
                            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Info -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Catatan:</strong> File Excel akan langsung diproses dan data absensi akan disimpan ke database. 
                        File tidak akan disimpan di server. Pastikan <strong>fingerprint_id</strong> di file Excel sesuai dengan <strong>noabsensi</strong> di tabel mastersiswa atau masterguru.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle form submission with loading state
document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('uploadBtn');
    const fileInput = document.getElementById('excel_file');
    
    if (!fileInput.files.length) {
        e.preventDefault();
        alert('Silakan pilih file terlebih dahulu.');
        return false;
    }
    
    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

