<?php
/**
 * Service untuk membaca dan memproses data dari file Finger.xlsx
 */

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class FingerExcelService {
    private $excelFile;
    private $spreadsheet;
    private $worksheet;
    
    public function __construct($excelFile = null) {
        if ($excelFile === null) {
            $excelFile = __DIR__ . '/../Finger.xlsx';
        }
        $this->excelFile = $excelFile;
    }
    
    /**
     * Load file Excel
     */
    public function load() {
        if (!file_exists($this->excelFile)) {
            throw new Exception("File Excel tidak ditemukan: {$this->excelFile}");
        }
        
        $this->spreadsheet = IOFactory::load($this->excelFile);
        $this->worksheet = $this->spreadsheet->getActiveSheet();
        
        return $this;
    }
    
    /**
     * Baca informasi karyawan dari baris 1-10
     */
    public function getEmployeeInfo() {
        if (!$this->worksheet) {
            $this->load();
        }
        
        $info = [
            'fingerprint_id' => null,
            'kode_karyawan' => null,
            'jabatan' => null,
            'tanggal_gabung' => null,
            'nama' => null,
            'departemen' => null
        ];
        
        // Baca data dari baris 1-10
        // Format: Label di baris ganjil, Data di baris genap atau di baris yang sama
        
        // Row 1: Fingerprint ID (label)
        // Row 2: Kode Karyawan (label) 
        // Row 3-4: Data kode (mungkin duplikat)
        $row2Value = $this->getCellValue('A', 2);
        if (stripos($row2Value, 'Kode Karyawan') !== false) {
            $row3Value = $this->getCellValue('A', 3);
            if (is_numeric($row3Value)) {
                $info['kode_karyawan'] = (string)$row3Value;
            }
        }
        
        // Row 1: Fingerprint ID
        $row1Value = $this->getCellValue('A', 1);
        if (stripos($row1Value, 'Fingerprint ID') !== false) {
            // Cari data fingerprint ID (mungkin di row lain atau kolom lain)
            // Untuk sementara, gunakan kode karyawan sebagai fingerprint ID
            $info['fingerprint_id'] = $info['kode_karyawan'];
        }
        
        // Row 5: Jabatan Karyawan (label)
        // Row 6: Tanggal Gabung (label)
        // Row 7: Data tanggal (Excel serial number)
        $row6Value = $this->getCellValue('A', 6);
        if (stripos($row6Value, 'Tanggal Gabung') !== false) {
            $row7Value = $this->getCellValue('A', 7);
            if (is_numeric($row7Value)) {
                // Convert Excel date serial number to date
                $info['tanggal_gabung'] = $this->excelDateToDate($row7Value);
            }
        }
        
        // Row 5: Jabatan (cari di baris lain atau kolom lain)
        $row5Value = $this->getCellValue('A', 5);
        if (stripos($row5Value, 'Jabatan') !== false) {
            // Cari data jabatan di baris berikutnya atau kolom lain
            // Untuk sementara skip, karena strukturnya tidak jelas
        }
        
        // Row 8: Nama Karyawan (label + data)
        $row8Value = $this->getCellValue('A', 8);
        if (stripos($row8Value, 'Nama Karyawan') !== false) {
            // Extract nama dari string (setelah "Nama Karyawan")
            $parts = explode('Nama Karyawan', $row8Value);
            if (count($parts) > 1) {
                $info['nama'] = trim($parts[1]);
            } else {
                // Coba ambil dari baris berikutnya
                $row9Value = $this->getCellValue('A', 9);
                if (stripos($row9Value, 'Nama Departemen') === false) {
                    $info['nama'] = trim($row9Value);
                }
            }
        }
        
        // Row 9: Nama Departemen (label)
        // Row 10: Data departemen
        $row9Value = $this->getCellValue('A', 9);
        if (stripos($row9Value, 'Nama Departemen') !== false) {
            $row10Value = $this->getCellValue('A', 10);
            if (!empty($row10Value)) {
                $info['departemen'] = trim((string)$row10Value);
            }
        }
        
        return $info;
    }
    
    /**
     * Baca data log absensi (mulai dari baris 11)
     */
    public function getAttendanceLogs($startRow = 11) {
        if (!$this->worksheet) {
            $this->load();
        }
        
        $logs = [];
        $highestRow = $this->worksheet->getHighestRow();
        
        // Baca header di baris 11
        $headers = $this->getRowHeaders($startRow);
        
        // Baca data mulai dari baris 12
        for ($row = $startRow + 1; $row <= $highestRow; $row++) {
            $rowData = $this->getRowData($row);
            
            // Skip baris kosong
            if (empty(array_filter($rowData))) {
                continue;
            }
            
            // Map data berdasarkan header
            $log = [];
            foreach ($headers as $col => $header) {
                $value = isset($rowData[$col]) ? $rowData[$col] : null;
                
                // Process berdasarkan nama header
                switch (strtolower(trim($header))) {
                    case 'tanggal log':
                        // Convert Excel date serial number to date
                        if (is_numeric($value)) {
                            $log['tanggal_log'] = $this->excelDateToDate($value);
                        } else {
                            $log['tanggal_log'] = $value;
                        }
                        break;
                        
                    case 'nomor terminal':
                        $log['nomor_terminal'] = $value;
                        break;
                        
                    case 'terminal location':
                        $log['terminal_location'] = $value;
                        break;
                        
                    case 'jam log':
                        // Bisa berupa waktu (07:05) atau decimal (0.78541666666667)
                        if (is_numeric($value) && $value < 1) {
                            // Excel time serial number
                            $log['jam_log'] = $this->excelTimeToTime($value);
                        } else {
                            $log['jam_log'] = $value;
                        }
                        break;
                        
                    case 'fungsi tombol':
                        // 0 = Check In, 1 = Check Out
                        $log['fungsi_tombol'] = $value;
                        $log['tipe'] = ($value == 1) ? 'Check Out' : 'Check In';
                        break;
                        
                    case 'keterangan':
                        $log['keterangan'] = $value;
                        break;
                        
                    case 'cara attendan':
                        $log['cara_attendan'] = $value;
                        break;
                        
                    case 'tanggal edit':
                        if (is_numeric($value)) {
                            $log['tanggal_edit'] = $this->excelDateToDate($value);
                        } else {
                            $log['tanggal_edit'] = $value;
                        }
                        break;
                        
                    case 'nama user':
                        $log['nama_user'] = $value;
                        break;
                }
            }
            
            if (!empty($log)) {
                $logs[] = $log;
            }
        }
        
        return $logs;
    }
    
    /**
     * Baca semua data (employee info + attendance logs)
     * Mendukung multiple employees dalam satu file
     */
    public function getAllData() {
        if (!$this->worksheet) {
            $this->load();
        }
        
        $highestRow = $this->worksheet->getHighestRow();
        $employees = [];
        
        // Cari semua kemunculan "Fingerprint ID" untuk mendeteksi multiple employees
        $fingerprintRows = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $this->getCellValue('A', $row);
            if ($cellValue !== null && stripos((string)$cellValue, 'Fingerprint ID') !== false) {
                $fingerprintRows[] = $row;
            }
        }
        
        // Jika tidak ada "Fingerprint ID", gunakan method lama (backward compatibility)
        if (empty($fingerprintRows)) {
            return [
                'employees' => [
                    [
                        'employee' => $this->getEmployeeInfo(),
                        'attendance_logs' => $this->getAttendanceLogs()
                    ]
                ],
                'total_employees' => 1
            ];
        }
        
        // Process setiap employee
        foreach ($fingerprintRows as $index => $fingerprintRow) {
            // Tentukan start row untuk employee ini
            $startRow = $fingerprintRow;
            
            // Tentukan end row (baris sebelum "Fingerprint ID" berikutnya, atau akhir file)
            $endRow = isset($fingerprintRows[$index + 1]) 
                ? $fingerprintRows[$index + 1] - 1 
                : $highestRow;
            
            // Baca employee info dari baris startRow sampai startRow+9
            $employeeInfo = $this->getEmployeeInfoFromRow($startRow);
            
            // Cari header log (biasanya setelah info employee, cari "Tanggal Log")
            // Perluas pencarian sampai endRow untuk memastikan tidak melewatkan header
            $headerRow = null;
            for ($row = $startRow + 1; $row <= $endRow; $row++) {
                $rowData = $this->getRowData($row);
                $foundHeaderKeywords = 0;
                
                // Cek semua kolom untuk keyword header
                foreach ($rowData as $col => $value) {
                    if ($value !== null) {
                        $valueStr = strtolower(trim((string)$value));
                        // Cari berbagai variasi header
                        if (stripos($valueStr, 'tanggal log') !== false || 
                            (stripos($valueStr, 'tanggal') !== false && stripos($valueStr, 'log') !== false)) {
                            $foundHeaderKeywords++;
                        }
                        if (stripos($valueStr, 'nomor terminal') !== false || stripos($valueStr, 'terminal') !== false) {
                            $foundHeaderKeywords++;
                        }
                        if (stripos($valueStr, 'jam log') !== false || stripos($valueStr, 'jam') !== false) {
                            $foundHeaderKeywords++;
                        }
                        if (stripos($valueStr, 'keterangan') !== false) {
                            $foundHeaderKeywords++;
                        }
                        if (stripos($valueStr, 'fungsi tombol') !== false) {
                            $foundHeaderKeywords++;
                        }
                    }
                }
                
                // Jika menemukan minimal 3 keyword header, ini adalah header row
                if ($foundHeaderKeywords >= 3) {
                    $headerRow = $row;
                    break;
                }
                
                // Fallback: jika sudah melewati 15 baris, coba pattern lebih longgar
                if ($row > $startRow + 15 && !$headerRow && $foundHeaderKeywords >= 2) {
                    // Cek apakah baris ini memiliki beberapa kolom terisi (kemungkinan header)
                    $filledCells = 0;
                    foreach ($rowData as $v) {
                        if ($v !== null && trim((string)$v) !== '') {
                            $filledCells++;
                        }
                    }
                    if ($filledCells >= 5) {
                        $headerRow = $row;
                        break;
                    }
                }
            }
            
            // Jika tidak menemukan header, coba gunakan baris setelah info employee (baris 11 dari startRow)
            if (!$headerRow) {
                // Default: header biasanya di baris startRow + 10 (setelah info employee)
                $potentialHeaderRow = $startRow + 10;
                if ($potentialHeaderRow <= $endRow) {
                    $rowData = $this->getRowData($potentialHeaderRow);
                    // Cek apakah baris ini memiliki beberapa kolom yang terisi (kemungkinan header)
                    $filledCells = 0;
                    foreach ($rowData as $value) {
                        if ($value !== null && trim((string)$value) !== '') {
                            $filledCells++;
                        }
                    }
                    if ($filledCells >= 3) {
                        $headerRow = $potentialHeaderRow;
                    }
                }
            }
            
            // Jika masih tidak menemukan header, log warning tapi tetap proses
            if (!$headerRow) {
                error_log("Warning: Header log tidak ditemukan untuk employee di baris {$startRow}. Mencoba default header.");
                // Gunakan baris default (startRow + 10) sebagai header
                $headerRow = $startRow + 10;
            }
            
            // Baca logs mulai dari baris setelah header sampai sebelum employee berikutnya
            $logs = $this->getAttendanceLogsFromRow($headerRow, $endRow);
            
            $employees[] = [
                'employee' => $employeeInfo,
                'attendance_logs' => $logs,
                'start_row' => $startRow,
                'end_row' => $endRow,
                'header_row' => $headerRow
            ];
        }
        
        return [
            'employees' => $employees,
            'total_employees' => count($employees)
        ];
    }
    
    /**
     * Baca informasi karyawan dari baris tertentu (untuk multiple employees)
     */
    private function getEmployeeInfoFromRow($startRow) {
        $info = [
            'fingerprint_id' => null,
            'kode_karyawan' => null,
            'jabatan' => null,
            'tanggal_gabung' => null,
            'nama' => null,
            'departemen' => null
        ];
        
        // Baca data dari baris startRow sampai startRow+9
        // Row startRow: Fingerprint ID (label)
        // Row startRow+1: Kode Karyawan (label) 
        // Row startRow+2: Data kode
        $row2Value = $this->getCellValue('A', $startRow + 1);
        if ($row2Value !== null && stripos((string)$row2Value, 'Kode Karyawan') !== false) {
            $row3Value = $this->getCellValue('A', $startRow + 2);
            if ($row3Value !== null && is_numeric($row3Value)) {
                $info['kode_karyawan'] = (string)$row3Value;
            }
        }
        
        // Row startRow: Fingerprint ID
        $row1Value = $this->getCellValue('A', $startRow);
        if ($row1Value !== null && stripos((string)$row1Value, 'Fingerprint ID') !== false) {
            $info['fingerprint_id'] = $info['kode_karyawan'];
        }
        
        // Row startRow+4: Jabatan Karyawan (label)
        // Row startRow+5: Tanggal Gabung (label)
        // Row startRow+6: Data tanggal
        $row6Value = $this->getCellValue('A', $startRow + 5);
        if ($row6Value !== null && stripos((string)$row6Value, 'Tanggal Gabung') !== false) {
            $row7Value = $this->getCellValue('A', $startRow + 6);
            if ($row7Value !== null && is_numeric($row7Value)) {
                $info['tanggal_gabung'] = $this->excelDateToDate($row7Value);
            }
        }
        
        // Row startRow+7: Nama Karyawan (label + data)
        $row8Value = $this->getCellValue('A', $startRow + 7);
        if ($row8Value !== null && stripos((string)$row8Value, 'Nama Karyawan') !== false) {
            $parts = explode('Nama Karyawan', (string)$row8Value);
            if (count($parts) > 1) {
                $info['nama'] = trim($parts[1]);
            } else {
                $row9Value = $this->getCellValue('A', $startRow + 8);
                if ($row9Value !== null && stripos((string)$row9Value, 'Nama Departemen') === false) {
                    $info['nama'] = trim((string)$row9Value);
                }
            }
        }
        
        // Row startRow+8: Nama Departemen (label)
        // Row startRow+9: Data departemen
        $row9Value = $this->getCellValue('A', $startRow + 8);
        if ($row9Value !== null && stripos((string)$row9Value, 'Nama Departemen') !== false) {
            $row10Value = $this->getCellValue('A', $startRow + 9);
            if ($row10Value !== null && !empty($row10Value)) {
                $info['departemen'] = trim((string)$row10Value);
            }
        }
        
        return $info;
    }
    
    /**
     * Baca data log absensi dari baris tertentu sampai baris akhir
     */
    private function getAttendanceLogsFromRow($headerRow, $endRow) {
        $logs = [];
        
        // Baca header di baris headerRow
        $headers = $this->getRowHeaders($headerRow);
        
        // Baca data mulai dari baris setelah header sampai endRow
        for ($row = $headerRow + 1; $row <= $endRow; $row++) {
            $rowData = $this->getRowData($row);
            
            // Cek jika baris ini adalah awal employee baru (ada "Fingerprint ID")
            $cellA = $this->getCellValue('A', $row);
            if ($cellA !== null && stripos((string)$cellA, 'Fingerprint ID') !== false) {
                // Ini adalah awal employee baru, stop membaca logs
                break;
            }
            
            // Skip baris kosong (setelah cek Fingerprint ID)
            if (empty(array_filter($rowData))) {
                continue;
            }
            
            // Skip jika baris ini adalah header lagi (duplikat header)
            $cellAStr = $cellA !== null ? strtolower((string)$cellA) : '';
            if (stripos($cellAStr, 'tanggal log') !== false || 
                stripos($cellAStr, 'nomor terminal') !== false) {
                continue;
            }
            
            // Map data berdasarkan header
            $log = [];
            foreach ($headers as $col => $header) {
                $value = isset($rowData[$col]) ? $rowData[$col] : null;
                
                // Process berdasarkan nama header
                switch (strtolower(trim($header))) {
                    case 'tanggal log':
                        if (is_numeric($value)) {
                            $log['tanggal_log'] = $this->excelDateToDate($value);
                        } else {
                            $log['tanggal_log'] = $value;
                        }
                        break;
                        
                    case 'nomor terminal':
                        $log['nomor_terminal'] = $value;
                        break;
                        
                    case 'terminal location':
                        $log['terminal_location'] = $value;
                        break;
                        
                    case 'jam log':
                        if (is_numeric($value) && $value < 1) {
                            $log['jam_log'] = $this->excelTimeToTime($value);
                        } else {
                            $log['jam_log'] = $value;
                        }
                        break;
                        
                    case 'fungsi tombol':
                        $log['fungsi_tombol'] = $value;
                        $log['tipe'] = ($value == 1) ? 'Check Out' : 'Check In';
                        break;
                        
                    case 'keterangan':
                        $log['keterangan'] = $value;
                        break;
                        
                    case 'cara attendan':
                        $log['cara_attendan'] = $value;
                        break;
                        
                    case 'tanggal edit':
                        if (is_numeric($value)) {
                            $log['tanggal_edit'] = $this->excelDateToDate($value);
                        } else {
                            $log['tanggal_edit'] = $value;
                        }
                        break;
                        
                    case 'nama user':
                        $log['nama_user'] = $value;
                        break;
                }
            }
            
            if (!empty($log)) {
                $logs[] = $log;
            }
        }
        
        return $logs;
    }
    
    /**
     * Helper: Get cell value
     */
    private function getCellValue($col, $row) {
        $cell = $this->worksheet->getCell($col . $row);
        return $cell->getValue();
    }
    
    /**
     * Helper: Get row data as array
     */
    private function getRowData($row) {
        $data = [];
        $highestColumn = $this->worksheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
        
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $cell = $this->worksheet->getCell($colLetter . $row);
            $value = $cell->getValue();
            $data[$colLetter] = $value !== null ? $value : null;
        }
        
        return $data;
    }
    
    /**
     * Helper: Get row headers
     */
    private function getRowHeaders($row) {
        $headers = [];
        $rowData = $this->getRowData($row);
        
        foreach ($rowData as $col => $value) {
            if ($value !== null && trim((string)$value) !== '') {
                $headers[$col] = trim((string)$value);
            }
        }
        
        return $headers;
    }
    
    /**
     * Convert Excel date serial number to PHP date string
     */
    private function excelDateToDate($excelDate) {
        if (!is_numeric($excelDate)) {
            return $excelDate;
        }
        
        // Excel epoch starts from 1900-01-01 (but Excel incorrectly treats 1900 as leap year)
        // PHP's DateTime can handle this
        $baseDate = new DateTime('1899-12-30');
        $days = floor($excelDate);
        $baseDate->modify("+{$days} days");
        
        return $baseDate->format('Y-m-d');
    }
    
    /**
     * Convert Excel time serial number to time string (HH:MM:SS)
     */
    private function excelTimeToTime($excelTime) {
        if (!is_numeric($excelTime)) {
            return $excelTime;
        }
        
        // Excel time is fraction of day (0.0 = 00:00:00, 0.5 = 12:00:00)
        $seconds = round($excelTime * 86400); // 86400 seconds in a day
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}

