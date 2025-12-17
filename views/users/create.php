<?php
$title = 'Tambah User';
require __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="breadcrumb-item">
        <div class="col-12">
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/users">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/users">Users</a></li>
                    <li class="breadcrumb-item active">Tambah User</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Tambah Data User</h4>
                    </div>
                </div>
                <form method="POST" action="/users/create" enctype="multipart/form-data">
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="Masukkan username">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="namalengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namalengkap" name="namalengkap" required placeholder="Masukkan nama lengkap">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="contoh@email.com">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="password-input-wrapper">
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Minimal 6 karakter">
                                    <button type="button" class="password-toggle-btn" data-target="password" aria-label="Toggle password visibility">
                                        <?= icon('eye-slash', '', 18) ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="tatausaha" selected>Tata Usaha</option>
                                    <option value="guru">Guru</option>
                                    <option value="kepalasekolah">Kepala Sekolah</option>
                                    <option value="walimurid">Wali Murid</option>
                                </select>
                            </div>
                            
							<div class="col-md-6 mb-3" id="id_guru-wrapper" style="display: none;">
								<label for="id_guru" class="form-label">Master Guru <span class="text-danger" id="id_guru-required">*</span></label>
								<select class="form-select" id="id_guru" name="id_guru">
									<option value="">Pilih Master Guru</option>
									<?php foreach ($masterGuruList as $guru): ?>
										<option value="<?= $guru['id'] ?>">
											<?= htmlspecialchars($guru['namaguru']) ?>
											<?php if (!empty($guru['nip'])): ?>
												(<?= htmlspecialchars($guru['nip']) ?>)
											<?php endif; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif" selected>Aktif</option>
                                    <option value="nonaktif">Non Aktif</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="picture" class="form-label">Foto Profil</label>
                                <input type="file" class="form-control" id="picture" name="picture" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB)</small>
                            </div>
                        </div>
                        
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/users" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                    <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Simpan User</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const idGuruWrapper = document.getElementById('id_guru-wrapper');
	const idGuruInput = document.getElementById('id_guru');
    const idGuruRequired = document.getElementById('id_guru-required');
    
    function toggleIdGuru() {
        if (roleSelect.value === 'guru') {
            idGuruWrapper.style.display = 'block';
			idGuruInput.setAttribute('required', 'required');
            idGuruRequired.style.display = 'inline';
        } else {
            idGuruWrapper.style.display = 'none';
            idGuruInput.removeAttribute('required');
			idGuruInput.value = '';
            idGuruRequired.style.display = 'none';
        }
    }
    
    toggleIdGuru();
    roleSelect.addEventListener('change', toggleIdGuru);
    
    // Password toggle
    const passwordToggle = document.querySelector('[data-target="password"]');
    if (passwordToggle) {
        passwordToggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (input) {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

