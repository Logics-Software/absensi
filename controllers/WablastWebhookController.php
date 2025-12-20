<?php
require_once __DIR__ . '/../models/WaMessage.php';

class WablastWebhookController extends Controller {
    /**
     * Handle webhook from Fonnte for message status updates
     * This endpoint should be publicly accessible (no auth required)
     * but should verify the request comes from Fonnte
     */
    public function handle() {
        // Webhook tidak perlu authentication karena dipanggil oleh Fonnte
        // Skip auth check untuk endpoint ini
        
        // Get raw input
        $rawInput = file_get_contents('php://input');
        
        // Log webhook for debugging
        error_log("=== Fonnte Webhook Debug ===");
        error_log("Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set'));
        error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
        error_log("Raw Input: " . ($rawInput ?: '(empty)'));
        error_log("POST data: " . print_r($_POST, true));
        error_log("GET data: " . print_r($_GET, true));
        
        // Handle GET request (verification or test)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            error_log("GET request received - returning success for verification");
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Webhook endpoint is active']);
            exit;
        }
        
        // Try to parse JSON
        $data = null;
        $jsonError = null;
        
        if (!empty($rawInput)) {
            $data = json_decode($rawInput, true);
            $jsonError = json_last_error();
            
            if ($jsonError !== JSON_ERROR_NONE) {
                error_log("JSON decode error: " . json_last_error_msg() . " (code: $jsonError)");
                error_log("Raw input (first 500 chars): " . substr($rawInput, 0, 500));
            }
        }
        
        // If JSON parsing failed, try to get data from POST
        if (!$data && !empty($_POST)) {
            $data = $_POST;
            error_log("Using POST data instead of JSON");
        }
        
        // If still no data, check if it's form-urlencoded
        if (!$data && !empty($rawInput)) {
            parse_str($rawInput, $parsedData);
            if (!empty($parsedData)) {
                $data = $parsedData;
                error_log("Using parsed form-urlencoded data");
            }
        }
        
        // If still no data, return error with details
        if (!$data) {
            error_log("No data received in webhook");
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Invalid JSON or no data received',
                'details' => [
                    'method' => $_SERVER['REQUEST_METHOD'] ?? 'not set',
                    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
                    'raw_input_length' => strlen($rawInput ?? ''),
                    'raw_input_preview' => substr($rawInput ?? '', 0, 200),
                    'json_error' => $jsonError ? json_last_error_msg() : null
                ]
            ]);
            exit;
        }
        
        error_log("Fonnte Webhook parsed data: " . print_r($data, true));
        
        try {
            $messageModel = new WaMessage();
            
            // Handle different webhook event types from Fonnte
            // Format may vary depending on Fonnte API documentation
            
            // Example: Update message status based on webhook data
            if (isset($data['id']) || isset($data['message_id']) || isset($data['phone']) || isset($data['target'])) {
                $messageId = $data['id'] ?? $data['message_id'] ?? null;
                $status = $data['status'] ?? $data['state'] ?? null;
                $phoneNumber = $data['phone'] ?? $data['target'] ?? $data['phone_number'] ?? null;
                
                error_log("Processing webhook - messageId: $messageId, status: $status, phone: $phoneNumber");
                
                // Find message by fonnte_message_id or phone number
                $message = null;
                if ($messageId) {
                    $sql = "SELECT * FROM wa_messages WHERE fonnte_message_id = ? LIMIT 1";
                    $message = $this->db->fetchOne($sql, [$messageId]);
                    error_log("Found message by ID: " . ($message ? 'yes' : 'no'));
                }
                
                if (!$message && $phoneNumber) {
                    // Normalize phone number
                    $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
                    if (substr($phoneNumber, 0, 2) === '62') {
                        $phoneNumber = substr($phoneNumber, 2);
                    }
                    if (substr($phoneNumber, 0, 1) === '0') {
                        $phoneNumber = substr($phoneNumber, 1);
                    }
                    $phoneNumber = '62' . $phoneNumber;
                    
                    $sql = "SELECT * FROM wa_messages WHERE nomor_hp LIKE ? AND status = 'sent' ORDER BY created_at DESC LIMIT 1";
                    $message = $this->db->fetchOne($sql, ['%' . $phoneNumber]);
                    error_log("Found message by phone: " . ($message ? 'yes' : 'no'));
                }
                
                if ($message) {
                    // Map Fonnte status to our status
                    $newStatus = $this->mapFonnteStatus($status);
                    
                    if ($newStatus) {
                        $updateData = [];
                        
                        if ($newStatus === 'delivered') {
                            $updateData['delivered_at'] = date('Y-m-d H:i:s');
                        } elseif ($newStatus === 'read') {
                            $updateData['read_at'] = date('Y-m-d H:i:s');
                        } elseif ($newStatus === 'failed') {
                            $updateData['error_message'] = $data['error'] ?? $data['message'] ?? 'Unknown error';
                        }
                        
                        $messageModel->updateStatus($message['id'], $newStatus, $updateData);
                        
                        error_log("Updated message ID {$message['id']} to status: {$newStatus}");
                    } else {
                        error_log("Status mapping failed for: " . ($status ?? 'null'));
                    }
                } else {
                    error_log("No message found for webhook data");
                }
            } else {
                error_log("Webhook data doesn't contain expected fields");
            }
            
            // Return success response
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Webhook processed']);
            
        } catch (Exception $e) {
            error_log("Webhook error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Map Fonnte status to our internal status
     */
    private function mapFonnteStatus($fonnteStatus) {
        $statusMap = [
            'sent' => 'sent',
            'delivered' => 'delivered',
            'read' => 'read',
            'failed' => 'failed',
            'error' => 'failed',
            'pending' => 'pending',
            'queued' => 'pending',
            'sending' => 'pending'
        ];
        
        $status = strtolower($fonnteStatus ?? '');
        return $statusMap[$status] ?? null;
    }
}
