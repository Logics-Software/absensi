<?php
$title = 'Edit Absensi Guru';
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
                    <li class="breadcrumb-item"><a href="/absensiguru">Absensi Guru</a></li>
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
                        <h4 class="mb-0">Edit Data Absensi Guru</h4>
                    </div>
                </div>
                <form method="POST" action="/absensiguru/edit/<?= $absensi['id'] ?>" id="formAbsensi">
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nip" class="form-label">NIP <span class="text-danger">*</span></label>
                                <select class="form-select" id="nip" name="nip" required>
                                    <option value="">Pilih Guru</option>
                                    <?php foreach ($teachersList as $teacher): ?>
                                        <option value="<?= htmlspecialchars($teacher['nip']) ?>" <?= ($absensi['nip'] == $teacher['nip']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($teacher['nip']) ?> - <?= htmlspecialchars($teacher['namaguru']) ?>
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
                                <label for="jammasuk" class="form-label">Jam Masuk <span class="text-danger" id="jammasuk-required">*</span></label>
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
                                <label for="jamkeluar" class="form-label">Jam Pulang <span class="text-danger" id="jamkeluar-required">*</span></label>
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
                                    <option value="terlambat" <?= ($absensi['status'] == 'terlambat') ? 'selected' : '' ?>>Terlambat</option>
                                    <option value="pulang_awal" <?= ($absensi['status'] == 'pulang_awal') ? 'selected' : '' ?>>Pulang Awal</option>
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
                        <a href="/absensiguru" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Choices.js for NIP select
    function initChoices() {
        const nipSelect = document.getElementById('nip');
        if (!nipSelect) return;
        
        // Wait for Choices.js to load
        if (typeof Choices === 'undefined') {
            setTimeout(initChoices, 100);
            return;
        }
        
        // Destroy existing instance if any
        if (nipSelect.choicesInstance) {
            nipSelect.choicesInstance.destroy();
        }
        
        const nipChoice = new Choices(nipSelect, {
            searchEnabled: true,
            searchChoices: true,
            itemSelectText: '',
            noResultsText: 'Tidak ada guru yang ditemukan',
            noChoicesText: 'Tidak ada guru tersedia',
            placeholder: true,
            placeholderValue: 'Pilih Guru',
            searchPlaceholderValue: 'Cari NIP atau nama guru...',
            shouldSort: true,
            shouldSortItems: true,
            fuseOptions: {
                threshold: 0.3,
                distance: 100,
                minMatchCharLength: 1
            }
        });
        
        // Store instance for later use
        nipSelect.choicesInstance = nipChoice;
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
    // Only format, don't validate during typing
    function formatTimeInput(input, validate = false) {
        if (!input) return;
        
        if (!input.value || input.value.trim() === '') {
            if (validate) {
                input.classList.remove('is-invalid', 'is-valid');
            }
            return;
        }
        
        let value = input.value.replace(/[^0-9:]/g, ''); // Remove non-numeric except :
        
        // Auto-format as user types (only add colon, don't force format)
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
        
        // Only format and validate if validate flag is true (on blur)
        if (validate) {
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
            validateTimeInput(input);
        } else {
            // Just update the value without validation during typing
            input.value = value;
        }
    }
    
    // Time mask - auto format as user types (light formatting only)
    function applyTimeMask(input) {
        if (!input) return;
        input.addEventListener('input', function(e) {
            // Only do light formatting during typing (add colon, limit length)
            formatTimeInput(this, false);
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
        // Only validate on blur (lost focus), not on change
        jamMasukInput.addEventListener('blur', function() {
            formatTimeInput(this, true); // true = validate and format
        });
        
        // Double click to set current time
        jamMasukInput.addEventListener('dblclick', function() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            this.value = hours + ':' + minutes;
            formatTimeInput(this, true); // true = validate and format
        });
    }
    
    if (jamKeluarInput) {
        applyTimeMask(jamKeluarInput);
        // Only validate on blur (lost focus), not on change
        jamKeluarInput.addEventListener('blur', function() {
            formatTimeInput(this, true); // true = validate and format
        });
        
        // Double click to set current time
        jamKeluarInput.addEventListener('dblclick', function() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            this.value = hours + ':' + minutes;
            formatTimeInput(this, true); // true = validate and format
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
    
    // Handle status change - make jam masuk and jam pulang optional for Alpha, Ijin, Sakit
    const statusSelect = document.getElementById('status');
    if (statusSelect) {
        function updateTimeFieldsRequired() {
            const status = statusSelect.value;
            const optionalStatuses = ['alpha', 'ijin', 'sakit'];
            const isOptional = optionalStatuses.includes(status);
            
            const jamMasukRequired = document.getElementById('jammasuk-required');
            const jamKeluarRequired = document.getElementById('jamkeluar-required');
            
            if (jamMasukInput) {
                if (isOptional) {
                    jamMasukInput.removeAttribute('required');
                    jamMasukInput.setAttribute('data-optional', 'true');
                    if (jamMasukRequired) jamMasukRequired.style.display = 'none';
                } else {
                    jamMasukInput.setAttribute('required', 'required');
                    jamMasukInput.removeAttribute('data-optional');
                    if (jamMasukRequired) jamMasukRequired.style.display = 'inline';
                }
            }
            
            if (jamKeluarInput) {
                if (isOptional) {
                    jamKeluarInput.removeAttribute('required');
                    jamKeluarInput.setAttribute('data-optional', 'true');
                    if (jamKeluarRequired) jamKeluarRequired.style.display = 'none';
                } else {
                    jamKeluarInput.setAttribute('required', 'required');
                    jamKeluarInput.removeAttribute('data-optional');
                    if (jamKeluarRequired) jamKeluarRequired.style.display = 'inline';
                }
            }
        }
        
        // Update on status change
        statusSelect.addEventListener('change', updateTimeFieldsRequired);
        
        // Update on page load
        updateTimeFieldsRequired();
    }
    
    // Form validation before submit
    const absensiForm = document.querySelector('form');
    if (absensiForm) {
        absensiForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            const status = statusSelect ? statusSelect.value : '';
            const optionalStatuses = ['alpha', 'ijin', 'sakit'];
            const isOptional = optionalStatuses.includes(status);
            
            // Validate jam masuk (only if required)
            if (jamMasukInput && !isOptional) {
                if (!validateTimeInput(jamMasukInput)) {
                    isValid = false;
                    jamMasukInput.focus();
                }
            } else if (jamMasukInput && isOptional && jamMasukInput.value.trim()) {
                // If optional but has value, validate it
                if (!validateTimeInput(jamMasukInput)) {
                    isValid = false;
                    jamMasukInput.focus();
                }
            }
            
            // Validate jam keluar (only if required)
            if (jamKeluarInput && isValid) {
                if (!isOptional) {
                    if (!validateTimeInput(jamKeluarInput)) {
                        isValid = false;
                        jamKeluarInput.focus();
                    }
                } else if (jamKeluarInput.value.trim()) {
                    // If optional but has value, validate it
                    if (!validateTimeInput(jamKeluarInput)) {
                        isValid = false;
                        jamKeluarInput.focus();
                    }
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

