<?php
require_once __DIR__ . '/../models/WaCampaign.php';
require_once __DIR__ . '/../models/WaMessage.php';
require_once __DIR__ . '/../models/WaTemplate.php';
require_once __DIR__ . '/../models/Mastersiswa.php';
require_once __DIR__ . '/../models/MasterGuru.php';
require_once __DIR__ . '/../models/AbsensiSiswa.php';
require_once __DIR__ . '/../services/FonnteService.php';

class WablastController extends Controller {
    public function index() {
        Auth::requireRole(['admin', 'tatausaha', 'kepalasekolah']);
        
        $campaignModel = new WaCampaign();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        $search = trim($_GET['search'] ?? '');
        $sortBy = $_GET['sort_by'] ?? 'created_at';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        
        $campaigns = $campaignModel->getAll($page, $perPage, $search, $sortBy, $sortOrder);
        $totalCampaigns = $campaignModel->count($search);
        $totalPages = ceil($totalCampaigns / $perPage);
        
        $data = [
            'campaigns' => $campaigns,
            'page' => $page,
            'perPage' => $perPage,
            'totalCampaigns' => $totalCampaigns,
            'totalPages' => $totalPages,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ];
        
        $this->view('wablast/index', $data);
    }
    
    public function create() {
        Auth::requireRole(['admin', 'tatausaha', 'kepalasekolah']);
        
        $templateModel = new WaTemplate();
        $mastersiswaModel = new Mastersiswa();
        $masterguruModel = new MasterGuru();
        
        $templates = $templateModel->getActive();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipeRecipient = $_POST['tipe_recipient'] ?? 'custom';
            $recipientIds = $_POST['recipient_ids'] ?? [];
            $templateId = !empty($_POST['template_id']) ? (int)$_POST['template_id'] : null;
            $pesan = trim($_POST['pesan'] ?? '');
            $nama = trim($_POST['nama'] ?? '');
            
            if (empty($nama)) {
                Message::error('Nama campaign wajib diisi');
                $this->redirect('/wablast/create');
            }
            
            if (empty($pesan)) {
                Message::error('Pesan wajib diisi');
                $this->redirect('/wablast/create');
            }
            
            // Get recipients based on type
            $recipients = [];
            $totalRecipient = 0;
            
            if ($tipeRecipient === 'siswa') {
                // Get all active students
                $siswaList = $mastersiswaModel->getAll(1, 10000);
                foreach ($siswaList as $siswa) {
                    if (!empty($siswa['nomorhp'])) {
                        $recipients[] = [
                            'type' => 'siswa',
                            'id' => $siswa['id'],
                            'nama' => $siswa['namasiswa'],
                            'nomor_hp' => $siswa['nomorhp']
                        ];
                        $totalRecipient++;
                    }
                }
            } elseif ($tipeRecipient === 'wali') {
                // Get all active students with wali phone
                $siswaList = $mastersiswaModel->getAll(1, 10000);
                foreach ($siswaList as $siswa) {
                    if (!empty($siswa['nomorhpwali'])) {
                        $recipients[] = [
                            'type' => 'wali',
                            'id' => $siswa['id'],
                            'nama' => $siswa['namawali'] ?: 'Wali dari ' . $siswa['namasiswa'],
                            'nomor_hp' => $siswa['nomorhpwali']
                        ];
                        $totalRecipient++;
                    }
                }
            } elseif ($tipeRecipient === 'guru') {
                // Get all active teachers
                $guruList = $masterguruModel->getAll(1, 10000);
                foreach ($guruList as $guru) {
                    if (!empty($guru['nomorhp'])) {
                        $recipients[] = [
                            'type' => 'guru',
                            'id' => $guru['id'],
                            'nama' => $guru['namaguru'],
                            'nomor_hp' => $guru['nomorhp']
                        ];
                        $totalRecipient++;
                    }
                }
            } elseif ($tipeRecipient === 'custom' && !empty($recipientIds)) {
                // Custom recipients from selected IDs
                foreach ($recipientIds as $recipientId) {
                    $parts = explode('_', $recipientId);
                    if (count($parts) === 2) {
                        $type = $parts[0];
                        $id = (int)$parts[1];
                        
                        if ($type === 'siswa') {
                            $siswa = $mastersiswaModel->findById($id);
                            if ($siswa && !empty($siswa['nomorhp'])) {
                                $recipients[] = [
                                    'type' => 'siswa',
                                    'id' => $siswa['id'],
                                    'nama' => $siswa['namasiswa'],
                                    'nomor_hp' => $siswa['nomorhp']
                                ];
                                $totalRecipient++;
                            }
                        } elseif ($type === 'wali') {
                            $siswa = $mastersiswaModel->findById($id);
                            if ($siswa && !empty($siswa['nomorhpwali'])) {
                                $recipients[] = [
                                    'type' => 'wali',
                                    'id' => $siswa['id'],
                                    'nama' => $siswa['namawali'] ?: 'Wali dari ' . $siswa['namasiswa'],
                                    'nomor_hp' => $siswa['nomorhpwali']
                                ];
                                $totalRecipient++;
                            }
                        } elseif ($type === 'guru') {
                            $guru = $masterguruModel->findById($id);
                            if ($guru && !empty($guru['nomorhp'])) {
                                $recipients[] = [
                                    'type' => 'guru',
                                    'id' => $guru['id'],
                                    'nama' => $guru['namaguru'],
                                    'nomor_hp' => $guru['nomorhp']
                                ];
                                $totalRecipient++;
                            }
                        }
                    }
                }
            }
            
            if ($totalRecipient === 0) {
                Message::error('Tidak ada penerima yang valid. Pastikan nomor HP sudah diisi.');
                $this->redirect('/wablast/create');
            }
            
            // Create campaign
            $campaignModel = new WaCampaign();
            $campaignId = $campaignModel->create([
                'nama' => $nama,
                'template_id' => $templateId,
                'pesan' => $pesan,
                'tipe_recipient' => $tipeRecipient,
                'total_recipient' => $totalRecipient,
                'status' => 'draft',
                'created_by' => $_SESSION['user_id'] ?? null
            ]);
            
            // Create messages
            $messageModel = new WaMessage();
            $messages = [];
            foreach ($recipients as $recipient) {
                $messages[] = [
                    'campaign_id' => $campaignId,
                    'recipient_type' => $recipient['type'],
                    'recipient_id' => $recipient['id'],
                    'nomor_hp' => $recipient['nomor_hp'],
                    'nama' => $recipient['nama'],
                    'pesan' => $pesan,
                    'status' => 'pending'
                ];
            }
            
            if (!empty($messages)) {
                $messageModel->createBulk($messages);
            }
            
            Message::success("Campaign berhasil dibuat dengan {$totalRecipient} penerima");
            $this->redirect('/wablast/view/' . $campaignId);
        }
        
