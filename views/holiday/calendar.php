<!-- Calendar View -->
<div class="mb-3">
    <form method="GET" action="/holiday" class="row g-2 align-items-end">
        <div class="col-12 col-md-2">
            <label for="year" class="form-label">Tahun</label>
            <select name="year" id="year" class="form-select" onchange="this.form.submit()" style="min-width: 150px;">
                <?php 
                $currentYear = (int)date('Y');
                for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++): 
                ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label for="month" class="form-label">Bulan</label>
            <select name="month" id="month" class="form-select" onchange="this.form.submit()" style="min-width: 200px;">
                <?php 
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                foreach ($months as $m => $monthName): 
                ?>
                    <option value="<?= $m ?>" <?= $month == $m ? 'selected' : '' ?>><?= $monthName ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="view" value="calendar">
    </form>
</div>

<div id="calendar-container">
    <?php
    // Ensure holidays is an array
    if (!is_array($holidays)) {
        $holidays = [];
    }
    
    // Generate calendar
    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $daysInMonth = date('t', $firstDay);
    $dayOfWeek = date('w', $firstDay); // 0 = Sunday, 6 = Saturday
    $dayOfWeek = $dayOfWeek == 0 ? 7 : $dayOfWeek; // Make Monday = 1
    
    $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    ?>
    
    <div class="table-responsive">
        <table class="table table-bordered calendar-table">
            <thead>
                <tr>
                    <?php foreach ($dayNames as $dayName): ?>
                        <th class="text-center bg-light"><?= $dayName ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $currentDay = 1;
                $weeks = ceil(($daysInMonth + $dayOfWeek - 1) / 7);
                
                for ($week = 0; $week < $weeks; $week++):
                ?>
                    <tr>
                        <?php
                        for ($day = 1; $day <= 7; $day++):
                            if ($week == 0 && $day < $dayOfWeek) {
                                // Empty cells before first day
                                echo '<td class="calendar-day empty"></td>';
                            } elseif ($currentDay <= $daysInMonth) {
                                // Valid day of the month
                                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $currentDay);
                                $dateHolidays = !empty($holidays) && isset($holidays[$dateStr]) ? $holidays[$dateStr] : [];
                                $isToday = ($year == (int)date('Y') && $month == (int)date('m') && $currentDay == (int)date('d'));
                                
                                $cellClass = 'calendar-day';
                                if ($isToday) $cellClass .= ' today';
                                if (!empty($dateHolidays)) $cellClass .= ' has-holiday';
                                
                                echo '<td class="' . $cellClass . '">';
                                echo '<div class="calendar-day-number">' . $currentDay . '</div>';
                                
                                if (!empty($dateHolidays)) {
                                    echo '<div class="calendar-holidays-list">';
                                    foreach ($dateHolidays as $holiday) {
                                        $holidayType = $holiday['is_national'] ? 'national' : 'local';
                                        $recurringIcon = $holiday['is_recurring'] ? ' 🔄' : '';
                                        echo '<div class="calendar-holiday-item ' . $holidayType . '" data-holiday-id="' . $holiday['id'] . '">';
                                        echo '<span class="holiday-name">' . htmlspecialchars($holiday['name']) . $recurringIcon . '</span>';
                                        echo '<div class="holiday-actions">';
                                        echo '<a href="/holiday/edit/' . $holiday['id'] . '" class="btn-action btn-edit" title="Edit">' . icon('pen-to-square', '', 10) . '</a>';
                                        echo '<a href="/holiday/delete/' . $holiday['id'] . '" class="btn-action btn-delete" onclick="event.preventDefault(); confirmDelete(\'Apakah Anda yakin ingin menghapus hari libur ini?\', this.href); return false;" title="Hapus">' . icon('trash-can', '', 10) . '</a>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                } else {
                                    echo '<div class="calendar-actions-empty">';
                                    echo '<a href="/holiday/create?date=' . $dateStr . '" class="btn-add-holiday" title="Tambah Hari Libur">' . icon('plus', '', 12) . '</a>';
                                    echo '</div>';
                                }
                                
                                echo '</td>';
                                $currentDay++;
                            } else {
                                // Empty cells after last day
                                echo '<td class="calendar-day empty"></td>';
                            }
                        endfor;
                        ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.calendar-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: separate;
    border-spacing: 0;
}
.calendar-table thead th {
    background-color: #f8f9fa;
    color: #000000;
    font-weight: 600;
    padding: 10px 5px;
    text-align: center;
    border: 1px solid #dee2e6;
}
.calendar-day {
    width: 14.28%;
    min-height: 140px;
    vertical-align: top;
    padding: 8px 5px;
    position: relative;
    border: 1px solid #e0e0e0;
    background-color: #ffffff;
    transition: background-color 0.2s;
}
.calendar-day:hover {
    background-color: #f8f9fa;
}
.calendar-day.empty {
    background-color: #f5f5f5;
    color: #999;
}
.calendar-day.today {
    background-color: #e3f2fd;
    border: 2px solid #0d6efd;
}
.calendar-day.has-holiday {
    background-color: #fff9e6;
}
.calendar-day-number {
    font-weight: 700;
    font-size: 15px;
    margin-bottom: 8px;
    color: #333;
    line-height: 1.2;
}
.calendar-holidays-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
    max-height: 100px;
    overflow-y: auto;
    margin-bottom: 5px;
}
.calendar-holiday-item {
    font-size: 10px;
    padding: 4px 6px;
    border-radius: 3px;
    background-color: #fff;
    border-left: 3px solid #ffc107;
    word-wrap: break-word;
    line-height: 1.3;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 4px;
    transition: background-color 0.2s;
}
.calendar-holiday-item:hover {
    background-color: #f8f9fa;
}
.calendar-holiday-item.national {
    border-left-color: #dc3545;
    background-color: #fff5f5;
}
.calendar-holiday-item.local {
    border-left-color: #ffc107;
    background-color: #fffbf0;
}
.holiday-name {
    flex: 1;
    color: #333;
    font-weight: 500;
    display: block;
}
.holiday-actions {
    display: flex;
    gap: 2px;
    flex-shrink: 0;
}
.btn-action {
    padding: 2px 4px;
    border-radius: 3px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    opacity: 0.7;
    transition: opacity 0.2s;
}
.btn-action:hover {
    opacity: 1;
}
.btn-edit {
    color: #ffc107;
}
.btn-edit:hover {
    background-color: #fff3cd;
}
.btn-delete {
    color: #dc3545;
}
.btn-delete:hover {
    background-color: #f8d7da;
}
.calendar-actions-empty {
    position: absolute;
    bottom: 5px;
    right: 5px;
}
.btn-add-holiday {
    padding: 4px 6px;
    background-color: #0d6efd;
    color: white;
    border-radius: 4px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    opacity: 0.6;
    transition: opacity 0.2s;
}
.btn-add-holiday:hover {
    opacity: 1;
    color: white;
    background-color: #0a58ca;
}
@media (max-width: 768px) {
    .calendar-day {
        min-height: 100px;
        padding: 5px 3px;
    }
    .calendar-day-number {
        font-size: 13px;
    }
    .calendar-holiday-item {
        font-size: 9px;
        padding: 3px 4px;
    }
    .calendar-holidays-list {
        max-height: 70px;
    }
}
</style>

