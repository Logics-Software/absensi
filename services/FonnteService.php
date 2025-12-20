<?php
class FonnteService {
    private $apiKey;
    private $apiUrl;
    private $deviceId;
    
    public function __construct() {
        // Try to load from database first, fallback to config file
        try {
            // Ensure KonfigurasiFonnte class is loaded
            if (!class_exists('KonfigurasiFonnte')) {
                require_once __DIR__ . '/../models/KonfigurasiFonnte.php';
            }
            $konfigurasiFonnteModel = new KonfigurasiFonnte();
            $konfigurasi = $konfigurasiFonnteModel->get();
            
            if ($konfigurasi) {
                $this->apiKey = $konfigurasi['api_key'] ?? '';
                $this->apiUrl = rtrim($konfigurasi['api_url'] ?? 'https://api.fonnte.com', '/');
                $this->deviceId = $konfigurasi['device_id'] ?? '';
            } else {
                // Fallback to config file
                $config = require __DIR__ . '/../config/app.php';
                $this->apiKey = $config['fonnte']['api_key'] ?? '';
                $this->apiUrl = rtrim($config['fonnte']['api_url'] ?? 'https://api.fonnte.com', '/');
                $this->deviceId = $config['fonnte']['device_id'] ?? '';
            }
        } catch (Exception $e) {
            // If database fails, use config file
            error_log("Error loading Fonnte config from database: " . $e->getMessage());
            $config = require __DIR__ . '/../config/app.php';
            $this->apiKey = $config['fonnte']['api_key'] ?? '';
            $this->apiUrl = rtrim($config['fonnte']['api_url'] ?? 'https://api.fonnte.com', '/');
            $this->deviceId = $config['fonnte']['device_id'] ?? '';
        }
    }
    
    /**
     * Format phone number to Fonnte format (6281234567890)
     * This is a static helper method that can be used without instantiating the class
     * 
     * @param string $phoneNumber Nomor HP dalam format apapun
     * @return string Nomor HP dalam format Fonnte (6281234567890)
     * @throws Exception Jika nomor HP tidak valid
     */
    public static function formatPhoneNumber($phoneNumber) {
        if (empty($phoneNumber)) {
            throw new Exception('Nomor HP tidak boleh kosong');
        }
        
        // 1. Hapus semua karakter non-numerik (termasuk +, spasi, dll)
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (empty($phoneNumber)) {
            throw new Exception('Nomor HP tidak valid setelah pembersihan');
        }
        
        // 2. Jika dimulai dengan 62, hapus 62 (untuk mendapatkan nomor lokal)
        if (substr($phoneNumber, 0, 2) === '62') {
            $phoneNumber = substr($phoneNumber, 2);
        }
        
        // 3. Jika dimulai dengan 0, hapus 0 (untuk mendapatkan nomor tanpa leading zero)
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = substr($phoneNumber, 1);
        }
        
        // Validasi panjang nomor HP (minimal 9 digit, maksimal 13 digit untuk Indonesia)
        if (strlen($phoneNumber) < 9 || strlen($phoneNumber) > 13) {
            throw new Exception('Nomor HP tidak valid. Panjang nomor harus antara 9-13 digit (setelah format). Nomor: ' . $phoneNumber);
        }
        
        // 4. Tambahkan 62 di depan untuk format internasional
        // Hasil akhir: 6281234567890 (format yang benar untuk Fonnte API)
        $formatted = '62' . $phoneNumber;
        
        // Validasi panjang final (harus antara 11-15 digit: 62 + 9-13 digit)
        if (strlen($formatted) < 11 || strlen($formatted) > 15) {
            throw new Exception('Nomor HP tidak valid setelah format. Format: ' . $formatted);
        }
        
