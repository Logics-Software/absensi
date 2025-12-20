<?php
/**
 * Controller untuk import data dari file Finger.xlsx
 */

require_once __DIR__ . '/../services/FingerExcelService.php';
require_once __DIR__ . '/../models/AbsensiSiswa.php';
require_once __DIR__ . '/../models/AbsensiGuru.php';
require_once __DIR__ . '/../models/Mastersiswa.php';
require_once __DIR__ . '/../models/MasterGuru.php';
require_once __DIR__ . '/../models/SettingJamBelajar.php';

class FingerImportController extends Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Halaman index - tampilkan form upload
     */
    public function index() {
        $data = [
            'error' => null,
            'success_message' => null
        ];
        
        // Check for success message
        if (isset($_SESSION['finger_import_success'])) {
            $data['success_message'] = $_SESSION['finger_import_success'];
            unset($_SESSION['finger_import_success']);
        }
        
        // Check for error message
        if (isset($_SESSION['finger_import_error'])) {
            $data['error'] = $_SESSION['finger_import_error'];
            unset($_SESSION['finger_import_error']);
        }
        
        $this->view('fingerimport/index', $data);
    }
    
    /**
     * Handle file upload
     */
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/fingerimport');
            return;
        }
        
        try {
            // Check if file was uploaded
            if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                $errorMsg = 'Tidak ada file yang diupload atau terjadi error saat upload.';
                if (isset($_FILES['excel_file']['error'])) {
                    switch ($_FILES['excel_file']['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $errorMsg = 'Ukuran file terlalu besar. Maksimal 10MB.';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $errorMsg = 'File hanya terupload sebagian.';
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $errorMsg = 'Tidak ada file yang dipilih.';
                            break;
                    }
                }
                Message::error($errorMsg);
                $this->redirect('/fingerimport');
                return;
            }
            
            $file = $_FILES['excel_file'];
            
            // Validate file type
            $allowedTypes = ['xlsx', 'xls'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExt, $allowedTypes)) {
                Message::error('Format file tidak diizinkan. Hanya file Excel (.xlsx, .xls) yang diperbolehkan.');
                $this->redirect('/fingerimport');
                return;
            }
            
            // Validate file size (max 10MB)
            $maxSize = 10 * 1024 * 1024; // 10MB
            if ($file['size'] > $maxSize) {
                Message::error('Ukuran file terlalu besar. Maksimal 10MB.');
                $this->redirect('/fingerimport');
                return;
            }
            
            // Langsung proses file dari tmp_name tanpa menyimpan ke disk
            $tmpFilePath = $file['tmp_name'];
            
            // Validate file can be read by PhpSpreadsheet
            try {
                $service = new FingerExcelService($tmpFilePath);
                $service->load();
                // Test read
                $service->getAllData();
            } catch (Exception $e) {
                Message::error('File Excel tidak valid atau rusak: ' . $e->getMessage());
                $this->redirect('/fingerimport');
                return;
            }
            
            // Langsung import to database dari file temporary
            $importResult = $this->importToDatabase($tmpFilePath);
            
            if ($importResult['success']) {
                $msg = 'Data absensi berhasil disimpan! ';
                $msg .= "Siswa: {$importResult['siswa_count']}, Guru: {$importResult['guru_count']}, ";
                $msg .= "Total: {$importResult['total_saved']} records";
                if (!empty($importResult['errors'])) {
                    $msg .= " (Warning: " . count($importResult['errors']) . " data tidak ditemukan)";
                }
                Message::success($msg);
                $_SESSION['finger_import_success'] = $msg;
            } else {
                Message::error('Gagal menyimpan data: ' . $importResult['message']);
                $_SESSION['finger_import_error'] = 'Gagal menyimpan data: ' . $importResult['message'];
            }
            
            $this->redirect('/fingerimport');
            
        } catch (Exception $e) {
            Message::error('Error saat upload: ' . $e->getMessage());
            $this->redirect('/fingerimport');
        }
    }
    
    /**
     * Import data absensi ke database
     */
    private function importToDatabase($excelFile) {
        try {
            $service = new FingerExcelService($excelFile);
            $service->load();
            $allData = $service->getAllData();
            
            $absensiSiswaModel = new AbsensiSiswa();
            $absensiGuruModel = new AbsensiGuru();
            
            $siswaCount = 0;
            $guruCount = 0;
            $totalSaved = 0;
            $errors = [];
            
            // Process setiap employee
            foreach ($allData['employees'] as $emp) {
                $fingerprintId = $emp['employee']['fingerprint_id'] ?? $emp['employee']['kode_karyawan'] ?? null;
                
                if (!$fingerprintId) {
                    $errors[] = "Fingerprint ID tidak ditemukan untuk employee: " . ($emp['employee']['nama'] ?? 'Unknown');
                    continue;
                }
                
                // Cari di mastersiswa berdasarkan noabsensi
                $siswa = $this->db->fetchOne(
                    "SELECT nisn, namasiswa FROM mastersiswa WHERE noabsensi = ? LIMIT 1",
                    [$fingerprintId]
                );
                
                if ($siswa) {
                    // Simpan ke absensi_siswa
                    $logsByDate = $this->groupLogsByDate($emp['attendance_logs']);
                    
                    foreach ($logsByDate as $tanggal => $dayLogs) {
                        $jammasuk = null;
                        $jamkeluar = null;
                        $keterangan = [];
                        $checkInTimes = [];
                        $checkOutTimes = [];
                        
                        foreach ($dayLogs as $log) {
                            $jamLog = $this->formatTime($log['jam_log'] ?? null);
                            if (($log['tipe'] ?? '') === 'Check In' && $jamLog) {
                                $checkInTimes[] = $jamLog;
                            } elseif (($log['tipe'] ?? '') === 'Check Out' && $jamLog) {
                                $checkOutTimes[] = $jamLog;
                            }
                            if (!empty($log['keterangan'])) {
                                $keterangan[] = $log['keterangan'];
                            }
                        }
                        
                        // Ambil Check In paling awal dan Check Out paling akhir
                        if (!empty($checkInTimes)) {
                            sort($checkInTimes);
                            $jammasuk = $checkInTimes[0]; // Paling awal
                        }
                        if (!empty($checkOutTimes)) {
                            sort($checkOutTimes);
                            $jamkeluar = end($checkOutTimes); // Paling akhir
                        }
                        
                        // Tentukan status berdasarkan jam masuk dan jam keluar
                        $status = $this->determineStatus($tanggal, $jammasuk, $jamkeluar);
                        
                        // Set keterangan berdasarkan status
                        $keteranganStatus = $this->getKeteranganByStatus($status);
                        
                        // Cek apakah sudah ada absensi untuk tanggal ini
                        $existing = $absensiSiswaModel->getByNisnAndDate($siswa['nisn'], $tanggal);
                        
                        if ($existing) {
                            // Update existing
                            $absensiSiswaModel->update($existing['id'], [
                                'jammasuk' => $jammasuk,
                                'jamkeluar' => $jamkeluar,
                                'keterangan' => $keteranganStatus,
                                'status' => $status
                            ]);
                        } else {
                            // Create new
                            $absensiSiswaModel->create([
                                'nisn' => $siswa['nisn'],
                                'tanggalabsen' => $tanggal,
                                'jammasuk' => $jammasuk,
                                'jamkeluar' => $jamkeluar,
                                'keterangan' => $keteranganStatus,
                                'status' => $status
                            ]);
                        }
                        $totalSaved++;
                    }
                    $siswaCount++;
                } else {
                    // Cari di masterguru berdasarkan noabsensi
                    $guru = $this->db->fetchOne(
                        "SELECT nip, namaguru FROM masterguru WHERE noabsensi = ? LIMIT 1",
                        [$fingerprintId]
                    );
                    
                    if ($guru) {
                        // Simpan ke absensi_guru
                        $logsByDate = $this->groupLogsByDate($emp['attendance_logs']);
                        
                        foreach ($logsByDate as $tanggal => $dayLogs) {
                            $jammasuk = null;
                            $jamkeluar = null;
                            $keterangan = [];
                            $checkInTimes = [];
                            $checkOutTimes = [];
                            
                            foreach ($dayLogs as $log) {
                                $jamLog = $this->formatTime($log['jam_log'] ?? null);
                                if (($log['tipe'] ?? '') === 'Check In' && $jamLog) {
                                    $checkInTimes[] = $jamLog;
                                } elseif (($log['tipe'] ?? '') === 'Check Out' && $jamLog) {
                                    $checkOutTimes[] = $jamLog;
                                }
                                if (!empty($log['keterangan'])) {
                                    $keterangan[] = $log['keterangan'];
                                }
                            }
                            
                            // Ambil Check In paling awal dan Check Out paling akhir
                            if (!empty($checkInTimes)) {
                                sort($checkInTimes);
                                $jammasuk = $checkInTimes[0]; // Paling awal
                            }
                            if (!empty($checkOutTimes)) {
                                sort($checkOutTimes);
                                $jamkeluar = end($checkOutTimes); // Paling akhir
                            }
                            
                            // Tentukan status berdasarkan jam masuk dan jam keluar
                            $status = $this->determineStatus($tanggal, $jammasuk, $jamkeluar);
                            
                            // Set keterangan berdasarkan status
                            $keteranganStatus = $this->getKeteranganByStatus($status);
                            
                            // Cek apakah sudah ada absensi untuk tanggal ini (ambil yang terakhir jika ada multiple)
                            $existing = $this->db->fetchOne(
                                "SELECT id FROM absensi_guru WHERE nip = ? AND tanggalabsen = ? ORDER BY id DESC LIMIT 1",
                                [$guru['nip'], $tanggal]
                            );
                            
                            if ($existing) {
                                // Update existing
                                $absensiGuruModel->update($existing['id'], [
                                    'jammasuk' => $jammasuk,
                                    'jamkeluar' => $jamkeluar,
                                    'keterangan' => $keteranganStatus,
                                    'status' => $status
                                ]);
                            } else {
                                // Create new
                                $absensiGuruModel->create([
                                    'nip' => $guru['nip'],
                                    'tanggalabsen' => $tanggal,
                                    'jammasuk' => $jammasuk,
                                    'jamkeluar' => $jamkeluar,
                                    'keterangan' => $keteranganStatus,
                                    'status' => $status
                                ]);
                            }
                            $totalSaved++;
                        }
                        $guruCount++;
                    } else {
                        $errors[] = "Fingerprint ID '{$fingerprintId}' tidak ditemukan di mastersiswa maupun masterguru untuk: " . ($emp['employee']['nama'] ?? 'Unknown');
                    }
                }
            }
            
            return [
                'success' => true,
                'siswa_count' => $siswaCount,
                'guru_count' => $guruCount,
                'total_saved' => $totalSaved,
                'errors' => $errors,
                'message' => !empty($errors) ? implode('; ', $errors) : 'Berhasil'
            ];
            
        } catch (Exception $e) {
            error_log("FingerImport importToDatabase error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'siswa_count' => 0,
                'guru_count' => 0,
                'total_saved' => 0,
                'errors' => []
            ];
        }
    }
    
    /**
     * Group logs by date
     */
    private function groupLogsByDate($logs) {
        $grouped = [];
        foreach ($logs as $log) {
            $tanggal = $log['tanggal_log'] ?? null;
            if ($tanggal) {
                // Normalize date format to Y-m-d
                if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $tanggal, $matches)) {
                    $tanggal = $matches[0];
                } elseif (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})/', $tanggal, $matches)) {
                    $tanggal = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                } elseif (preg_match('/^(\d{2})-(\d{2})-(\d{4})/', $tanggal, $matches)) {
                    $tanggal = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                }
                
                if (!isset($grouped[$tanggal])) {
                    $grouped[$tanggal] = [];
                }
                $grouped[$tanggal][] = $log;
            }
        }
        return $grouped;
    }
    
    /**
     * Format time to HH:MM:SS
     */
    private function formatTime($time) {
        if (empty($time)) {
            return null;
        }
        
        // Jika sudah format HH:MM atau HH:MM:SS
        if (preg_match('/^(\d{2}):(\d{2})(:(\d{2}))?$/', $time, $matches)) {
            return $time . (isset($matches[4]) ? '' : ':00');
        }
        
        return $time;
    }
    
    /**
     * Determine status based on jam masuk and jam keluar
     * Returns: 'tidak_hadir', 'hadir', 'terlambat', 'pulang_awal'
     */
    private function determineStatus($tanggal, $jammasuk, $jamkeluar) {
        // Jika tidak ada jam masuk, berarti tidak hadir
        if (empty($jammasuk)) {
            return 'tidak_hadir';
        }
        
        // Ambil setting jam belajar
        $settingJamModel = new SettingJamBelajar();
        $settingJam = $settingJamModel->get();
        
        if (!$settingJam) {
            // Jika tidak ada setting, default ke hadir
            return 'hadir';
        }
        
        // Tentukan hari dari tanggal
        $dateObj = DateTime::createFromFormat('Y-m-d', $tanggal);
        if (!$dateObj) {
            return 'hadir'; // Fallback
        }
        
        $dayOfWeek = (int)$dateObj->format('w'); // 0 = Sunday, 1 = Monday, etc.
        $dayNames = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $dayName = $dayNames[$dayOfWeek];
        
        // Ambil jam standar untuk hari tersebut
        $jamMasukStandar = null;
        $jamPulangStandar = null;
        
        $jamMasukField = 'jammasuk' . $dayName;
        $jamPulangField = 'jampulang' . $dayName;
        
        if (isset($settingJam[$jamMasukField]) && !empty($settingJam[$jamMasukField])) {
            $jamMasukStandar = $settingJam[$jamMasukField];
        }
        if (isset($settingJam[$jamPulangField]) && !empty($settingJam[$jamPulangField])) {
            $jamPulangStandar = $settingJam[$jamPulangField];
        }
        
        // Jika tidak ada setting untuk hari tersebut, default ke hadir
        if (empty($jamMasukStandar)) {
            return 'hadir';
        }
        
        // Bandingkan jam masuk dengan jam standar
        $status = 'hadir';
        $terlambat = false;
        $pulangAwal = false;
        
        try {
            $jamMasukActual = new DateTime($jammasuk);
            $jamMasukStandarObj = new DateTime($jamMasukStandar);
            
            // Jika jam masuk lebih dari jam standar, berarti terlambat
            if ($jamMasukActual > $jamMasukStandarObj) {
                $terlambat = true;
            }
            
            // Bandingkan jam keluar dengan jam standar (jika ada)
            if (!empty($jamkeluar) && !empty($jamPulangStandar)) {
                $jamKeluarActual = new DateTime($jamkeluar);
                $jamPulangStandarObj = new DateTime($jamPulangStandar);
                
                // Jika jam keluar lebih awal dari jam standar, berarti pulang awal
                if ($jamKeluarActual < $jamPulangStandarObj) {
                    $pulangAwal = true;
                }
            }
            
            // Tentukan status akhir
            if ($terlambat && $pulangAwal) {
                $status = 'terlambat'; // Prioritas ke terlambat jika keduanya true
            } elseif ($terlambat) {
                $status = 'terlambat';
            } elseif ($pulangAwal) {
                $status = 'pulang_awal';
            } else {
                $status = 'hadir';
            }
        } catch (Exception $e) {
            error_log("Error determining status: " . $e->getMessage());
            return 'hadir'; // Fallback
        }
        
        return $status;
    }
    
    /**
     * Get keterangan text based on status
     */
    private function getKeteranganByStatus($status) {
        $keteranganMap = [
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'pulang_awal' => 'Pulang Awal',
            'tidak_hadir' => 'Tidak Hadir'
        ];
        
        return $keteranganMap[$status] ?? 'Hadir';
    }
    
}

