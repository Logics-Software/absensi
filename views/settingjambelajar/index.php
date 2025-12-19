<?php
$title = 'Setting Jam Belajar';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}
require __DIR__ . '/../layouts/header.php';

$days = [
    'senin' => 'Senin',
    'selasa' => 'Selasa',
    'rabu' => 'Rabu',
    'kamis' => 'Kamis',
    'jumat' => 'Jumat',
    'sabtu' => 'Sabtu',
    'minggu' => 'Minggu'
];
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Setting Jam Belajar</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Setting Jam Belajar</h4>
                    </div>
                </div>

                <form method="POST" action="/settingjambelajar" id="formSettingJamBelajar">
                    <div class="card-body">
                        <!-- <p class="text-muted mb-4">Atur jam belajar untuk setiap hari dalam seminggu. Aktifkan hari yang digunakan dan isi jam masuk serta jam pulang.</p> -->
                        
                        <?php foreach ($days as $dayKey => $dayLabel): ?>
                        <?php 
                        $isActive = ($setting[$dayKey] ?? 'nonaktif') === 'aktif';
                        $jamMasuk = $setting['jammasuk' . $dayKey] ?? '';
                        $jamPulang = $setting['jampulang' . $dayKey] ?? '';
                        ?>
                        <div class="day-setting mb-4 p-3 border rounded" data-day="<?= $dayKey ?>">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="mb-0"><?= $dayLabel ?></h5>
                                <div class="form-check form-switch">
                                    <input class="form-check-input day-switch" type="checkbox" 
                                           id="switch_<?= $dayKey ?>" 
                                           name="<?= $dayKey ?>" 
                                           value="aktif"
                                           <?= $isActive ? 'checked' : '' ?>
                                           onchange="toggleDayInputs('<?= $dayKey ?>')">
                                    <label class="form-check-label" for="switch_<?= $dayKey ?>">
                                        <span class="switch-label"><?= $isActive ? 'Aktif' : 'Nonaktif' ?></span>
                                    </label>
                                </div>
                                <?php if (!$isActive): ?>
                                <input type="hidden" name="<?= $dayKey ?>" value="nonaktif">
                                <?php endif; ?>
                            </div>
                            
                            <div class="row day-inputs" style="display: <?= $isActive ? 'flex' : 'none' ?>;">
                                <div class="col-md-6 mb-3">
                                    <label for="jammasuk_<?= $dayKey ?>" class="form-label">
                                        Jam Masuk <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control time-picker" 
                                               id="jammasuk_<?= $dayKey ?>" 
                                               name="jammasuk<?= $dayKey ?>" 
                                               value="<?= htmlspecialchars($jamMasuk) ?>" 
                                               placeholder="00:00" 
                                               pattern="^([0-1][0-9]|2[0-3]):[0-5][0-9]$" 
                                               maxlength="5"
                                               <?= $isActive ? 'required' : '' ?>>
                                        <button type="button" class="btn btn-outline-secondary" title="Pilih Waktu">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- <small class="text-muted">Format: HH:MM (00:00 - 23:59)</small> -->
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="jampulang_<?= $dayKey ?>" class="form-label">
                                        Jam Pulang <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control time-picker" 
                                               id="jampulang_<?= $dayKey ?>" 
                                               name="jampulang<?= $dayKey ?>" 
                                               value="<?= htmlspecialchars($jamPulang) ?>" 
                                               placeholder="00:00" 
                                               pattern="^([0-1][0-9]|2[0-3]):[0-5][0-9]$" 
                                               maxlength="5"
                                               <?= $isActive ? 'required' : '' ?>>
                                        <button type="button" class="btn btn-outline-secondary" title="Pilih Waktu">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- <small class="text-muted">Format: HH:MM (00:00 - 23:59)</small> -->
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="/dashboard" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.day-setting {
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}
.day-setting.active {
    background-color:rgb(235, 243, 250);
    border-color:rgb(214, 223, 236) !important;
}
.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}
.switch-label {
    font-weight: 500;
    margin-left: 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle day inputs based on switch
    window.toggleDayInputs = function(dayKey) {
        const switchEl = document.getElementById('switch_' + dayKey);
        const daySetting = document.querySelector('.day-setting[data-day="' + dayKey + '"]');
        const inputsContainer = daySetting.querySelector('.day-inputs');
        const jamMasukInput = document.getElementById('jammasuk_' + dayKey);
        const jamPulangInput = document.getElementById('jampulang_' + dayKey);
        const switchLabel = switchEl.nextElementSibling.querySelector('.switch-label');
        const switchContainer = switchEl.closest('.d-flex');
        
        // Remove existing hidden input if any
        const existingHidden = switchContainer.querySelector('input[type="hidden"][name="' + dayKey + '"]');
        if (existingHidden) {
            existingHidden.remove();
        }
        
        if (switchEl.checked) {
            // Checkbox checked = aktif, no hidden input needed
            inputsContainer.style.display = 'flex';
            daySetting.classList.add('active');
            switchLabel.textContent = 'Aktif';
            if (jamMasukInput) jamMasukInput.required = true;
            if (jamPulangInput) jamPulangInput.required = true;
        } else {
            // Checkbox not checked = nonaktif, add hidden input
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = dayKey;
            hiddenInput.value = 'nonaktif';
            switchContainer.appendChild(hiddenInput);
            
            inputsContainer.style.display = 'none';
            daySetting.classList.remove('active');
            switchLabel.textContent = 'Nonaktif';
            if (jamMasukInput) {
                jamMasukInput.required = false;
                jamMasukInput.value = '';
            }
            if (jamPulangInput) {
                jamPulangInput.required = false;
                jamPulangInput.value = '';
            }
        }
    };
    
    // Initialize all switches
    document.querySelectorAll('.day-switch').forEach(function(switchEl) {
        const dayKey = switchEl.id.replace('switch_', '');
        toggleDayInputs(dayKey);
    });
    
    // Time input functions
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
        
        input.setCustomValidity('');
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        return true;
    }
    
    function formatTimeInput(input, validate = false) {
        if (!input) return;
        
        if (!input.value || input.value.trim() === '') {
            if (validate) {
                input.classList.remove('is-invalid', 'is-valid');
            }
            return;
        }
        
        let value = input.value.replace(/[^0-9:]/g, '');
        
        if (value.length > 0 && !value.includes(':')) {
            if (value.length === 2) {
                value = value + ':';
            } else if (value.length > 2) {
                value = value.substring(0, 2) + ':' + value.substring(2);
            }
        }
        
        if (value.length > 5) {
            value = value.substring(0, 5);
        }
        
        if (validate) {
            const parts = value.split(':');
            if (parts.length === 2) {
                let hours = parseInt(parts[0], 10) || 0;
                let minutes = parseInt(parts[1], 10) || 0;
                
                if (hours < 0) hours = 0;
                if (hours > 23) hours = 23;
                if (minutes < 0) minutes = 0;
                if (minutes > 59) minutes = 59;
                
                value = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
            }
            
            input.value = value;
            validateTimeInput(input);
        } else {
            input.value = value;
        }
    }
    
    function applyTimeMask(input) {
        if (!input) return;
        input.addEventListener('input', function(e) {
            formatTimeInput(this, false);
        });
        
        input.addEventListener('keydown', function(e) {
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }
    
    // Apply time mask to all time inputs
    document.querySelectorAll('.time-picker').forEach(function(input) {
        applyTimeMask(input);
        input.addEventListener('blur', function() {
            formatTimeInput(this, true);
        });
        
        input.addEventListener('dblclick', function() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            this.value = hours + ':' + minutes;
            formatTimeInput(this, true);
        });
    });
    
    // Form validation
    const form = document.getElementById('formSettingJamBelajar');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            document.querySelectorAll('.day-switch').forEach(function(switchEl) {
                if (switchEl.checked) {
                    const dayKey = switchEl.id.replace('switch_', '');
                    const jamMasukInput = document.getElementById('jammasuk_' + dayKey);
                    const jamPulangInput = document.getElementById('jampulang_' + dayKey);
                    
                    if (jamMasukInput && !validateTimeInput(jamMasukInput)) {
                        isValid = false;
                        if (isValid) jamMasukInput.focus();
                    }
                    
                    if (jamPulangInput && isValid && !validateTimeInput(jamPulangInput)) {
                        isValid = false;
                        jamPulangInput.focus();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