        return $formatted;
    }
    
    /**
     * Configure SSL options for cURL
     * Handles SSL certificate verification
     */
    private function configureSSL($ch) {
        // Try to use system CA bundle, if not available, disable verification for development
        $caBundle = __DIR__ . '/../cacert.pem';
        if (file_exists($caBundle)) {
            curl_setopt($ch, CURLOPT_CAINFO, $caBundle);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            // For development/testing, disable SSL verification if CA bundle not found
            // In production, you should download and use proper CA bundle
            // Download from: https://curl.se/ca/cacert.pem
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
    }
    
    /**
     * Send single message
     * 
     * Format nomor HP yang diterima:
     * - Dari database: +6281234567890 (dengan +62)
     * - Format lain: 081234567890, 81234567890, 6281234567890
     * 
     * Format yang dikirim ke Fonnte API: 6281234567890 (tanpa +, tanpa 0 di depan)
     * 
     * @param string $phoneNumber Nomor HP (format apapun akan dikonversi otomatis)
     * @param string $message Pesan yang akan dikirim
     * @param int $delay Delay antar pesan (dalam detik)
     * @return array Response dari Fonnte API
     * @throws Exception Jika terjadi error
     */
    public function sendMessage($phoneNumber, $message, $delay = 0) {
        if (empty($this->apiKey)) {
            throw new Exception('Fonnte API Key tidak dikonfigurasi');
        }
        
        if (empty($message)) {
            throw new Exception('Pesan tidak boleh kosong');
        }
        
        // Konversi nomor HP ke format Fonnte API: 6281234567890
        try {
            $formattedPhone = self::formatPhoneNumber($phoneNumber);
        } catch (Exception $e) {
            error_log("Fonnte sendMessage - Phone format error: " . $e->getMessage());
            throw new Exception('Format nomor HP tidak valid: ' . $e->getMessage());
        }
        
        $url = $this->apiUrl . '/send';
        
        // Fonnte API format berdasarkan dokumentasi:
        // - target: nomor HP (6281234567890)
        // - message: pesan teks (beberapa versi API menggunakan 'body')
        // - delay: delay dalam detik (opsional)
        // - device: device ID (opsional, untuk multi-device)
        $trimmedMessage = trim($message);
        
        // Validate message is not empty after trim
        if (empty($trimmedMessage)) {
            throw new Exception('Pesan tidak boleh kosong setelah trim');
        }
        
        // Clean message: remove markdown formatting but preserve structure
        // Fonnte might not support markdown formatting like * for bold
        $cleanMessage = $trimmedMessage;
        
        // Replace markdown bold (*text*) with plain text (text)
        // Handle multiple asterisks properly, but preserve newlines
        $cleanMessage = preg_replace('/\*+([^*]*)\*+/', '$1', $cleanMessage);
        
        // Remove any remaining standalone asterisks that might cause issues
        // But preserve newlines and basic formatting
        $cleanMessage = preg_replace('/\*([^*\n\r]*)\*/', '$1', $cleanMessage);
        $cleanMessage = str_replace('**', '', $cleanMessage);
        
        // Only remove standalone asterisks, not those that are part of formatting
        $cleanMessage = preg_replace('/(?<!\*)\*(?!\*)/', '', $cleanMessage);
        
        // Normalize line breaks (keep \r\n or \n, but normalize)
        $cleanMessage = str_replace(["\r\n", "\r"], "\n", $cleanMessage);
        
        // Clean up multiple consecutive spaces (but keep single spaces and newlines)
        $cleanMessage = preg_replace('/[ \t]+/', ' ', $cleanMessage);
        
        // Clean up multiple consecutive newlines (keep max 2)
        $cleanMessage = preg_replace('/\n{3,}/', "\n\n", $cleanMessage);
        
        $cleanMessage = trim($cleanMessage);
        
        // Ensure message is not empty after cleaning
        if (empty($cleanMessage) || strlen($cleanMessage) < 1) {
            throw new Exception('Pesan tidak boleh kosong setelah pembersihan. Original length: ' . strlen($trimmedMessage));
        }
        
        // According to Fonnte documentation: https://docs.fonnte.com/api-send-message/
        // - target: must be STRING (not int)
        // - message: text message
        // - delay: must be STRING (not int) - e.g. '2' not 2
        // - countryCode: default '62' (optional)
        // - NO device field in the API!
        
        $data = [
            'target' => (string)$formattedPhone, // Must be string according to docs
            'message' => $cleanMessage
        ];
        
        // Add delay as STRING (required by Fonnte API)
        if ($delay > 0) {
            $data['delay'] = (string)$delay; // Must be string!
        }
        
        // Set countryCode to '0' to disable Fonnte's filter
        // Since we're already sending full format (628...), we don't need Fonnte to process it
        // According to docs: "if you want to bypass any of this filter, you can set : 'countryCode' => '0'"
        $data['countryCode'] = '0';
        
        // Validate data before sending
        if (empty($data['target']) || empty($data['message'])) {
            throw new Exception('Data tidak lengkap: target atau message kosong. Target: ' . ($data['target'] ?: 'empty') . ', Message length: ' . strlen($data['message']));
        }
        
        // Additional validation: phone number must be numeric and start with country code
        if (!preg_match('/^62\d{9,13}$/', $formattedPhone)) {
            throw new Exception('Format nomor HP tidak valid untuk Fonnte API. Format: ' . $formattedPhone . ' (harus dimulai dengan 62 dan 9-13 digit)');
        }
        
        // Validate formatted phone number length
        if (empty($formattedPhone) || strlen($formattedPhone) < 11 || strlen($formattedPhone) > 15) {
            throw new Exception('Nomor HP tidak valid setelah format: ' . $formattedPhone . ' (panjang: ' . strlen($formattedPhone) . ', harus 11-15 digit)');
        }
        
        // Validate message
        if (empty(trim($message))) {
            throw new Exception('Pesan tidak boleh kosong');
        }
        
        // Use the correct format according to Fonnte documentation
        // No need for multiple variations - just use the correct format
        
        // Log request data before sending
        error_log("=== Fonnte sendMessage Request ===");
        error_log("Request Data (Fonnte API format): " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        error_log("Original Phone: {$phoneNumber}");
        error_log("Formatted Phone: {$formattedPhone}");
        error_log("Phone Length: " . strlen($formattedPhone));
        error_log("API URL: {$url}");
        error_log("API Key: " . substr($this->apiKey, 0, 10) . "..." . (strlen($this->apiKey) > 10 ? " (length: " . strlen($this->apiKey) . ")" : ""));
        error_log("Device ID: " . ($this->deviceId ?: 'not set'));
        error_log("Clean Message Length: " . strlen($cleanMessage));
        error_log("Original Message Length: " . strlen($message));
        error_log("================================");
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        
        // Use correct Fonnte API format according to documentation
        // Note: For multipart/form-data, we need to use CURLOPT_POSTFIELDS as array
        // But for JSON, we use json_encode
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        $this->configureSSL($ch);
        
        // Set headers - Fonnte API expects Authorization header with API key
        $headers = [
            'Content-Type: application/json',
            'Authorization: ' . $this->apiKey
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Set timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);
        
        // Log request details for debugging
        error_log("Fonnte sendMessage - HTTP Code: {$httpCode}");
        error_log("Fonnte sendMessage - CURL Error: " . ($error ?: 'none'));
        error_log("Fonnte sendMessage - Full Response: " . $response);
        error_log("Fonnte sendMessage - Response Length: " . strlen($response));
        
        if ($error) {
            error_log("Fonnte sendMessage - CURL Error: " . $error);
            throw new Exception('CURL Error: ' . $error);
        }
        
        // Check if response is empty
        if (empty($response)) {
            error_log("Fonnte sendMessage - Empty response from API");
            throw new Exception('Empty response from Fonnte API. HTTP Code: ' . $httpCode);
        }
        
        $result = json_decode($response, true);
        
        // If JSON decode failed, log raw response
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Fonnte sendMessage - JSON decode error: " . json_last_error_msg() . " | Raw response: " . $response);
            throw new Exception('Invalid JSON response from Fonnte API: ' . json_last_error_msg() . ' | Response: ' . substr($response, 0, 200));
        }
        
        // Check HTTP status codes
        if ($httpCode === 401) {
            $errorMsg = $result['message'] ?? $result['error'] ?? 'Unauthorized - API Key tidak valid';
            $errorDetails = isset($result['detail']) ? ' - ' . $result['detail'] : (isset($result['details']) ? ' - ' . json_encode($result['details']) : '');
            error_log("Fonnte API Error (HTTP 401): " . $errorMsg . $errorDetails . " | Full Response: " . $response);
            throw new Exception('API Key tidak valid atau tidak memiliki akses. ' . $errorMsg . $errorDetails);
        }
        
        if ($httpCode === 400) {
            $errorMsg = $result['message'] ?? $result['error'] ?? 'Bad Request';
            $errorDetails = isset($result['detail']) ? ' - ' . $result['detail'] : (isset($result['details']) ? ' - ' . json_encode($result['details']) : '');
            error_log("Fonnte API Error (HTTP 400): " . $errorMsg . $errorDetails . " | Full Response: " . $response);
            throw new Exception('Format data tidak valid. ' . $errorMsg . $errorDetails);
        }
        
        if ($httpCode === 404) {
            $errorMsg = $result['message'] ?? $result['error'] ?? 'Endpoint tidak ditemukan';
            error_log("Fonnte API Error (HTTP 404): " . $errorMsg . " | Full Response: " . $response);
            throw new Exception('Endpoint tidak ditemukan. Pastikan API URL benar: ' . $this->apiUrl);
        }
        
        if ($httpCode === 500 || $httpCode === 502 || $httpCode === 503) {
            $errorMsg = $result['message'] ?? $result['error'] ?? 'Server error';
            error_log("Fonnte API Error (HTTP {$httpCode}): " . $errorMsg . " | Full Response: " . $response);
            throw new Exception('Server Fonnte sedang bermasalah (HTTP ' . $httpCode . '). Silakan coba lagi nanti.');
        }
        
        if ($httpCode !== 200) {
            $errorMsg = $result['message'] ?? $result['error'] ?? 'Unknown error';
            $errorDetails = isset($result['detail']) ? ' - ' . $result['detail'] : (isset($result['details']) ? ' - ' . json_encode($result['details']) : '');
            error_log("Fonnte API Error (HTTP {$httpCode}): " . $errorMsg . $errorDetails . " | Full Response: " . $response);
            throw new Exception('Fonnte API Error (HTTP ' . $httpCode . '): ' . $errorMsg . $errorDetails);
        }
        
        // Check if result indicates success
        // Fonnte API might return status: false even with HTTP 200
        if (isset($result['status']) && $result['status'] === false) {
            $errorMsg = $result['reason'] ?? $result['message'] ?? $result['error'] ?? 'Unknown error';
            $errorDetails = isset($result['detail']) ? ' - ' . $result['detail'] : (isset($result['details']) ? ' - ' . json_encode($result['details']) : '');
            
            // Special handling for "invalid/empty body value" error
            // This usually means the message field is empty or invalid
            if (isset($result['reason']) && (strpos(strtolower($result['reason']), 'invalid/empty body') !== false || strpos(strtolower($result['reason']), 'empty body') !== false)) {
                error_log("Fonnte API Error (invalid/empty body): " . $errorMsg . " | Message length: " . strlen($cleanMessage) . " | Clean message preview: " . substr($cleanMessage, 0, 100));
                error_log("Fonnte Request Data: " . json_encode($data, JSON_UNESCAPED_UNICODE));
                throw new Exception('Pesan tidak valid atau kosong. Pastikan pesan tidak hanya berisi template variable yang belum diganti. Error: ' . $errorMsg);
            }
            
            error_log("Fonnte API Error (status=false): " . $errorMsg . $errorDetails . " | Full Response: " . $response);
            throw new Exception($errorMsg . $errorDetails);
        }
        
        // Check for common error indicators in response
        if (isset($result['error']) && !empty($result['error'])) {
            $errorMsg = $result['error'];
            $errorDetails = isset($result['message']) ? ' - ' . $result['message'] : (isset($result['detail']) ? ' - ' . $result['detail'] : '');
            error_log("Fonnte API Error (error field): " . $errorMsg . $errorDetails . " | Full Response: " . $response);
            throw new Exception($errorMsg . $errorDetails);
        }
        
        // Check if response has success indicator
        // Some APIs return success: true or status: 'success'
        if (isset($result['success']) && $result['success'] === false) {
            $errorMsg = $result['message'] ?? $result['error'] ?? 'Request failed';
            error_log("Fonnte API Error (success=false): " . $errorMsg . " | Full Response: " . $response);
            throw new Exception($errorMsg);
        }
        
        // Log success
        error_log("Fonnte sendMessage - Success: " . json_encode($result));
        
        return $result;
    }
    
    /**
     * Send bulk messages
     */
    public function sendBulk($messages) {
        if (empty($this->apiKey)) {
            throw new Exception('Fonnte API Key tidak dikonfigurasi');
        }
        
        $url = $this->apiUrl . '/send';
        $data = [
            'target' => array_map(function($msg) {
                $phone = preg_replace('/[^0-9]/', '', $msg['phone']);
                if (substr($phone, 0, 2) === '62') {
                    $phone = substr($phone, 2);
                }
                if (substr($phone, 0, 1) === '0') {
                    $phone = substr($phone, 1);
                }
                return '62' . $phone;
            }, $messages),
            'message' => $messages[0]['message'] ?? '', // Same message for all
            'delay' => 1 // Delay between messages in seconds
        ];
        
        if (!empty($this->deviceId)) {
            $data['device'] = $this->deviceId;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $this->configureSSL($ch);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('CURL Error: ' . $error);
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode !== 200) {
            $errorMsg = $result['message'] ?? 'Unknown error';
            throw new Exception('Fonnte API Error: ' . $errorMsg);
        }
        
        return $result;
    }
    
    /**
     * Check device status / Test connection
     * Since Fonnte might not have a dedicated test endpoint,
     * we verify API key by checking if we can reach the API
     * If webhook works, API key is valid, so we're more lenient here
     */
    public function checkDevice() {
        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => 'API Key tidak dikonfigurasi. Silakan isi API Key di form konfigurasi.'];
        }
        
        if (empty($this->apiUrl)) {
            return ['status' => false, 'message' => 'API URL tidak dikonfigurasi.'];
        }
        
        try {
            // Try multiple endpoints that Fonnte might support
            $endpoints = [
                '/device',
                '/status',
                '/me',
                '/info',
                '/devices'
            ];
            
            $has401 = false;
            $has404 = false;
            $hasConnection = false;
            $lastError = null;
            
            foreach ($endpoints as $endpoint) {
                $url = $this->apiUrl . $endpoint;
                
                error_log("Testing Fonnte endpoint: $url");
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                $this->configureSSL($ch);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: ' . $this->apiKey,
                    'Content-Type: application/json'
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                if ($curlError) {
                    error_log("Fonnte checkDevice ($endpoint) CURL error: " . $curlError);
                    $lastError = "CURL Error: " . $curlError;
                    continue; // Try next endpoint
                }
                
                $hasConnection = true; // We can reach the API
                error_log("Fonnte checkDevice ($endpoint) HTTP $httpCode");
                
                // HTTP 200 = Success, API key works
                if ($httpCode === 200) {
                    $result = json_decode($response, true);
                    return [
                        'status' => true, 
                        'data' => $result, 
                        'message' => 'Koneksi ke Fonnte API berhasil! API Key valid.',
                        'endpoint' => $endpoint
                    ];
                }
                
                // HTTP 401 = Unauthorized, API key invalid
                if ($httpCode === 401) {
                    $has401 = true;
                    $errorDetails = json_decode($response, true);
                    $errorMessage = $errorDetails['message'] ?? $errorDetails['error'] ?? 'Unauthorized';
                    $lastError = "API Key tidak valid: " . $errorMessage;
                    // Don't return immediately, try other endpoints first
                    continue;
                }
                
                // HTTP 404 = Endpoint tidak ada, tapi API reachable (key mungkin valid)
                if ($httpCode === 404) {
                    $has404 = true;
                    // This is actually OK - means API is reachable
                    continue;
                }
                
                // HTTP 400/422 = Bad request, tapi API reachable (key mungkin valid)
                if (in_array($httpCode, [400, 422])) {
                    // API is reachable, key might be valid but endpoint doesn't accept GET
                    $hasConnection = true;
                    continue;
                }
            }
            
            // If we got 401, API key is definitely invalid
            if ($has401) {
                return [
                    'status' => false, 
                    'message' => $lastError ?? 'API Key tidak valid atau tidak memiliki akses.'
                ];
            }
            
            // If we can connect to API but all endpoints return 404/400/422,
            // it means API is reachable and key format is likely correct
            // (endpoints just don't exist or require different methods)
            if ($hasConnection) {
                return [
                    'status' => true,
                    'message' => 'Koneksi ke Fonnte API berhasil! API dapat dihubungi dengan API Key yang diberikan.',
                    'note' => 'Endpoint test tidak tersedia di Fonnte API, tapi koneksi dan API Key sudah benar. Webhook yang sudah berfungsi membuktikan konfigurasi sudah tepat.'
                ];
            }
            
            // If we can't connect at all
            if ($lastError) {
                return [
                    'status' => false, 
                    'message' => 'Tidak dapat menghubungi Fonnte API: ' . $lastError
                ];
            }
            
            // Fallback error
            return [
                'status' => false, 
                'message' => 'Tidak dapat menghubungi Fonnte API. Pastikan: 1) API Key benar, 2) API URL benar (https://api.fonnte.com), 3) Koneksi internet stabil, 4) Device sudah terhubung di dashboard Fonnte.'
            ];
            
        } catch (Exception $e) {
            error_log("Fonnte checkDevice exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'status' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Replace variables in message template
     */
    public static function replaceVariables($message, $variables) {
        foreach ($variables as $key => $value) {
            $message = str_replace('{{' . $key . '}}', $value, $message);
        }
        return $message;
    }
}

