<?php
/**
 * Script untuk download manual PhpSpreadsheet dan dependencies
 * 
 * Library yang akan didownload:
 * 1. phpoffice/phpspreadsheet
 * 2. psr/simple-cache (dependency)
 * 3. markbaker/matrix (dependency)
 * 4. markbaker/complex (dependency)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Download Manual PhpSpreadsheet ===\n\n";

// Konfigurasi
$baseDir = __DIR__;
$vendorDir = $baseDir . '/vendor';
$tempDir = $baseDir . '/temp_downloads';

// Library yang akan didownload (GitHub repository URLs)
$libraries = [
    'phpspreadsheet' => [
        'name' => 'phpoffice/phpspreadsheet',
        'url' => 'https://github.com/PHPOffice/PhpSpreadsheet/archive/refs/heads/master.zip',
        'target' => 'phpoffice/phpspreadsheet',
        'version' => 'master'
    ],
    'simple-cache' => [
        'name' => 'psr/simple-cache',
        'url' => 'https://github.com/php-fig/simple-cache/archive/refs/tags/3.0.0.zip',
        'target' => 'psr/simple-cache',
        'version' => '3.0.0'
    ],
    'matrix' => [
        'name' => 'markbaker/matrix',
        'url' => 'https://github.com/MarkBaker/PHPMatrix/archive/refs/heads/master.zip',
        'target' => 'markbaker/matrix',
        'version' => 'master'
    ],
    'complex' => [
        'name' => 'markbaker/complex',
        'url' => 'https://github.com/MarkBaker/PHPComplex/archive/refs/heads/master.zip',
        'target' => 'markbaker/complex',
        'version' => 'master'
    ],
    'pcre' => [
        'name' => 'composer/pcre',
        'url' => 'https://github.com/composer/pcre/archive/refs/heads/main.zip',
        'target' => 'composer/pcre',
        'version' => 'main'
    ]
];

/**
 * Download file dari URL
 */
function downloadFile($url, $destination) {
    echo "Downloading: {$url}\n";
    echo "To: {$destination}\n";
    
    // Cek apakah extension curl tersedia
    if (!function_exists('curl_init')) {
        // Fallback ke file_get_contents
        echo "cURL tidak tersedia, menggunakan file_get_contents...\n";
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: PHP Download Script',
                    'Accept: application/zip'
                ],
                'timeout' => 300,
                'follow_location' => true
            ]
        ]);
        
        $content = @file_get_contents($url, false, $context);
        if ($content === false) {
            echo "ERROR: Gagal download menggunakan file_get_contents\n";
            return false;
        }
    } else {
        // Gunakan cURL (lebih reliable)
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Download Script');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Untuk development
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($content === false || $httpCode !== 200) {
            echo "ERROR: Gagal download. HTTP Code: {$httpCode}\n";
            if ($error) {
                echo "cURL Error: {$error}\n";
            }
            return false;
        }
    }
    
    // Simpan file
    $dir = dirname($destination);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $written = file_put_contents($destination, $content);
    if ($written === false) {
        echo "ERROR: Gagal menulis file ke {$destination}\n";
        return false;
    }
    
    echo "✓ Download berhasil ({$written} bytes)\n\n";
    return true;
}

/**
 * Extract ZIP file
 */
function extractZip($zipFile, $targetDir) {
    echo "Extracting: {$zipFile}\n";
    echo "To: {$targetDir}\n";
    
    if (!class_exists('ZipArchive')) {
        echo "ERROR: Extension ZipArchive tidak tersedia. Install php-zip extension.\n";
        return false;
    }
    
    $zip = new ZipArchive();
    $result = $zip->open($zipFile);
    
    if ($result !== true) {
        echo "ERROR: Gagal membuka ZIP file. Code: {$result}\n";
        return false;
    }
    
    // Buat target directory
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Extract semua file
    $extracted = $zip->extractTo($targetDir);
    $zip->close();
    
    if (!$extracted) {
        echo "ERROR: Gagal extract ZIP file\n";
        return false;
    }
    
    echo "✓ Extract berhasil\n\n";
    return true;
}

/**
 * Rename extracted folder ke nama yang benar
 */
function renameExtractedFolder($extractedPath, $targetPath) {
    // Cari folder yang diextract (biasanya ada subfolder dengan nama repo-version)
    $files = scandir($extractedPath);
    $found = false;
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $fullPath = $extractedPath . '/' . $file;
        if (is_dir($fullPath)) {
            // Pindahkan isi folder ke target
            $targetParent = dirname($targetPath);
            if (!is_dir($targetParent)) {
                mkdir($targetParent, 0755, true);
            }
            
            // Jika target sudah ada, hapus dulu
            if (is_dir($targetPath)) {
                deleteDirectory($targetPath);
            }
            
            // Rename folder
            if (rename($fullPath, $targetPath)) {
                $found = true;
                echo "✓ Folder di-rename ke: {$targetPath}\n\n";
                break;
            }
        }
    }
    
    if (!$found) {
        echo "WARNING: Tidak menemukan folder yang diextract\n";
        return false;
    }
    
    return true;
}

/**
 * Hapus directory secara recursive
 */
function deleteDirectory($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    return rmdir($dir);
}

// Main process
try {
    // Buat directory vendor dan temp
    if (!is_dir($vendorDir)) {
        mkdir($vendorDir, 0755, true);
        echo "✓ Directory vendor dibuat\n";
    }
    
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
        echo "✓ Directory temp_downloads dibuat\n";
    }
    
    echo "\n";
    
    // Download dan extract setiap library
    foreach ($libraries as $key => $lib) {
        echo "--- Processing: {$lib['name']} ---\n";
        
        $zipFile = $tempDir . '/' . $key . '.zip';
        $extractDir = $tempDir . '/' . $key;
        $targetPath = $vendorDir . '/' . $lib['target'];
        
        // Skip jika sudah ada
        if (is_dir($targetPath)) {
            echo "✓ Library sudah ada, skip: {$targetPath}\n\n";
            continue;
        }
        
        // Download
        if (!downloadFile($lib['url'], $zipFile)) {
            echo "ERROR: Gagal download {$lib['name']}\n\n";
            continue;
        }
        
        // Extract
        if (!extractZip($zipFile, $extractDir)) {
            echo "ERROR: Gagal extract {$lib['name']}\n\n";
            continue;
        }
        
        // Rename folder
        if (!renameExtractedFolder($extractDir, $targetPath)) {
            echo "ERROR: Gagal rename folder untuk {$lib['name']}\n\n";
            continue;
        }
        
        // Hapus ZIP file
        if (file_exists($zipFile)) {
            unlink($zipFile);
        }
        
        // Hapus extract directory
        if (is_dir($extractDir)) {
            deleteDirectory($extractDir);
        }
        
        echo "✓ {$lib['name']} selesai\n\n";
    }
    
    // Cleanup temp directory
    if (is_dir($tempDir)) {
        $files = scandir($tempDir);
        $isEmpty = true;
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $isEmpty = false;
                break;
            }
        }
        if ($isEmpty) {
            rmdir($tempDir);
            echo "✓ Temp directory dibersihkan\n";
        }
    }
    
    echo "\n=== Download Selesai ===\n";
    echo "Library tersimpan di: {$vendorDir}\n";
    echo "\nLangkah selanjutnya:\n";
    echo "1. Setup autoloader untuk PhpSpreadsheet\n";
    echo "2. Test dengan membaca file Excel\n";
    
} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

