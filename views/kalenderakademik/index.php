<?php
$title = 'Kalender Akademik';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}
require __DIR__ . '/../layouts/header.php';

$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$currentYear = date('Y');
$years = [];
for ($i = $currentYear - 2; $i <= $currentYear + 5; $i++) {
    $years[] = $i;
}
?>

<div class="kalender-akademik-wrapper">
    <div class="container-fluid">
        <div class="breadcrumb-item">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Kalender Akademik</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Kalender Akademik</h4>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Month/Year Selector -->
                    <form method="GET" action="/kalenderakademik" class="mb-4">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label for="month" class="form-label">Bulan</label>
                                <select name="month" id="month" class="form-select" onchange="this.form.submit();">
                                    <?php foreach ($monthNames as $num => $name): ?>
                                    <option value="<?= $num ?>" <?= $selectedMonth == $num ? 'selected' : '' ?>>
                                        <?= $name ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="year" class="form-label">Tahun</label>
                                <select name="year" id="year" class="form-select" onchange="this.form.submit();">
                                    <?php foreach ($years as $y): ?>
                                    <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Calendar Grid -->
                    <form method="POST" action="/kalenderakademik?year=<?= $selectedYear ?>&month=<?= $selectedMonth ?>" id="formKalenderAkademik">
                        <input type="hidden" name="year" value="<?= $selectedYear ?>">
                        <input type="hidden" name="month" value="<?= $selectedMonth ?>">
                        
                        <div class="row g-3 calendar-date-grid">
                            <?php 
                            // Display all dates in the month, including Sunday/Minggu
                            foreach ($dates as $dateInfo): 
                            ?>
                            <?php 
                            $date = $dateInfo['date'];
                            $isActive = $dateInfo['isActive'];
                            $isHoliday = $dateInfo['isHoliday'];
                            $holidayName = $dateInfo['holidayName'] ?? null;
                            $isDayActiveInSetting = $dateInfo['isDayActiveInSetting'];
                            ?>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 calendar-date-item">
                                <div class="date-setting-card p-3 border rounded <?= $isActive ? 'active' : '' ?> <?= $isHoliday ? 'holiday' : '' ?>" data-date="<?= $date ?>">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            <strong><?= $dateInfo['dayNameLabel'] ?></strong>
                                            <span class="ms-2 text-muted"><?= sprintf('%02d', $dateInfo['day']) ?></span>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input date-switch" 
                                                   type="checkbox" 
                                                   id="switch_<?= $date ?>" 
                                                   name="active_<?= $date ?>" 
                                                   value="1"
                                                   <?= $isActive ? 'checked' : '' ?>
                                                   onchange="toggleDateInputs('<?= $date ?>')">
                                            <label class="form-check-label" for="switch_<?= $date ?>">
                                                <span class="switch-label"><?= $isActive ? 'Masuk' : 'Libur' ?></span>
                                            </label>
                                        </div>
                                        <?php if (!$isActive): ?>
                                        <input type="hidden" name="active_<?= $date ?>" value="0">
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($isHoliday): ?>
                                    <div class="alert alert-warning py-1 px-2 mb-2">
                                        <small><strong><?= htmlspecialchars($holidayName ?? 'Hari Libur') ?></strong></small>
                                    </div>
                                    <?php elseif (!$isDayActiveInSetting): ?>
                                    <div class="alert alert-info py-1 px-2 mb-2 libur-reguler-alert" style="display: <?= $isActive ? 'none' : 'block' ?>;">
                                        <small><strong>Libur Reguler</strong></small>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="date-inputs" style="display: <?= $isActive ? 'block' : 'none' ?>;">
                                        <div class="mb-2 d-flex align-items-center gap-2">
                                            <label for="jammasuk_<?= $date ?>" class="form-label small mb-0" style="min-width: 50px;">
                                                Masuk
                                            </label>
                                            <div class="input-group input-group-sm flex-grow-1">
                                                <input type="text" 
                                                       class="form-control time-picker" 
                                                       id="jammasuk_<?= $date ?>" 
                                                       name="jammasuk_<?= $date ?>" 
                                                       value="<?= htmlspecialchars($dateInfo['jamMasuk']) ?>" 
                                                       placeholder="00:00" 
                                                       pattern="^([0-1][0-9]|2[0-3]):[0-5][0-9]$" 
                                                       maxlength="5"
                                                       <?= $isActive ? 'required' : '' ?>>
                                                <button type="button" class="btn btn-outline-secondary" title="Pilih Waktu">
                                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-2 d-flex align-items-center gap-2">
                                            <label for="jamkeluar_<?= $date ?>" class="form-label small mb-0" style="min-width: 50px;">
                                                Pulang
                                            </label>
                                            <div class="input-group input-group-sm flex-grow-1">
                                                <input type="text" 
                                                       class="form-control time-picker" 
                                                       id="jamkeluar_<?= $date ?>" 
                                                       name="jamkeluar_<?= $date ?>" 
                                                       value="<?= htmlspecialchars($dateInfo['jamKeluar']) ?>" 
                                                       placeholder="00:00" 
                                                       pattern="^([0-1][0-9]|2[0-3]):[0-5][0-9]$" 
                                                       maxlength="5"
                                                       <?= $isActive ? 'required' : '' ?>>
                                                <button type="button" class="btn btn-outline-secondary" title="Pilih Waktu">
                                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
                                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label for="keterangan_<?= $date ?>" class="form-label small">Keterangan</label>
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   id="keterangan_<?= $date ?>" 
                                                   name="keterangan_<?= $date ?>" 
                                                   value="<?= htmlspecialchars($dateInfo['keterangan']) ?>" 
                                                   placeholder="Keterangan (opsional)" 
                                                   maxlength="255">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Simpan Kalender Akademik</button>
                            <a href="/dashboard" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<style>
