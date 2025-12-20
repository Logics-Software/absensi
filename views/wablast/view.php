<?php
$title = 'Detail WA Blast Campaign';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
}

// Initialize variables with defaults if not set
$page = isset($page) ? (int)$page : 1;
$perPage = isset($perPage) ? (int)$perPage : 50;
$totalMessages = isset($totalMessages) ? (int)$totalMessages : 0;
$totalPages = isset($totalPages) ? (int)$totalPages : 1;
$messages = isset($messages) ? $messages : [];
$campaign = isset($campaign) ? $campaign : [];
$stats = isset($stats) ? $stats : [];

// Ensure page is at least 1
if ($page < 1) {
    $page = 1;
}

// Ensure perPage is at least 1
if ($perPage < 1) {
    $perPage = 50;
}

require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/wablast">WA Blast</a></li>
                    <li class="breadcrumb-item active">Detail Campaign</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">Detail Campaign: <?= htmlspecialchars($campaign['nama']) ?></h4>
                        <div>
                            <?php if ($campaign['status'] === 'draft' || $campaign['status'] === 'failed'): ?>
                            <form method="POST" action="/wablast/send/<?= $campaign['id'] ?>" style="display: inline;" 
                                  onsubmit="return confirm('Yakin ingin mengirim campaign ini ke <?= number_format($campaign['total_recipient']) ?> penerima?');">
                                <button type="submit" class="btn btn-success">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="margin-right: 0.5rem;">
                                        <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm-1.833-1.809L13.927 1.376 2.37 6.862l2.433 1.399Z"/>
                                    </svg>
                                    Kirim Campaign
                                </button>
                            </form>
                            <?php endif; ?>
                            <a href="/wablast" class="btn btn-secondary">Kembali</a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Campaign Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Nama Campaign:</th>
                                    <td><?= htmlspecialchars($campaign['nama']) ?></td>
                                </tr>
                                <tr>
                                    <th>Template:</th>
                                    <td><?= htmlspecialchars($campaign['template_nama'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Tipe Penerima:</th>
                                    <td>
                                        <?php
                                        $tipeLabels = [
                                            'siswa' => 'Siswa',
                                            'wali' => 'Wali Siswa',
                                            'guru' => 'Guru',
                                            'custom' => 'Custom'
                                        ];
                                        echo $tipeLabels[$campaign['tipe_recipient']] ?? $campaign['tipe_recipient'];
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'scheduled' => 'info',
                                            'sending' => 'warning',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                            'cancelled' => 'dark'
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Draft',
                                            'scheduled' => 'Terjadwal',
                                            'sending' => 'Mengirim',
                                            'completed' => 'Selesai',
                                            'failed' => 'Gagal',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        $color = $statusColors[$campaign['status']] ?? 'secondary';
                                        $label = $statusLabels[$campaign['status']] ?? $campaign['status'];
                                        ?>
                                        <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Total Penerima:</th>
                                    <td><?= number_format($campaign['total_recipient']) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Terkirim:</th>
                                    <td><?= number_format($campaign['total_sent']) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Delivered:</th>
                                    <td><?= number_format($campaign['total_delivered']) ?></td>
                                </tr>
                                <tr>
                                    <th>Total Gagal:</th>
                                    <td><?= number_format($campaign['total_failed']) ?></td>
                                </tr>
                                <tr>
                                    <th>Dibuat:</th>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($campaign['created_at'])) ?>
                                        <br>
                                        <small class="text-muted">oleh <?= htmlspecialchars($campaign['created_by_name'] ?? '-') ?></small>
                                    </td>
                                </tr>
                                <?php if ($campaign['sent_at']): ?>
                                <tr>
                                    <th>Dikirim:</th>
                                    <td><?= date('d/m/Y H:i', strtotime($campaign['sent_at'])) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Message Preview -->
                    <div class="mb-4">
                        <h5>Preview Pesan</h5>
                        <div class="border rounded p-3 bg-light">
                            <pre style="white-space: pre-wrap; font-family: inherit; margin: 0;"><?= htmlspecialchars($campaign['pesan']) ?></pre>
                        </div>
                    </div>
                    
                    <!-- Statistics -->
                    <?php if ($stats && $stats['total'] > 0): ?>
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-primary"><?= number_format($stats['total']) ?></h3>
                                    <p class="mb-0">Total Pesan</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-success"><?= number_format($stats['sent'] ?? 0) ?></h3>
                                    <p class="mb-0">Terkirim</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-info"><?= number_format($stats['delivered'] ?? 0) ?></h3>
                                    <p class="mb-0">Delivered</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-danger"><?= number_format($stats['failed'] ?? 0) ?></h3>
                                    <p class="mb-0">Gagal</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Messages List -->
                    <h5>Daftar Pesan</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Nomor HP</th>
                                    <th>Tipe</th>
                                    <th>Status</th>
                                    <th>Error Message</th>
                                    <th style="min-width: 100px;">Debug Info</th>
                                    <th>Waktu</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada pesan</td>
                                </tr>
                                <?php else: ?>
                                <?php 
                                // Calculate starting number for pagination
                                $no = max(1, ($page - 1) * $perPage + 1);
                                ?>
                                <?php foreach ($messages as $message): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($message['nama'] ?? '-') ?></td>
                                    <td>
                                        <code><?= htmlspecialchars($message['nomor_hp']) ?></code>
                                        <?php if ($message['fonnte_message_id']): ?>
                                        <br><small class="text-muted">ID: <?= htmlspecialchars($message['fonnte_message_id']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $tipeLabels = [
                                            'siswa' => 'Siswa',
                                            'wali' => 'Wali',
                                            'guru' => 'Guru',
                                            'custom' => 'Custom'
                                        ];
                                        echo $tipeLabels[$message['recipient_type']] ?? $message['recipient_type'];
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'secondary',
                                            'sent' => 'info',
                                            'delivered' => 'success',
                                            'read' => 'primary',
                                            'failed' => 'danger'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pending',
                                            'sent' => 'Terkirim',
                                            'delivered' => 'Delivered',
                                            'read' => 'Dibaca',
                                            'failed' => 'Gagal'
                                        ];
                                        $color = $statusColors[$message['status']] ?? 'secondary';
                                        $label = $statusLabels[$message['status']] ?? $message['status'];
                                        ?>
                                        <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                        <?php if ($message['status_message']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($message['status_message']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="max-width: 400px;">
                                        <?php if ($message['error_message']): ?>
                                        <div class="text-danger small" style="word-wrap: break-word; white-space: normal;">
                                            <strong>⚠️ Error:</strong><br>
                                            <code style="font-size: 0.85em; background: #f8d7da; padding: 2px 4px; border-radius: 3px;">
                                                <?= htmlspecialchars($message['error_message']) ?>
                                            </code>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="white-space: nowrap; min-width: 100px;">
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#debugModal<?= $message['id'] ?>" style="min-width: 90px; font-weight: 500;">
                                            <span style="margin-right: 4px;">ℹ️</span> Debug
                                        </button>
                                        
                                        <!-- Debug Modal -->
                                        <div class="modal fade" id="debugModal<?= $message['id'] ?>" tabindex="-1" aria-labelledby="debugModalLabel<?= $message['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="debugModalLabel<?= $message['id'] ?>">Debug Info - <?= htmlspecialchars($message['nama'] ?? '-') ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <strong>Nomor HP Tujuan:</strong>
                                                            <div class="mt-1">
                                                                <code class="bg-light p-2 rounded d-inline-block"><?= htmlspecialchars($message['nomor_hp']) ?></code>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <strong>Pesan yang Dikirim:</strong>
                                                            <div class="mt-1 border rounded p-3 bg-light">
                                                                <pre style="white-space: pre-wrap; font-family: inherit; margin: 0; max-height: 300px; overflow-y: auto;"><?= htmlspecialchars($message['pesan']) ?></pre>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if ($message['fonnte_message_id']): ?>
                                                        <div class="mb-3">
                                                            <strong>Fonnte Message ID:</strong>
                                                            <div class="mt-1">
                                                                <code class="bg-light p-2 rounded d-inline-block"><?= htmlspecialchars($message['fonnte_message_id']) ?></code>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($message['error_message']): ?>
                                                        <div class="mb-3">
                                                            <strong>Error Message:</strong>
                                                            <div class="mt-1 alert alert-danger">
                                                                <?= htmlspecialchars($message['error_message']) ?>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($message['status_message']): ?>
                                                        <div class="mb-3">
                                                            <strong>Status Message:</strong>
                                                            <div class="mt-1">
                                                                <?= htmlspecialchars($message['status_message']) ?>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <strong>Status:</strong>
                                                                <div class="mt-1">
                                                                    <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>Tipe Penerima:</strong>
                                                                <div class="mt-1">
                                                                    <?= $tipeLabels[$message['recipient_type']] ?? $message['recipient_type'] ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if ($message['sent_at']): ?>
                                                        <div class="mt-3">
                                                            <strong>Waktu Terkirim:</strong>
                                                            <div class="mt-1">
                                                                <?= date('d/m/Y H:i:s', strtotime($message['sent_at'])) ?>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($message['sent_at']): ?>
                                        Terkirim: <?= date('d/m/Y H:i', strtotime($message['sent_at'])) ?>
                                        <?php endif; ?>
                                        <?php if ($message['delivered_at']): ?>
                                        <br>Delivered: <?= date('d/m/Y H:i', strtotime($message['delivered_at'])) ?>
                                        <?php endif; ?>
                                        <?php if ($message['read_at']): ?>
                                        <br>Dibaca: <?= date('d/m/Y H:i', strtotime($message['read_at'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($message['status'] === 'failed'): ?>
                                        <form method="POST" action="/wablast/resend/<?= $campaign['id'] ?>/<?= $message['id'] ?>" style="display: inline;" 
                                              onsubmit="return confirm('Yakin ingin mengirim ulang pesan ke <?= htmlspecialchars($message['nomor_hp']) ?>?');">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Kirim Ulang">
                                                🔄 Kirim Ulang
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

