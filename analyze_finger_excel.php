<?php
/**
 * Script untuk menganalisis struktur file Finger.xlsx secara detail
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/index.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$excelFile = __DIR__ . '/Finger.xlsx';

echo "=== Analisis File Finger.xlsx ===\n\n";

try {
    $spreadsheet = IOFactory::load($excelFile);
    $worksheet = $spreadsheet->getActiveSheet();
    
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
    
    echo "Total Rows: {$highestRow}\n";
    echo "Total Columns: {$highestColumn} ({$highestColumnIndex})\n\n";
    
    // Baca semua data dengan detail
    echo "=== Data Lengkap (Baris 1-30) ===\n";
    $maxDisplayRows = min(30, $highestRow);
    
    for ($row = 1; $row <= $maxDisplayRows; $row++) {
        echo "\n--- Row {$row} ---\n";
        $rowData = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
            $cellValue = $worksheet->getCell($cellAddress)->getValue();
            $cellType = $worksheet->getCell($cellAddress)->getDataType();
            
            if ($cellValue !== null && $cellValue !== '') {
                $rowData[] = [
                    'col' => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col),
                    'value' => $cellValue,
                    'type' => $cellType
                ];
            }
        }
        
        if (!empty($rowData)) {
            foreach ($rowData as $cell) {
                echo "  {$cell['col']}: [{$cell['type']}] " . substr((string)$cell['value'], 0, 100) . "\n";
            }
        } else {
            echo "  (empty)\n";
        }
    }
    
    // Cari pola data (mungkin ada header di baris tertentu)
    echo "\n\n=== Mencari Pola Header ===\n";
    for ($row = 1; $row <= min(20, $highestRow); $row++) {
        $rowData = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
            $cellValue = $worksheet->getCell($cellAddress)->getValue();
            if ($cellValue !== null && $cellValue !== '') {
                $rowData[] = trim((string)$cellValue);
            }
        }
        
        if (!empty($rowData)) {
            $rowText = implode(' | ', $rowData);
            // Cek apakah ini header (berisi kata kunci seperti "ID", "Nama", "Tanggal", dll)
            $headerKeywords = ['id', 'nama', 'tanggal', 'kode', 'jabatan', 'departemen', 'karyawan'];
            $isHeader = false;
            foreach ($headerKeywords as $keyword) {
                if (stripos($rowText, $keyword) !== false) {
                    $isHeader = true;
                    break;
                }
            }
            
            if ($isHeader) {
                echo "Row {$row} (Mungkin Header): {$rowText}\n";
            }
        }
    }
    
    // Cek apakah ada data yang terstruktur (setiap baris memiliki pola yang sama)
    echo "\n\n=== Analisis Struktur Data ===\n";
    $sampleRows = [];
    for ($row = 1; $row <= min(15, $highestRow); $row++) {
        $rowData = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellAddress = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
            $cellValue = $worksheet->getCell($cellAddress)->getValue();
            $rowData[] = $cellValue !== null ? (string)$cellValue : '';
        }
        $sampleRows[$row] = $rowData;
    }
    
    // Tampilkan dalam format tabel
    echo "\nFormat Tabel (Baris 1-15, Kolom A-N):\n";
    echo str_repeat("-", 120) . "\n";
    printf("%-5s", "Row");
    for ($col = 1; $col <= min(14, $highestColumnIndex); $col++) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
        printf("| %-6s", $colLetter);
    }
    echo "\n" . str_repeat("-", 120) . "\n";
    
    for ($row = 1; $row <= min(15, $highestRow); $row++) {
        printf("%-5s", $row);
        for ($col = 1; $col <= min(14, $highestColumnIndex); $col++) {
            $value = isset($sampleRows[$row][$col - 1]) ? $sampleRows[$row][$col - 1] : '';
            $display = mb_substr($value, 0, 6);
            printf("| %-6s", $display);
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}

