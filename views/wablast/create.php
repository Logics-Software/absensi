<?php
$title = 'Buat WA Blast Campaign';
$config = require __DIR__ . '/../../config/app.php';
$baseUrl = rtrim($config['base_url'], '/');
if (empty($baseUrl) || $baseUrl === 'http://' || $baseUrl === 'https://') {
    $baseUrl = '/';
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
                    <li class="breadcrumb-item active">Buat Campaign</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Buat WA Blast Campaign</h4>
                </div>
                
                <form method="POST" action="/wablast/create" id="formWablast">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama" class="form-label">Nama Campaign <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       placeholder="Contoh: Notifikasi Absensi Bulan Januari" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="template_id" class="form-label">Template (Opsional)</label>
                                <select class="form-select" id="template_id" name="template_id" onchange="loadTemplate()">
                                    <option value="">Pilih Template</option>
                                    <?php foreach ($templates as $template): ?>
                                    <option value="<?= $template['id'] ?>" 
                                            data-pesan="<?= htmlspecialchars($template['pesan']) ?>"
                                            data-variabel="<?= htmlspecialchars($template['variabel'] ?? '[]') ?>">
                                        <?= htmlspecialchars($template['nama']) ?> 
                                        <?php if ($template['kategori']): ?>
                                        (<?= htmlspecialchars($template['kategori']) ?>)
                                        <?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipe_recipient" class="form-label">Tipe Penerima <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe_recipient" name="tipe_recipient" required onchange="toggleRecipientSelection()">
                                <option value="siswa">Semua Siswa Aktif</option>
                                <option value="wali">Semua Wali Siswa Aktif</option>
                                <option value="guru">Semua Guru Aktif</option>
                                <option value="custom">Pilih Manual (Custom)</option>
                            </select>
                            <small class="text-muted">Pilih tipe penerima untuk campaign ini</small>
                        </div>
                        
                        <!-- Custom Recipient Selection (hidden by default) -->
                        <div id="customRecipientSection" style="display: none;" class="mb-3">
                            <label class="form-label">Pilih Penerima Manual</label>
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                <div class="mb-2">
                                    <input type="text" class="form-control" id="recipientSearch" 
                                           placeholder="Cari siswa, wali, atau guru...">
                                </div>
                                <div id="recipientList" class="row g-2">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                            <small class="text-muted">Pilih penerima dengan mencentang checkbox</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="pesan" class="form-label">Pesan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="pesan" name="pesan" rows="8" 
                                      placeholder="Tulis pesan yang akan dikirim..." required></textarea>
                            <small class="text-muted">
                                Gunakan variabel: {{nama}}, {{nama_siswa}}, {{tanggal}}, {{status}}, dll. 
                                <br>Variabel akan diganti otomatis saat pengiriman.
                            </small>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Info:</strong> Campaign akan dibuat dalam status "Draft". 
                            Anda dapat mengirim campaign setelah dibuat dari halaman detail campaign.
                        </div>
                    </div>
                    
                    <div class="card-footer d-flex justify-content-between">
                        <a href="/wablast" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Buat Campaign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedRecipients = [];
    
    // Load template
    window.loadTemplate = function() {
        const select = document.getElementById('template_id');
        const option = select.options[select.selectedIndex];
        if (option && option.value) {
            const pesan = option.getAttribute('data-pesan');
            const variabel = option.getAttribute('data-variabel');
            document.getElementById('pesan').value = pesan || '';
            
            if (variabel) {
                try {
                    const vars = JSON.parse(variabel);
                    console.log('Variabel template:', vars);
                } catch (e) {
                    console.error('Error parsing variabel:', e);
                }
            }
        }
    };
    
    // Toggle recipient selection
    window.toggleRecipientSelection = function() {
        const tipe = document.getElementById('tipe_recipient').value;
        const customSection = document.getElementById('customRecipientSection');
        
        if (tipe === 'custom') {
            customSection.style.display = 'block';
            loadRecipients();
        } else {
            customSection.style.display = 'none';
            selectedRecipients = [];
            updateRecipientInput();
        }
    };
    
    // Load recipients for custom selection
    function loadRecipients() {
        const search = document.getElementById('recipientSearch').value.toLowerCase();
        const recipientList = document.getElementById('recipientList');
        
        // Fetch siswa, wali, and guru data
        fetch('/wablast/api/recipients?status=aktif')
            .then(r => r.json())
            .then(data => {
                const siswaData = data.siswa || [];
                const guruData = data.guru || [];
            let html = '';
            
            // Add siswa
            if (siswaData && siswaData.length > 0) {
                siswaData.forEach(siswa => {
                    if (!siswa.namasiswa) return;
                    
                    const match = siswa.namasiswa.toLowerCase().includes(search) || 
                                (siswa.nisn && siswa.nisn.toLowerCase().includes(search));
                    if (!match && search) return;
                    
                    const siswaId = 'siswa_' + siswa.id;
                    const isSelected = selectedRecipients.includes(siswaId);
                    
                    if (siswa.nomorhp) {
                        html += `
                            <div class="col-md-4 col-sm-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="${siswaId}" value="${siswaId}"
                                           ${isSelected ? 'checked' : ''}
                                           onchange="toggleRecipient('${siswaId}')">
                                    <label class="form-check-label" for="${siswaId}">
                                        <strong>Siswa:</strong> ${escapeHtml(siswa.namasiswa)} 
                                        ${siswa.nisn ? '(' + escapeHtml(siswa.nisn) + ')' : ''}
                                    </label>
                                </div>
                            </div>
                        `;
                    }
                    
                    if (siswa.nomorhpwali) {
                        const waliId = 'wali_' + siswa.id;
                        const isWaliSelected = selectedRecipients.includes(waliId);
                        html += `
                            <div class="col-md-4 col-sm-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="${waliId}" value="${waliId}"
                                           ${isWaliSelected ? 'checked' : ''}
                                           onchange="toggleRecipient('${waliId}')">
                                    <label class="form-check-label" for="${waliId}">
                                        <strong>Wali:</strong> ${escapeHtml(siswa.namawali || 'Wali dari ' + siswa.namasiswa)}
                                        <small class="text-muted">(${escapeHtml(siswa.namasiswa)})</small>
                                    </label>
                                </div>
                            </div>
                        `;
                    }
                });
            }
            
            // Add guru
            if (guruData && guruData.length > 0) {
                guruData.forEach(guru => {
                    if (!guru.namaguru) return;
                    
                    const match = guru.namaguru.toLowerCase().includes(search) || 
                                (guru.nip && guru.nip.toLowerCase().includes(search));
                    if (!match && search) return;
                    
                    const guruId = 'guru_' + guru.id;
                    const isSelected = selectedRecipients.includes(guruId);
                    
                    if (guru.nomorhp) {
                        html += `
                            <div class="col-md-4 col-sm-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="${guruId}" value="${guruId}"
                                           ${isSelected ? 'checked' : ''}
                                           onchange="toggleRecipient('${guruId}')">
                                    <label class="form-check-label" for="${guruId}">
                                        <strong>Guru:</strong> ${escapeHtml(guru.namaguru)}
                                        ${guru.nip ? '(' + escapeHtml(guru.nip) + ')' : ''}
                                    </label>
                                </div>
                            </div>
                        `;
                    }
                });
            }
            
            if (!html) {
                html = '<div class="col-12"><p class="text-muted text-center">Tidak ada data ditemukan</p></div>';
            }
            
            recipientList.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading recipients:', error);
            recipientList.innerHTML = '<div class="col-12"><p class="text-danger text-center">Error memuat data</p></div>';
        });
    }
    
    // Toggle recipient
    window.toggleRecipient = function(recipientId) {
        const index = selectedRecipients.indexOf(recipientId);
        if (index >= 0) {
            selectedRecipients.splice(index, 1);
        } else {
            selectedRecipients.push(recipientId);
        }
        updateRecipientInput();
    };
    
    // Update hidden input for recipients
    function updateRecipientInput() {
        // Create hidden inputs for selected recipients
        const existingInputs = document.querySelectorAll('input[name="recipient_ids[]"]');
        existingInputs.forEach(input => input.remove());
        
        selectedRecipients.forEach(recipientId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'recipient_ids[]';
            input.value = recipientId;
            document.getElementById('formWablast').appendChild(input);
        });
    }
    
    // Search functionality
    const recipientSearch = document.getElementById('recipientSearch');
    if (recipientSearch) {
        let searchTimeout;
        recipientSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(loadRecipients, 300);
        });
    }
    
    // Escape HTML helper
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Form validation
    const form = document.getElementById('formWablast');
    form.addEventListener('submit', function(e) {
        const tipe = document.getElementById('tipe_recipient').value;
        if (tipe === 'custom' && selectedRecipients.length === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 penerima untuk campaign custom');
            return false;
        }
    });
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

