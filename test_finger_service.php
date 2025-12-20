<?php
/**
 * Test script untuk FingerExcelService
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/index.php';

require_once __DIR__ . '/services/FingerExcelService.php';

echo "=== Test FingerExcelService ===\n\n";

try {
    $service = new FingerExcelService();
    $service->load();
    
    // Test 1: Get employee info
    echo "1. Employee Info:\n";
    $employeeInfo = $service->getEmployeeInfo();
    foreach ($employeeInfo as $key => $value) {
        echo "   {$key}: " . ($value ?? '(null)') . "\n";
    }
    
    // Test 2: Get attendance logs (first 10)
    echo "\n2. Attendance Logs (first 10):\n";
    $logs = $service->getAttendanceLogs();
    $count = min(10, count($logs));
    
    for ($i = 0; $i < $count; $i++) {
        $log = $logs[$i];
        echo "\n   Log " . ($i + 1) . ":\n";
        foreach ($log as $key => $value) {
            echo "     {$key}: " . ($value ?? '(null)') . "\n";
        }
    }
    
    echo "\n   Total logs: " . count($logs) . "\n";
    
    // Test 3: Get all data
    echo "\n3. All Data Summary:\n";
    $allData = $service->getAllData();
    echo "   Employee: " . ($allData['employee']['nama'] ?? 'N/A') . "\n";
    echo "   Total Attendance Logs: " . count($allData['attendance_logs']) . "\n";
    
    echo "\n=== Test Berhasil! ===\n";
    
} catch (Exception $e) {
    echo "\n=== ERROR ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

