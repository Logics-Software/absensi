<?php
class KalenderAkademikController extends Controller {
    public function index() {
        Auth::requireRole(['admin']);
        
        $kalenderModel = new KalenderAkademik();
        $settingJamBelajarModel = new SettingJamBelajar();
        $holidayModel = new Holiday();
        
        // Get selected month and year
        $selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        $selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
        
        // Validate month and year
        if ($selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = date('m');
        }
        if ($selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = date('Y');
        }
        
        // Get setting jam belajar
        $settingJamBelajar = $settingJamBelajarModel->get();
        
        // Get existing kalender akademik for the month
        $existingKalender = $kalenderModel->getByMonth($selectedYear, $selectedMonth);
        $kalenderMap = [];
        foreach ($existingKalender as $kal) {
            $kalenderMap[$kal['tanggal']] = $kal;
        }
        
        // Get holidays for the month (returns array with date as key and holiday_name as value)
        $holidays = $kalenderModel->getHolidaysByMonth($selectedYear, $selectedMonth);
        
        // Generate all dates in the month (including Sunday/Minggu)
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
        $dates = [];
        $dayNames = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']; // 0 = Minggu, 1 = Senin, etc.
        
        // Loop through all days in the month - ALL days are displayed including Sunday/Minggu
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $day);
            $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday/Minggu, 1 = Monday/Senin, etc.
            $dayName = $dayNames[$dayOfWeek]; // Get day name (minggu, senin, etc.)
            
            // Check if holiday (override to nonaktif)
            $isHoliday = isset($holidays[$date]);
            $holidayName = $isHoliday ? $holidays[$date] : null;
            
            // Get default from setting jam belajar
            $isDayActive = false;
            $defaultJamMasuk = null;
            $defaultJamKeluar = null;
            
            if ($settingJamBelajar && isset($settingJamBelajar[$dayName]) && $settingJamBelajar[$dayName] === 'aktif') {
                $isDayActive = true;
                $defaultJamMasuk = $settingJamBelajar['jammasuk' . $dayName] ?? null;
                $defaultJamKeluar = $settingJamBelajar['jampulang' . $dayName] ?? null;
            }
            
            // Get existing data
            $existing = $kalenderMap[$date] ?? null;
            
            // Determine if active: existing data takes precedence, then holiday (nonaktif), then setting jam belajar
            $isActive = false;
            $jamMasuk = '';
            $jamKeluar = '';
            
            if ($existing && $existing['jammasuk'] !== null && $existing['jamkeluar'] !== null) {
                // Existing data - active
                $isActive = true;
                $jamMasuk = $existing['jammasuk'] ? date('H:i', strtotime($existing['jammasuk'])) : '';
                $jamKeluar = $existing['jamkeluar'] ? date('H:i', strtotime($existing['jamkeluar'])) : '';
            } elseif (!$isHoliday && $isDayActive) {
                // Not holiday and day is active in setting - default active
                $isActive = true;
                $jamMasuk = $defaultJamMasuk ?? '';
                $jamKeluar = $defaultJamKeluar ?? '';
            }
            // Otherwise: nonaktif (holiday or day not active in setting)
            
            $dates[] = [
                'date' => $date,
                'day' => $day,
                'dayName' => $dayName,
                'dayNameLabel' => ucfirst($dayName),
                'isActive' => $isActive,
                'jamMasuk' => $jamMasuk,
                'jamKeluar' => $jamKeluar,
                'keterangan' => $existing ? ($existing['keterangan'] ?? '') : '',
                'isHoliday' => $isHoliday,
                'holidayName' => $holidayName,
                'isDayActiveInSetting' => $isDayActive
            ];
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $savedCount = 0;
            $errors = [];
            
            // Process each date
            foreach ($dates as $dateInfo) {
                $date = $dateInfo['date'];
                $isActive = isset($_POST['active_' . $date]) && $_POST['active_' . $date] === '1';
                
                if ($isActive) {
                    $jamMasuk = trim($_POST['jammasuk_' . $date] ?? '');
                    $jamKeluar = trim($_POST['jamkeluar_' . $date] ?? '');
                    
                    // Validate time format
                    $timePattern = '/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/';
                    
                    if (empty($jamMasuk)) {
                        $errors[] = "Jam masuk untuk tanggal " . date('d/m/Y', strtotime($date)) . " wajib diisi";
                        continue;
                    } elseif (!preg_match($timePattern, $jamMasuk)) {
                        $errors[] = "Format jam masuk untuk tanggal " . date('d/m/Y', strtotime($date)) . " tidak valid";
                        continue;
                    }
                    
                    if (empty($jamKeluar)) {
                        $errors[] = "Jam keluar untuk tanggal " . date('d/m/Y', strtotime($date)) . " wajib diisi";
                        continue;
                    } elseif (!preg_match($timePattern, $jamKeluar)) {
                        $errors[] = "Format jam keluar untuk tanggal " . date('d/m/Y', strtotime($date)) . " tidak valid";
                        continue;
                    }
                    
                    // Convert HH:MM to HH:MM:SS for database
                    if (strlen($jamMasuk) == 5) {
                        $jamMasuk .= ':00';
                    }
                    if (strlen($jamKeluar) == 5) {
                        $jamKeluar .= ':00';
                    }
                    
                    $data = [
                        'tanggal' => $date,
                        'jammasuk' => $jamMasuk,
                        'jamkeluar' => $jamKeluar,
                        'keterangan' => trim($_POST['keterangan_' . $date] ?? '')
                    ];
                    
                    if ($kalenderModel->save($data)) {
                        $savedCount++;
                    }
                } else {
                    // Delete if exists (nonaktif)
                    $kalenderModel->delete($date);
                }
            }
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    Message::error($error);
                }
            } else {
                Message::success("Kalender Akademik untuk " . date('F Y', strtotime($selectedYear . '-' . $selectedMonth . '-01')) . " berhasil disimpan");
            }
            
            $this->redirect('/kalenderakademik?year=' . $selectedYear . '&month=' . $selectedMonth);
        }
        
        $data = [
            'dates' => $dates,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'monthName' => date('F', strtotime($selectedYear . '-' . $selectedMonth . '-01')),
            'settingJamBelajar' => $settingJamBelajar
        ];
        
        $this->view('kalenderakademik/index', $data);
    }
}