        $data = [
            'templates' => $templates
        ];
        
        $this->view('wablast/create', $data);
    }
    
    public function viewCampaign($id) {
        Auth::requireRole(['admin', 'tatausaha', 'kepalasekolah']);
        
        $campaignModel = new WaCampaign();
        $messageModel = new WaMessage();
        
        $campaign = $campaignModel->findById($id);
        if (!$campaign) {
            Message::error('Campaign tidak ditemukan');
            $this->redirect('/wablast');
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 50;
        
        $messages = $messageModel->getByCampaign($id, $page, $perPage);
        $totalMessages = $messageModel->countByCampaign($id);
        $totalPages = ceil($totalMessages / $perPage);
        $stats = $messageModel->getStatsByCampaign($id);
        
        $data = [
            'campaign' => $campaign,
            'messages' => $messages,
            'page' => $page,
            'perPage' => $perPage,
            'totalMessages' => $totalMessages,
            'totalPages' => $totalPages,
            'stats' => $stats
        ];
        
        $this->view('wablast/view', $data);
    }
    
    public function send($id) {
        Auth::requireRole(['admin', 'tatausaha', 'kepalasekolah']);
        
        try {
            $campaignModel = new WaCampaign();
            $messageModel = new WaMessage();
            $fonnteService = new FonnteService();
            
            $campaign = $campaignModel->findById($id);
            if (!$campaign) {
                Message::error('Campaign tidak ditemukan');
                $this->redirect('/wablast');
                return;
            }
            
            if ($campaign['status'] !== 'draft' && $campaign['status'] !== 'failed') {
                Message::error('Campaign sudah dikirim atau sedang diproses');
                $this->redirect('/wablast/view/' . $id);
                return;
            }
            
            // Update campaign status
            $campaignModel->update($id, ['status' => 'sending', 'sent_at' => date('Y-m-d H:i:s')]);
            
            // Get pending messages
            $messages = $messageModel->getByCampaign($id, 1, 10000);
            $pendingMessages = array_filter($messages, function($msg) {
                return $msg['status'] === 'pending';
            });
            
            if (empty($pendingMessages)) {
                Message::error('Tidak ada pesan yang perlu dikirim');
                $campaignModel->update($id, ['status' => 'draft']);
                $this->redirect('/wablast/view/' . $id);
                return;
            }
            
            $successCount = 0;
            $failedCount = 0;
            
            // Ensure counts are integers (not null or string)
            $successCount = (int)$successCount;
            $failedCount = (int)$failedCount;
            
            foreach ($pendingMessages as $msg) {
                $errorOccurred = false;
                $errorMsg = '';
                
                try {
                    // Validate message data
                    if (empty($msg['nomor_hp'])) {
                        throw new Exception('Nomor HP tidak boleh kosong');
                    }
                    
                    if (empty($msg['pesan'])) {
                        throw new Exception('Pesan tidak boleh kosong');
                    }
                    
                    // Format nomor HP ke format Fonnte (6281234567890)
                    try {
                        $formattedPhone = FonnteService::formatPhoneNumber($msg['nomor_hp']);
                    } catch (Exception $e) {
                        throw new Exception('Format nomor HP tidak valid: ' . $e->getMessage());
                    }
                    
                    // Replace template variables in message
                    $processedMessage = $this->replaceTemplateVariables($msg['pesan'], $msg);
                    
                    // Validate processed message is not empty
                    if (empty(trim($processedMessage))) {
                        throw new Exception('Pesan menjadi kosong setelah template replacement. Pastikan template variables sudah diisi dengan nilai yang valid.');
                    }
                    
                    // Log before sending
                    error_log("WA Blast: Sending to {$formattedPhone} (Original: {$msg['nomor_hp']}, Message ID: {$msg['id']})");
                    error_log("WA Blast: Message content length: " . strlen($processedMessage));
                    
                    // Send message with processed content
                    $result = $fonnteService->sendMessage($msg['nomor_hp'], $processedMessage, 1);
                    
                    // Log response
                    error_log("WA Blast: Response for message ID {$msg['id']}: " . json_encode($result));
                    
                    // Check if result indicates success
                    $fonnteMessageId = null;
                    if (isset($result['id'])) {
                        $fonnteMessageId = $result['id'];
                    } elseif (isset($result['message_id'])) {
                        $fonnteMessageId = $result['message_id'];
                    } elseif (isset($result['status']) && $result['status'] === true) {
                        // Some APIs return status: true
                        $fonnteMessageId = $result['message_id'] ?? $result['id'] ?? null;
                    }
                    
                    // Update nomor HP ke format Fonnte di database
                    $updateResult = $messageModel->updateStatus($msg['id'], 'sent', [
                        'fonnte_message_id' => $fonnteMessageId,
                        'status_message' => 'Pesan berhasil dikirim',
                        'nomor_hp' => $formattedPhone // Update nomor HP ke format Fonnte
                    ]);
                    
                    if (!$updateResult) {
                        error_log("Warning: Failed to update message status to 'sent' for message ID {$msg['id']}");
                    }
                    
                    $successCount++;
                    
                    // Small delay to avoid rate limiting
                    usleep(500000); // 0.5 second delay
                    
                } catch (Exception $e) {
                    $errorOccurred = true;
                    $errorMsg = $e->getMessage();
                    $errorClass = get_class($e);
                    
                    // Log detailed error
                    error_log("WA Blast Send Error for message ID {$msg['id']} (Phone: {$msg['nomor_hp']}): " . $errorMsg);
                    error_log("Error Class: " . $errorClass);
                    error_log("Stack trace: " . $e->getTraceAsString());
                    
                    // Ensure error message is not empty
                    if (empty(trim($errorMsg))) {
                        $errorMsg = 'Terjadi kesalahan saat mengirim pesan. Silakan cek log untuk detail.';
                    }
                    
                    // Update status with error message
                    $updateResult = $messageModel->updateStatus($msg['id'], 'failed', [
                        'error_message' => $errorMsg,
                        'status_message' => 'Gagal mengirim pesan'
                    ]);
                    
                    // Log if update failed
                    if (!$updateResult) {
                        error_log("CRITICAL: Failed to update message status for message ID {$msg['id']}");
                        // Try direct update as fallback
                        try {
                            $db = Database::getInstance();
                            $db->query(
                                "UPDATE wa_messages SET status = 'failed', error_message = ?, status_message = 'Gagal mengirim pesan' WHERE id = ?",
                                [$errorMsg, $msg['id']]
                            );
                            error_log("Fallback update successful for message ID {$msg['id']}");
                        } catch (Exception $updateEx) {
                            error_log("CRITICAL: Fallback update also failed for message ID {$msg['id']}: " . $updateEx->getMessage());
                        }
                    }
                    
                    $failedCount++;
                } catch (Error $e) {
                    // Catch fatal errors (PHP 7+)
                    $errorOccurred = true;
                    $errorMsg = 'Fatal Error: ' . $e->getMessage();
                    error_log("WA Blast Fatal Error for message ID {$msg['id']} (Phone: {$msg['nomor_hp']}): " . $errorMsg);
                    error_log("Stack trace: " . $e->getTraceAsString());
                    
                    $updateResult = $messageModel->updateStatus($msg['id'], 'failed', [
                        'error_message' => $errorMsg,
                        'status_message' => 'Fatal error saat mengirim pesan'
                    ]);
                    
                    if (!$updateResult) {
                        error_log("CRITICAL: Failed to update message status for fatal error, message ID {$msg['id']}");
                    }
                    
                    $failedCount++;
                } catch (Throwable $e) {
                    // Catch any other throwable (PHP 7+)
                    $errorOccurred = true;
                    $errorMsg = 'Unexpected Error: ' . $e->getMessage();
                    error_log("WA Blast Unexpected Error for message ID {$msg['id']} (Phone: {$msg['nomor_hp']}): " . $errorMsg);
                    error_log("Error Class: " . get_class($e));
                    error_log("Stack trace: " . $e->getTraceAsString());
                    
                    $updateResult = $messageModel->updateStatus($msg['id'], 'failed', [
                        'error_message' => $errorMsg,
                        'status_message' => 'Unexpected error saat mengirim pesan'
                    ]);
                    
                    if (!$updateResult) {
                        error_log("CRITICAL: Failed to update message status for unexpected error, message ID {$msg['id']}");
                    }
                    
                    $failedCount++;
                }
            }
            
            // Determine final campaign status
            // - completed: jika ada yang berhasil (meskipun ada yang gagal)
            // - failed: jika semua gagal
            $finalStatus = 'completed';
            if ($successCount === 0 && $failedCount > 0) {
                $finalStatus = 'failed';
            } elseif ($successCount > 0 && $failedCount === 0) {
                $finalStatus = 'completed';
            } elseif ($successCount > 0 && $failedCount > 0) {
                $finalStatus = 'completed'; // Ada yang berhasil, jadi completed
            } elseif ($successCount === 0 && $failedCount === 0) {
                $finalStatus = 'draft'; // Tidak ada yang diproses
            }
            
            // Log before update
            error_log("WA Blast: Updating campaign status. Success: {$successCount}, Failed: {$failedCount}, Final Status: {$finalStatus}");
            
            // Ensure counts are integers before update
            $successCount = (int)$successCount;
            $failedCount = (int)$failedCount;
            
            // Update campaign status
            $updateResult = $campaignModel->update($id, [
                'status' => $finalStatus,
                'total_sent' => $successCount,
                'total_failed' => $failedCount
            ]);
            
            // Log if update failed
            if (!$updateResult) {
                error_log("CRITICAL: Failed to update campaign status for campaign ID {$id}");
                // Try direct update as fallback
                try {
                    $db = Database::getInstance();
                    $db->query(
                        "UPDATE wa_campaigns SET status = ?, total_sent = ?, total_failed = ? WHERE id = ?",
                        [$finalStatus, $successCount, $failedCount, $id]
                    );
                    error_log("Fallback campaign update successful for campaign ID {$id}");
                } catch (Exception $updateEx) {
                    error_log("CRITICAL: Fallback campaign update also failed for campaign ID {$id}: " . $updateEx->getMessage());
                }
            } else {
                error_log("Campaign status updated successfully for campaign ID {$id}: {$finalStatus}");
            }
            
            // Show appropriate message
            if ($successCount > 0) {
                if ($failedCount > 0) {
                    Message::warning("Campaign selesai. {$successCount} pesan berhasil, {$failedCount} gagal");
                } else {
                    Message::success("Campaign berhasil dikirim. Semua {$successCount} pesan berhasil terkirim.");
                }
            } else {
                Message::error("Campaign gagal dikirim. Semua {$failedCount} pesan gagal terkirim.");
            }
            
            $this->redirect('/wablast/view/' . $id);
            
        } catch (Exception $e) {
            // Log error
            error_log("WA Blast Send Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Update campaign status to failed if still in sending state
            try {
                $campaignModel = new WaCampaign();
                $currentCampaign = $campaignModel->findById($id);
                if ($currentCampaign && $currentCampaign['status'] === 'sending') {
                    // Get actual stats from messages
                    $messageModel = new WaMessage();
                    $stats = $messageModel->getStatsByCampaign($id);
                    
                    $finalStatus = 'failed';
                    if ($stats && $stats['sent'] > 0) {
                        $finalStatus = 'completed';
                    }
                    
                    $campaignModel->update($id, [
                        'status' => $finalStatus,
                        'total_sent' => $stats['sent'] ?? 0,
                        'total_failed' => $stats['failed'] ?? 0
                    ]);
                    
                    error_log("Campaign status updated to {$finalStatus} after exception for campaign ID {$id}");
                }
            } catch (Exception $updateEx) {
                error_log("Failed to update campaign status after exception: " . $updateEx->getMessage());
            }
            
            Message::error('Terjadi kesalahan saat mengirim campaign: ' . $e->getMessage());
            $this->redirect('/wablast/view/' . $id);
        }
    }
    
    /**
     * Resend failed message
     */
    public function resend($campaignId, $messageId) {
        Auth::requireRole(['admin', 'tatausaha', 'kepalasekolah']);
        
        try {
            $campaignModel = new WaCampaign();
            $messageModel = new WaMessage();
            $fonnteService = new FonnteService();
            
            $campaign = $campaignModel->findById($campaignId);
            if (!$campaign) {
                Message::error('Campaign tidak ditemukan');
                $this->redirect('/wablast');
                return;
            }
            
            $message = $messageModel->findById($messageId);
            if (!$message) {
                Message::error('Pesan tidak ditemukan');
                $this->redirect('/wablast/view/' . $campaignId);
                return;
            }
            
            if ($message['campaign_id'] != $campaignId) {
                Message::error('Pesan tidak sesuai dengan campaign');
                $this->redirect('/wablast/view/' . $campaignId);
                return;
            }
            
            if ($message['status'] !== 'failed') {
                Message::error('Hanya pesan yang gagal yang dapat dikirim ulang');
                $this->redirect('/wablast/view/' . $campaignId);
                return;
            }
            
            // Format nomor HP ke format Fonnte (6281234567890)
            $formattedPhone = FonnteService::formatPhoneNumber($message['nomor_hp']);
            
            // Replace template variables in message
            $processedMessage = $this->replaceTemplateVariables($message['pesan'], $message);
            
            // Validate processed message is not empty
            if (empty(trim($processedMessage))) {
                throw new Exception('Pesan menjadi kosong setelah template replacement. Pastikan template variables sudah diisi dengan nilai yang valid.');
            }
            
            // Log before resending
            error_log("WA Blast Resend: Message ID {$messageId}, Phone: {$formattedPhone} (Original: {$message['nomor_hp']}), Campaign ID: {$campaignId}");
            error_log("WA Blast Resend: Message content: " . substr($processedMessage, 0, 200));
            
            // Reset status to pending
            $messageModel->updateStatus($messageId, 'pending', [
                'error_message' => null,
                'status_message' => 'Mengirim ulang...'
            ]);
            
            // Try to send with processed message
            try {
                $result = $fonnteService->sendMessage($message['nomor_hp'], $processedMessage, 1);
                
                // Log response
                error_log("WA Blast Resend: Response for message ID {$messageId}: " . json_encode($result));
                
                // Check if result indicates success
                $fonnteMessageId = null;
                if (isset($result['id'])) {
                    $fonnteMessageId = $result['id'];
                } elseif (isset($result['message_id'])) {
                    $fonnteMessageId = $result['message_id'];
                } elseif (isset($result['status']) && $result['status'] === true) {
                    $fonnteMessageId = $result['message_id'] ?? $result['id'] ?? null;
                }
                
                // Update nomor HP ke format Fonnte di database
                $messageModel->updateStatus($messageId, 'sent', [
                    'fonnte_message_id' => $fonnteMessageId,
                    'status_message' => 'Pesan berhasil dikirim ulang',
                    'error_message' => null,
                    'nomor_hp' => $formattedPhone // Update nomor HP ke format Fonnte
                ]);
                
                // Update campaign stats
                $stats = $messageModel->getStatsByCampaign($campaignId);
                $campaignModel->update($campaignId, [
                    'total_sent' => $stats['sent'] ?? 0,
                    'total_failed' => $stats['failed'] ?? 0
                ]);
                
                Message::success('Pesan berhasil dikirim ulang');
                
            } catch (Exception $e) {
                $errorMsg = $e->getMessage();
                error_log("WA Blast Resend Error for message ID {$messageId} (Phone: {$message['nomor_hp']}): " . $errorMsg);
                error_log("Stack trace: " . $e->getTraceAsString());
                
                $messageModel->updateStatus($messageId, 'failed', [
                    'error_message' => $errorMsg,
                    'status_message' => 'Gagal mengirim ulang pesan'
                ]);
                
                Message::error('Gagal mengirim ulang pesan: ' . $errorMsg);
            }
            
            $this->redirect('/wablast/view/' . $campaignId);
            
        } catch (Exception $e) {
            error_log("WA Blast Resend Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            Message::error('Terjadi kesalahan saat mengirim ulang pesan: ' . $e->getMessage());
            $this->redirect('/wablast/view/' . $campaignId);
        }
    }
    
    public function delete($id) {
        Auth::requireRole(['admin']);
        
        $campaignModel = new WaCampaign();
        $campaign = $campaignModel->findById($id);
        
        if (!$campaign) {
            Message::error('Campaign tidak ditemukan');
            $this->redirect('/wablast');
        }
        
        if ($campaign['status'] === 'sending') {
            Message::error('Tidak dapat menghapus campaign yang sedang dikirim');
            $this->redirect('/wablast/view/' . $id);
        }
        
        if ($campaignModel->delete($id)) {
            Message::success('Campaign berhasil dihapus');
        } else {
            Message::error('Gagal menghapus campaign');
        }
        
        $this->redirect('/wablast');
    }
    
    /**
     * API endpoint to get list of students and teachers for custom selection
     */
    public function apiGetRecipients() {
        Auth::requireRole(['admin', 'tatausaha', 'kepalasekolah']);
        
        $mastersiswaModel = new Mastersiswa();
        $masterguruModel = new MasterGuru();
        
        $status = $_GET['status'] ?? 'aktif';
        
        // Get all students (with large limit)
        $siswaList = $mastersiswaModel->getAll(1, 10000, '', 'id', 'ASC', null, null);
        $guruList = $masterguruModel->getAll(1, 10000, '', 'id', 'ASC');
        
        // Filter by status if needed
        if ($status === 'aktif') {
            $siswaList = array_filter($siswaList, function($s) {
                return ($s['status'] ?? '') === 'aktif';
            });
            $guruList = array_filter($guruList, function($g) {
                return ($g['status'] ?? '') === 'aktif';
            });
        }
        
        // Only return students and teachers with phone numbers
        $siswaList = array_filter($siswaList, function($s) {
            return !empty($s['nomorhp']) || !empty($s['nomorhpwali']);
        });
        $guruList = array_filter($guruList, function($g) {
            return !empty($g['nomorhp']);
        });
        
        header('Content-Type: application/json');
        echo json_encode([
            'siswa' => array_values($siswaList),
            'guru' => array_values($guruList)
        ]);
        exit;
    }
    
    /**
     * Replace template variables in message with actual values
     * @param string $message The message template with variables like {{nama}}, {{nama_siswa}}, etc.
     * @param array $msgData Message data from database (includes recipient info)
     * @return string Processed message with variables replaced
     */
    private function replaceTemplateVariables($message, $msgData) {
        $mastersiswaModel = new Mastersiswa();
        $masterguruModel = new MasterGuru();
        $absensiSiswaModel = new AbsensiSiswa();
        
        $replacements = [];
        
        // Basic replacements
        $replacements['{{nama}}'] = $msgData['nama'] ?? '';
        
        // Get tanggal for absensi lookup (use today's date)
        $tanggal = date('Y-m-d');
        
        // Get additional data based on recipient type
        $siswa = null;
        if ($msgData['recipient_type'] === 'siswa') {
            $siswa = $mastersiswaModel->findById($msgData['recipient_id']);
            if ($siswa) {
                $replacements['{{nama_siswa}}'] = $siswa['namasiswa'] ?? '';
            }
        } elseif ($msgData['recipient_type'] === 'wali') {
            $siswa = $mastersiswaModel->findById($msgData['recipient_id']);
            if ($siswa) {
                $replacements['{{nama_siswa}}'] = $siswa['namasiswa'] ?? '';
            }
        } elseif ($msgData['recipient_type'] === 'guru') {
            $guru = $masterguruModel->findById($msgData['recipient_id']);
            if ($guru) {
                $replacements['{{nama_siswa}}'] = ''; // Guru doesn't have siswa
            }
        }
        
        // Date replacements
        $replacements['{{tanggal}}'] = date('d/m/Y');
        $replacements['{{tanggal_lengkap}}'] = date('d F Y');
        $replacements['{{hari}}'] = date('l'); // Day name in English
        $replacements['{{hari_indonesia}}'] = $this->getIndonesianDayName(date('w')); // 0=Sunday, 6=Saturday
        
        // Get absensi data for jam masuk and jam keluar
        $jamMasuk = date('H:i'); // Default to current time
        $jamKeluar = date('H:i'); // Default to current time
        $status = 'Hadir'; // Default status
        
        if ($siswa && !empty($siswa['nisn'])) {
            // Get absensi data from table absensi_siswa
            $absensi = $absensiSiswaModel->getByNisnAndDate($siswa['nisn'], $tanggal);
            if ($absensi) {
                // Format jam masuk (from time format to H:i)
                if (!empty($absensi['jammasuk'])) {
                    // jammasuk might be in format 'HH:MM:SS' or 'HH:MM'
                    $jamMasukTime = strtotime($absensi['jammasuk']);
                    if ($jamMasukTime !== false) {
                        $jamMasuk = date('H:i', $jamMasukTime);
                    } else {
                        // Try direct format if already in H:i
                        $jamMasuk = substr($absensi['jammasuk'], 0, 5);
                    }
                }
                
                // Format jam keluar (from time format to H:i)
                if (!empty($absensi['jamkeluar'])) {
                    // jamkeluar might be in format 'HH:MM:SS' or 'HH:MM'
                    $jamKeluarTime = strtotime($absensi['jamkeluar']);
                    if ($jamKeluarTime !== false) {
                        $jamKeluar = date('H:i', $jamKeluarTime);
                    } else {
                        // Try direct format if already in H:i
                        $jamKeluar = substr($absensi['jamkeluar'], 0, 5);
                    }
                }
                
                // Get status from absensi
                if (!empty($absensi['status'])) {
                    $status = ucfirst(strtolower($absensi['status'])); // Capitalize first letter
                }
            }
        }
        
        // Time replacements (from absensi data)
        $replacements['{{jam_masuk}}'] = $jamMasuk;
        $replacements['{{jam_keluar}}'] = $jamKeluar;
        
        // Status replacement (from absensi data)
        $replacements['{{status}}'] = $status;
        
        // Replace all template variables
        $processedMessage = $message;
        foreach ($replacements as $variable => $value) {
            $processedMessage = str_replace($variable, $value, $processedMessage);
        }
        
        // Remove any remaining template variables (in case some weren't replaced)
        $processedMessage = preg_replace('/\{\{[^}]+\}\}/', '', $processedMessage);
        
        return $processedMessage;
    }
    
    /**
     * Get Indonesian day name
     */
    private function getIndonesianDayName($dayNumber) {
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu'
        ];
        return $days[$dayNumber] ?? '';
    }
}

