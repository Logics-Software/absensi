<?php
/**
 * Test script untuk memastikan PhpSpreadsheet bisa di-load dan membaca file Excel
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Test PhpSpreadsheet ===\n\n";

// Load autoloader dari index.php
require __DIR__ . '/index.php';

// Use statements harus setelah require
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

try {
    // Test 1: Cek apakah class PhpSpreadsheet bisa di-load
    echo "1. Testing class loading...\n";
    
    if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
        throw new Exception("Class PhpOffice\\PhpSpreadsheet\\IOFactory tidak ditemukan");
    }
    
    echo "   ✓ IOFactory class loaded\n";
    
    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        throw new Exception("Class PhpOffice\\PhpSpreadsheet\\Spreadsheet tidak ditemukan");
    }
    
    echo "   ✓ Spreadsheet class loaded\n";
    
    // Test 2: Cek apakah file Excel ada
    echo "\n2. Testing file Excel...\n";
    $excelFile = __DIR__ . '/Finger.xlsx';
    
    if (!file_exists($excelFile)) {
        throw new Exception("File Excel tidak ditemukan: {$excelFile}");
    }
    
    echo "   ✓ File Excel ditemukan: Finger.xlsx\n";
    echo "   File size: " . filesize($excelFile) . " bytes\n";
    
    // Test 3: Coba baca file Excel
    echo "\n3. Testing read Excel file...\n";
    
    $spreadsheet = IOFactory::load($excelFile);
    echo "   ✓ File Excel berhasil di-load\n";
    
    // Test 4: Cek worksheet
    echo "\n4. Testing worksheet...\n";
    $worksheet = $spreadsheet->getActiveSheet();
    $sheetName = $worksheet->getTitle();
    echo "   ✓ Active sheet: {$sheetName}\n";
    
    // Test 5: Baca beberapa baris pertama
    echo "\n5. Testing read data (first 10 rows)...\n";
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();
    
    echo "   Total rows: {$highestRow}\n";
    echo "   Highest column: {$highestColumn}\n\n";
    
    $maxRows = min(10, $highestRow);
    for ($row = 1; $row <= $maxRows; $row++) {
        $rowData = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $cellValue = $worksheet->getCell($col . $row)->getValue();
            $rowData[] = $cellValue !== null ? (string)$cellValue : '';
        }
        echo "   Row {$row}: " . implode(' | ', array_slice($rowData, 0, 5)) . "\n";
    }
    
    // Test 6: Cek semua worksheet
    echo "\n6. Testing all worksheets...\n";
    $sheetCount = $spreadsheet->getSheetCount();
    echo "   Total worksheets: {$sheetCount}\n";
    
    for ($i = 0; $i < $sheetCount; $i++) {
        $sheet = $spreadsheet->getSheet($i);
        $name = $sheet->getTitle();
        $rows = $sheet->getHighestRow();
        $cols = $sheet->getHighestColumn();
        echo "   - Sheet " . ($i + 1) . ": {$name} ({$rows} rows, {$cols} columns)\n";
    }
    
    echo "\n=== Test Berhasil! ===\n";
    echo "PhpSpreadsheet siap digunakan untuk membaca file Excel.\n";
    
} catch (Exception $e) {
    echo "\n=== ERROR ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} catch (Error $e) {
    echo "\n=== FATAL ERROR ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