/* Kalender Akademik Wrapper - Full width dengan padding di desktop */
.kalender-akademik-wrapper {
    width: 100%;
    padding-left: 1rem;
    padding-right: 1rem;
}

@media (min-width: 992px) {
    .kalender-akademik-wrapper {
        padding: 1rem;
    }
}

/* Calendar Date Grid - 7 kolom per baris di desktop */
@media (min-width: 992px) {
    .calendar-date-grid {
        display: flex;
        flex-wrap: wrap;
    }
    
    .calendar-date-grid .calendar-date-item {
        flex: 0 0 14.285714%; /* 7 kolom per baris (100% / 7 = 14.285714%) */
        max-width: 14.285714%;
        width: 14.285714%;
    }
}

.date-setting-card {
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    min-height: 200px;
    height: 100%;
}
.date-setting-card.active {
    background-color: rgb(235, 243, 250);
    border-color: rgb(214, 223, 236) !important;
}
.date-setting-card.holiday {
    background-color: #fff3cd;
    border-color: #ffc107 !important;
}
.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

/* Align switch and label vertically centered */
.date-setting-card .form-check.form-switch {
    display: flex;
    align-items: center;
    margin-bottom: 0;
}

.date-setting-card .form-check-input {
    margin-top: 0;
    margin-bottom: 0;
    vertical-align: middle;
}

.date-setting-card .form-check-label {
    display: flex;
    align-items: center;
    margin-bottom: 0;
    margin-left: 0.5rem;
}

.switch-label {
    font-weight: 500;
    font-size: 0.875rem;
    line-height: 1;
}
.date-inputs {
    margin-top: 0.5rem;
}
.input-group-sm .form-control {
    font-size: 0.875rem;
}
.input-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle date inputs based on switch
    window.toggleDateInputs = function(date) {
        const switchEl = document.getElementById('switch_' + date);
        const dateCard = document.querySelector('.date-setting-card[data-date="' + date + '"]');
        const inputsContainer = dateCard.querySelector('.date-inputs');
        const jamMasukInput = document.getElementById('jammasuk_' + date);
        const jamKeluarInput = document.getElementById('jamkeluar_' + date);
        const switchLabel = switchEl.nextElementSibling.querySelector('.switch-label');
        const hiddenInput = dateCard.querySelector('input[type="hidden"][name="active_' + date + '"]');
        
        // Get libur reguler alert
        const liburRegulerAlert = dateCard.querySelector('.libur-reguler-alert');
        
        if (switchEl.checked) {
            // Remove hidden input if exists
            if (hiddenInput) hiddenInput.remove();
            inputsContainer.style.display = 'block';
            dateCard.classList.add('active');
            switchLabel.textContent = 'Masuk';
            // Hide libur reguler alert when switch is ON
            if (liburRegulerAlert) liburRegulerAlert.style.display = 'none';
            if (jamMasukInput) jamMasukInput.required = true;
            if (jamKeluarInput) jamKeluarInput.required = true;
        } else {
            // Add hidden input for nonaktif
            if (!hiddenInput) {
                const newHidden = document.createElement('input');
                newHidden.type = 'hidden';
                newHidden.name = 'active_' + date;
                newHidden.value = '0';
                switchEl.parentNode.parentNode.appendChild(newHidden);
            }
            inputsContainer.style.display = 'none';
            dateCard.classList.remove('active');
            switchLabel.textContent = 'Libur';
            // Show libur reguler alert when switch is OFF
            if (liburRegulerAlert) liburRegulerAlert.style.display = 'block';
            if (jamMasukInput) {
                jamMasukInput.required = false;
                jamMasukInput.value = '';
            }
            if (jamKeluarInput) {
                jamKeluarInput.required = false;
                jamKeluarInput.value = '';
            }
        }
    };
    
    // Initialize all switches
    document.querySelectorAll('.date-switch').forEach(function(switchEl) {
        const date = switchEl.id.replace('switch_', '');
        toggleDateInputs(date);
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
    const form = document.getElementById('formKalenderAkademik');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            document.querySelectorAll('.date-switch:checked').forEach(function(switchEl) {
                const date = switchEl.id.replace('switch_', '');
                const jamMasukInput = document.getElementById('jammasuk_' + date);
                const jamKeluarInput = document.getElementById('jamkeluar_' + date);
                
                if (jamMasukInput && !validateTimeInput(jamMasukInput)) {
                    isValid = false;
                    if (isValid) jamMasukInput.focus();
                }
                
                if (jamKeluarInput && isValid && !validateTimeInput(jamKeluarInput)) {
                    isValid = false;
                    jamKeluarInput.focus();
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

