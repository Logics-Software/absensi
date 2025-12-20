<?php
require_once __DIR__ . '/../layouts/header.php';

// Helper function untuk format status
if (!function_exists('formatStatus')) {
    function formatStatus($status) {
        $statusMap = [
            'hadir' => 'Hadir',
            'alpha' => 'Alpha',
            'ijin' => 'Ijin',
            'sakit' => 'Sakit',
            'terlambat' => 'Terlambat',
            'pulang_awal' => 'Pulang Awal'
        ];
        return $statusMap[strtolower($status)] ?? ucfirst($status);
    }
}

// Helper function untuk format waktu
if (!function_exists('formatTime')) {
    function formatTime($time) {
        if (empty($time)) return '-';
        return date('H:i', strtotime($time));
    }
}

$bulanNama = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>Laporan Detail Absensi Siswa
                        </h5>
                        <a href="/laporankehadiran?kelas=<?= $siswa['idkelas'] ?? '' ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                           class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Info Siswa -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Informasi Siswa</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="150"><strong>NISN</strong></td>
                                            <td>: <?= htmlspecialchars($siswa['nisn'] ?? '-') ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nama Siswa</strong></td>
                                            <td>: <?= htmlspecialchars($siswa['namasiswa'] ?? '-') ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Periode Laporan</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="150"><strong>Bulan</strong></td>
                                            <td>: <?= $bulanNama[$bulan] ?? $bulan ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tahun</strong></td>
                                            <td>: <?= htmlspecialchars($tahun) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabel Detail Absensi -->
                    <div class="card print-report-card">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-file-alt me-2"></i>
                                Detail Absensi
                            </h6>
                            <button onclick="window.print()" class="btn btn-sm btn-light print-btn no-print">
                                <i class="fas fa-print me-1"></i>Print
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped detail-absensi-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">No.</th>
                                            <th class="text-center" style="width: 100px;">Tgl.</th>
                                            <th class="text-center" style="width: 100px;">Masuk</th>
                                            <th class="text-center" style="width: 100px;">Pulang</th>
                                            <th class="text-center" style="width: 120px;">Status</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($absensi_list)): ?>
                                            <?php 
                                            $no = 1;
                                            foreach ($absensi_list as $absensi): 
                                            ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td class="text-center"><?= date('d/m/Y', strtotime($absensi['tanggalabsen'])) ?></td>
                                                    <td class="text-center"><?= formatTime($absensi['jammasuk']) ?></td>
                                                    <td class="text-center"><?= formatTime($absensi['jamkeluar']) ?></td>
                                                    <td class="text-center">
                                                        <?php 
                                                        $status = strtolower($absensi['status'] ?? 'alpha');
                                                        $isAlpha = ($status === 'alpha' && empty($absensi['id']));
                                                        ?>
                                                        <span class="badge bg-<?php
                                                            switch($status) {
                                                                case 'hadir':
                                                                    echo 'success';
                                                                    break;
                                                                case 'terlambat':
                                                                    echo 'warning';
                                                                    break;
                                                                case 'pulang_awal':
                                                                    echo 'info';
                                                                    break;
                                                                case 'ijin':
                                                                    echo 'secondary';
                                                                    break;
                                                                case 'sakit':
                                                                    echo 'primary';
                                                                    break;
                                                                case 'alpha':
                                                                    echo 'danger';
                                                                    break;
                                                                default:
                                                                    echo 'secondary';
                                                            }
                                                        ?>">
                                                            <?= formatStatus($absensi['status'] ?? 'alpha') ?>
                                                        </span>
                                                    </td>
                                                    <td><?= htmlspecialchars($absensi['keterangan'] ?? '-') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    Tidak ada data absensi untuk periode ini.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Summary -->
                            <?php if (!empty($absensi_list)): ?>
                                <div class="mt-4">
                                    <h6>Ringkasan:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><strong>Total Hari Aktif:</strong> <?= count($tanggal_aktif) ?> hari</li>
                                                <li><strong>Total Data:</strong> <?= count($absensi_list) ?> hari</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <?php
                                            $statusCount = [];
                                            foreach ($absensi_list as $abs) {
                                                $stat = strtolower($abs['status'] ?? 'alpha');
                                                $statusCount[$stat] = ($statusCount[$stat] ?? 0) + 1;
                                            }
                                            ?>
                                            <ul class="list-unstyled">
                                                <?php if (isset($statusCount['hadir'])): ?>
                                                    <li><strong>Hadir:</strong> <?= $statusCount['hadir'] ?> hari</li>
                                                <?php endif; ?>
                                                <?php if (isset($statusCount['terlambat'])): ?>
                                                    <li><strong>Terlambat:</strong> <?= $statusCount['terlambat'] ?> hari</li>
                                                <?php endif; ?>
                                                <?php if (isset($statusCount['pulang_awal'])): ?>
                                                    <li><strong>Pulang Awal:</strong> <?= $statusCount['pulang_awal'] ?> hari</li>
                                                <?php endif; ?>
                                                <?php if (isset($statusCount['ijin'])): ?>
                                                    <li><strong>Ijin:</strong> <?= $statusCount['ijin'] ?> hari</li>
                                                <?php endif; ?>
                                                <?php if (isset($statusCount['sakit'])): ?>
                                                    <li><strong>Sakit:</strong> <?= $statusCount['sakit'] ?> hari</li>
                                                <?php endif; ?>
                                                <?php if (isset($statusCount['alpha'])): ?>
                                                    <li><strong>Alpha:</strong> <?= $statusCount['alpha'] ?> hari</li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        border-collapse: collapse !important;
    }
    
    .table th,
    .table td {
        border: 1px solid #dee2e6 !important;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

