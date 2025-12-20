<?php
require_once __DIR__ . '/../layouts/header.php';

// Helper function untuk get status class
if (!function_exists('getStatusClass')) {
    function getStatusClass($code) {
        $classMap = [
            'H' => 'status-h',
            'I' => 'status-i',
            'T' => 'status-t',
            'A' => 'status-a',
            'P' => 'status-p',
            'S' => 'status-s'
        ];
        return $classMap[$code] ?? '';
    }
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Laporan Kehadiran Per Bulan Per Kelas
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="card mb-4 no-print">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Laporan</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="/laporankehadiran" class="row g-3">
                                <div class="col-md-4">
                                    <label for="kelas" class="form-label">Kelas</label>
                                    <select class="form-select" id="kelas" name="kelas" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        <?php foreach ($kelas_list as $kls): ?>
                                            <option value="<?= $kls['idkelas'] ?>" <?= ($id_kelas == $kls['idkelas']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kls['namakelas']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="bulan" class="form-label">Bulan</label>
                                    <select class="form-select" id="bulan" name="bulan" required>
                                        <?php
                                        $bulanNama = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
                                        foreach ($bulanNama as $val => $nama):
                                        ?>
                                            <option value="<?= $val ?>" <?= ($bulan == $val) ? 'selected' : '' ?>>
                                                <?= $nama ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <input type="number" class="form-control" id="tahun" name="tahun" 
                                           value="<?= htmlspecialchars($tahun) ?>" min="2020" max="2099" required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-filter btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Tampilkan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Laporan -->
                    <?php if ($laporan): ?>
                        <div class="card print-report-card">
                            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Laporan Kehadiran - <?= htmlspecialchars($laporan['kelas']['namakelas']) ?> 
                                    (<?php 
                                    $bulanNama = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
                                    echo $bulanNama[$bulan] ?? date('F'); 
                                    ?> <?= $tahun ?>)</h6>
                                <button onclick="window.print()" class="btn btn-sm btn-light print-btn no-print">
                                    <i class="fas fa-print me-1"></i>Print
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive print-table-wrapper">
                                    <table class="table table-bordered table-sm print-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th rowspan="2" class="laporan-table-header laporan-table-header-no">No.</th>
                                                <th rowspan="2" class="laporan-table-header laporan-table-header-nisn">NISN</th>
                                                <th rowspan="2" class="laporan-table-header laporan-table-header-nama">Nama Siswa</th>
                                                <th colspan="<?= count($laporan['tanggal_aktif']) ?>" class="laporan-table-header">
                                                    Tanggal
                                                </th>
                                            </tr>
                                            <tr>
                                                <?php foreach ($laporan['tanggal_aktif'] as $tanggal): ?>
                                                    <th class="laporan-table-header laporan-table-header-tanggal">
                                                        <?= date('d', strtotime($tanggal)) ?>
                                                    </th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if (!empty($laporan['siswa_list'])):
                                                $no = 1;
                                                foreach ($laporan['siswa_list'] as $siswa): 
                                                    if (empty($siswa['nisn'])) continue; // Skip jika tidak ada nisn
                                            ?>
                                                <tr>
                                                    <td class="laporan-table-cell-center"><?= $no++ ?></td>
                                                    <td class="laporan-table-cell-center">
                                                        <a href="/laporankehadiran/detail/<?= urlencode($siswa['nisn']) ?>?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                                                           class="text-decoration-none" 
                                                           title="Klik untuk melihat detail absensi">
                                                            <?= htmlspecialchars($siswa['nisn'] ?? '-') ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="/laporankehadiran/detail/<?= urlencode($siswa['nisn']) ?>?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                                                           class="text-decoration-none" 
                                                           title="Klik untuk melihat detail absensi">
                                                            <?= htmlspecialchars($siswa['namasiswa'] ?? '-') ?>
                                                        </a>
                                                    </td>
                                                    <?php foreach ($laporan['tanggal_aktif'] as $tanggal): ?>
                                                        <?php 
                                                        $code = $laporan['absensi_data'][$siswa['nisn']][$tanggal] ?? 'A';
                                                        $class = getStatusClass($code);
                                                        // Define color mapping for inline style fallback
                                                        $colorMap = [
                                                            'A' => '#dc3545', // Merah
                                                            'I' => '#856404', // Kuning Warning
                                                            'S' => '#28a745', // Hijau
                                                            'T' => '#ff69b4', // Pink
                                                            'P' => '#007bff', // Biru
                                                            'H' => '#155724'  // Hijau
                                                        ];
                                                        $statusColor = $colorMap[$code] ?? '#000000';
                                                        ?>
                                                        <td class="laporan-table-cell-center laporan-table-cell-bold <?= $class ?>" style="color: <?= $statusColor ?> !important;">
                                                            <?= $code ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php 
                                                endforeach;
                                            else:
                                            ?>
                                                <tr>
                                                    <td colspan="<?= (count($laporan['tanggal_aktif']) ?? 0) + 3 ?>" class="text-center text-muted">
                                                        Tidak ada data siswa dalam kelas ini
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Legend -->
                                <div class="mt-4">
                                    <h6>Keterangan:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><strong>H</strong> = Hadir</li>
                                                <li><strong>I</strong> = Ijin</li>
                                                <li><strong>T</strong> = Terlambat</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><strong>A</strong> = Alpha</li>
                                                <li><strong>P</strong> = Pulang Awal</li>
                                                <li><strong>S</strong> = Sakit</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($id_kelas): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Tidak ada data laporan untuk kelas dan periode yang dipilih.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Silakan pilih kelas, bulan, dan tahun untuk menampilkan laporan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getStatusClass(code) {
    const classMap = {
        'H': 'status-h',
        'I': 'status-i',
        'T': 'status-t',
        'A': 'status-a',
        'P': 'status-p',
        'S': 'status-s'
    };
    return classMap[code] || '';
}

// Apply status classes only if not already applied (preserve PHP-generated classes)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.print-table td').forEach(function(td) {
        const code = td.textContent.trim();
        // Only apply if the cell doesn't already have a status class
        if (code && ['H', 'I', 'T', 'A', 'P', 'S'].includes(code) && !td.classList.contains('status-h') && !td.classList.contains('status-i') && !td.classList.contains('status-t') && !td.classList.contains('status-a') && !td.classList.contains('status-p') && !td.classList.contains('status-s')) {
            // Preserve existing classes and add status class
            const existingClasses = td.className;
            td.className = existingClasses + ' ' + getStatusClass(code);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
