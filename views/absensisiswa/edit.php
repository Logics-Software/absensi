<?php
$title = 'Edit Absensi Siswa';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}
// Add Choices.js CSS and JS
$additionalStyles = ['https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css'];
$additionalScripts = ['https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js'];
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/absensisiswa">Absensi Siswa</a></li>
                    <li class="breadcrumb-item active">Edit Absensi</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data Absensi Siswa</h4>
                    </div>
                </div>
                <form method="POST" action="/absensisiswa/edit/<?= $absensi['id'] ?>" id="formAbsensi">
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nisn" class="form-label">NISN <span class="text-danger">*</span></label>
                                <select class="form-select" id="nisn" name="nisn" required>
                                    <option value="">Pilih Siswa</option>
                                    <?php foreach ($studentsList as $student): ?>
                                        <option value="<?= htmlspecialchars($student['nisn']) ?>" <?= ($absensi['nisn'] == $student['nisn']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($student['nisn']) ?> - <?= htmlspecialchars($student['namasiswa']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tanggalabsen" class="form-label">Tanggal Absen <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggalabsen" name="tanggalabsen" value="<?= !empty($absensi['tanggalabsen']) ? htmlspecialchars($absensi['tanggalabsen']) : '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jammasuk" class="form-label">Jam Masuk <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control time-picker" id="jammasuk" name="jammasuk" value="<?= !empty($absensi['jammasuk']) ? date('H:i', strtotime($absensi['jammasuk'])) : '' ?>" placeholder="00:00" pattern="^([0-1][0-9]|2[0-3]):[0-5][0-9]$" maxlength="5" required>
                                    <button type="button" class="btn btn-outline-secondary" id="jammasuk-btn" title="Pilih Waktu">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="jammasuk-error"></div>
                                <small class="text-muted">Format: HH:MM (00:00 - 23:59)</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="jamkeluar" class="form-label">Jam Pulang <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control time-picker" id="jamkeluar" name="jamkeluar" value="<?= !empty($absensi['jamkeluar']) ? date('H:i', strtotime($absensi['jamkeluar'])) : '' ?>" placeholder="00:00" pattern="^([0-1][0-9]|2[0-3]):[0-5][0-9]$" maxlength="5" required>
                                    <button type="button" class="btn btn-outline-secondary" id="jamkeluar-btn" title="Pilih Waktu">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="jamkeluar-error"></div>
                                <small class="text-muted">Format: HH:MM (00:00 - 23:59)</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="hadir" <?= ($absensi['status'] == 'hadir') ? 'selected' : '' ?>>Hadir</option>
                                    <option value="alpha" <?= ($absensi['status'] == 'alpha') ? 'selected' : '' ?>>Alpha</option>
                                    <option value="ijin" <?= ($absensi['status'] == 'ijin') ? 'selected' : '' ?>>Ijin</option>
                                    <option value="sakit" <?= ($absensi['status'] == 'sakit') ? 'selected' : '' ?>>Sakit</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Masukkan keterangan (opsional)" value="<?= htmlspecialchars($absensi['keterangan'] ?? '') ?>" maxlength="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="/absensisiswa" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.time-picker {
    cursor: pointer;
    font-family: monospace;
    font-size: 1rem;
    text-align: center;
}
.input-group .btn {
    border-left: 0;
}
.input-group .form-control:focus + .btn {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Choices.js for NISN select
    function initChoices() {
        const nisnSelect = document.getElementById('nisn');
        if (!nisnSelect) return;
        
        // Wait for Choices.js to load
        if (typeof Choices === 'undefined') {
            setTimeout(initChoices, 100);
            return;
        }
        
        // Destroy existing instance if any
        if (nisnSelect.choicesInstance) {
            nisnSelect.choicesInstance.destroy();
        }
        
        const nisnChoice = new Choices(nisnSelect, {
            searchEnabled: true,
            searchChoices: true,
            itemSelectText: '',
            noResultsText: 'Tidak ada siswa yang ditemukan',
            noChoicesText: 'Tidak ada siswa tersedia',
            placeholder: true,
            placeholderValue: 'Pilih Siswa',
            searchPlaceholderValue: 'Cari NISN atau nama siswa...',
            shouldSort: true,
            shouldSortItems: true,
            fuseOptions: {
                threshold: 0.3,
                distance: 100,
                minMatchCharLength: 1
            }
        });
        
        // Store instance for later use
        nisnSelect.choicesInstance = nisnChoice;
    }
    
    // Initialize Choices.js
    initChoices();
    
    // Time picker enhancement - using text input with 24-hour format
    const jamMasukInput = document.getElementById('jammasuk');
    const jamKeluarInput = document.getElementById('jamkeluar');
    const jamMasukBtn = document.getElementById('jammasuk-btn');
    const jamKeluarBtn = document.getElementById('jamkeluar-btn');
    
    // Validate time input format (HH:MM, 00-23:00-59)
    function validateTimeInput(input) {
        if (!input) return false;
        
        const timePattern = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
        const value = input.value.trim();
        
        if (!value) {
            input.setCustomValidity('Jam harus diisi');
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            return false;
        }
        
        if (!timePattern.test(value)) {
            input.setCustomValidity('Format jam tidak valid. Gunakan format HH:MM (00:00 - 23:59)');
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            return false;
        }
        
        // Additional validation: check if hours and minutes are valid
        const parts = value.split(':');
        const hours = parseInt(parts[0], 10);
        const minutes = parseInt(parts[1], 10);
        
        if (isNaN(hours) || hours < 0 || hours > 23) {
            input.setCustomValidity('Jam harus antara 00-23');
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            return false;
        }
        
        if (isNaN(minutes) || minutes < 0 || minutes > 59) {
            input.setCustomValidity('Menit harus antara 00-59');
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            return false;
        }
        
        // Valid
        input.setCustomValidity('');
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        return true;
    }
    
    // Format time input to HH:MM - 24 hour format (00-23)
    function formatTimeInput(input) {
        if (!input) return;
        
        if (!input.value || input.value.trim() === '') {
            input.classList.remove('is-invalid', 'is-valid');
            return;
        }
        
        let value = input.value.replace(/[^0-9:]/g, ''); // Remove non-numeric except :
        
        // Auto-format as user types
        if (value.length > 0 && !value.includes(':')) {
            if (value.length === 2) {
                value = value + ':';
            } else if (value.length > 2) {
                value = value.substring(0, 2) + ':' + value.substring(2);
            }
        }
        
        // Limit to 5 characters (HH:MM)
        if (value.length > 5) {
            value = value.substring(0, 5);
        }
        
        // Validate and fix format
        const parts = value.split(':');
        if (parts.length === 2) {
            let hours = parseInt(parts[0], 10) || 0;
            let minutes = parseInt(parts[1], 10) || 0;
            
            // Ensure hours are 00-23
            if (hours < 0) hours = 0;
            if (hours > 23) hours = 23;
            
            // Ensure minutes are 00-59
            if (minutes < 0) minutes = 0;
            if (minutes > 59) minutes = 59;
            
            // Format with leading zeros
            value = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
        }
        
        input.value = value;
        
        // Validate after formatting
        validateTimeInput(input);
    }
    
    // Time mask - auto format as user types
    function applyTimeMask(input) {
        input.addEventListener('input', function(e) {
            formatTimeInput(this);
        });
        
        input.addEventListener('keydown', function(e) {
            // Allow: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }
    
    // Apply time mask to inputs
    if (jamMasukInput) {
        applyTimeMask(jamMasukInput);
        jamMasukInput.addEventListener('input', function() {
            formatTimeInput(this);
        });
        jamMasukInput.addEventListener('change', function() {
            formatTimeInput(this);
            validateTimeInput(this);
        });
        jamMasukInput.addEventListener('blur', function() {
            formatTimeInput(this);
            validateTimeInput(this);
        });
        
        // Double click to set current time
        jamMasukInput.addEventListener('dblclick', function() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            this.value = hours + ':' + minutes;
            formatTimeInput(this);
            validateTimeInput(this);
        });
    }
    
    if (jamKeluarInput) {
        applyTimeMask(jamKeluarInput);
        jamKeluarInput.addEventListener('input', function() {
            formatTimeInput(this);
        });
        jamKeluarInput.addEventListener('change', function() {
            formatTimeInput(this);
            validateTimeInput(this);
        });
        jamKeluarInput.addEventListener('blur', function() {
            formatTimeInput(this);
            validateTimeInput(this);
        });
        
        // Double click to set current time
        jamKeluarInput.addEventListener('dblclick', function() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            this.value = hours + ':' + minutes;
            formatTimeInput(this);
            validateTimeInput(this);
        });
    }
    
    // Button click to focus input
    if (jamMasukBtn && jamMasukInput) {
        jamMasukBtn.addEventListener('click', function(e) {
            e.preventDefault();
            jamMasukInput.focus();
        });
    }
    
    if (jamKeluarBtn && jamKeluarInput) {
        jamKeluarBtn.addEventListener('click', function(e) {
            e.preventDefault();
            jamKeluarInput.focus();
        });
    }
    
    // Form validation before submit
    const absensiForm = document.querySelector('form');
    if (absensiForm) {
        absensiForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate jam masuk
            if (jamMasukInput) {
                if (!validateTimeInput(jamMasukInput)) {
                    isValid = false;
                    jamMasukInput.focus();
                }
            }
            
            // Validate jam keluar
            if (jamKeluarInput && isValid) {
                if (!validateTimeInput(jamKeluarInput)) {
                    isValid = false;
                    jamKeluarInput.focus();
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    }
    
    // Format on page load
    if (jamMasukInput && jamMasukInput.value) {
        formatTimeInput(jamMasukInput);
        validateTimeInput(jamMasukInput);
    }
    if (jamKeluarInput && jamKeluarInput.value) {
        formatTimeInput(jamKeluarInput);
        validateTimeInput(jamKeluarInput);
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

