<?php
$title = 'Edit User';
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
            <nav aria-label="breadcrumb" data-breadcrumb-parent="/users">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/users">Users</a></li>
                    <li class="breadcrumb-item active">Edit User</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0">Edit Data User</h4>
                    </div>
                </div>

                <form method="POST" action="/users/edit/<?= $user['id'] ?>" enctype="multipart/form-data">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required placeholder="Masukkan username">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="namalengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="namalengkap" name="namalengkap" value="<?= htmlspecialchars($user['namalengkap']) ?>" required placeholder="Masukkan nama lengkap">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required placeholder="contoh@email.com">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin" <?= ($user['role'] == 'admin' || $user['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                                    <option value="tatausaha" <?= ($user['role'] == 'tatausaha' || $user['role'] == 'tata_usaha') ? 'selected' : '' ?>>Tata Usaha</option>
                                    <option value="guru" <?= $user['role'] == 'guru' ? 'selected' : '' ?>>Guru</option>
                                    <option value="kepalasekolah" <?= ($user['role'] == 'kepalasekolah' || $user['role'] == 'kepala_sekolah' || $user['role'] == 'penilik_sekolah') ? 'selected' : '' ?>>Kepala Sekolah</option>
                                    <option value="walimurid" <?= ($user['role'] == 'walimurid' || $user['role'] == 'wali_murid') ? 'selected' : '' ?>>Wali Murid</option>
                                </select>
                            </div>
                        </div>
                        
						<div class="row">
							<div class="col-md-6 mb-3" id="id_guru-wrapper" style="display: <?= ($user['role'] == 'guru') ? 'block' : 'none' ?>;">
								<label for="id_guru" class="form-label">Master Guru <span class="text-danger" id="id_guru-required" style="display: <?= ($user['role'] == 'guru') ? 'inline' : 'none' ?>;">*</span></label>
								<select class="form-select" id="id_guru" name="id_guru" <?= ($user['role'] == 'guru') ? 'required' : '' ?>>
									<option value="">Pilih Master Guru</option>
									<?php foreach ($masterGuruList as $guru): ?>
										<option value="<?= $guru['id'] ?>" <?= ($user['id_guru'] ?? null) == $guru['id'] ? 'selected' : '' ?>>
											<?= htmlspecialchars($guru['namaguru']) ?>
											<?php if (!empty($guru['nip'])): ?>
												(<?= htmlspecialchars($guru['nip']) ?>)
											<?php endif; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="aktif" <?= $user['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= ($user['status'] == 'nonaktif' || $user['status'] == 'non aktif') ? 'selected' : '' ?>>Non Aktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="picture" class="form-label">Foto Profil</label>
                            <?php if ($user['picture'] && file_exists(__DIR__ . '/../../uploads/' . $user['picture'])): ?>
                            <div class="mb-3">
                                <img src="<?= htmlspecialchars($baseUrl) ?>/uploads/<?= htmlspecialchars($user['picture']) ?>" alt="Current Picture" class="img-thumbnail rounded" style="max-width: 200px;">
                            </div>
                            <?php else: ?>
                            <div class="mb-3">
                                <p class="mb-2 text-muted"><em>Tidak ada foto profil</em></p>
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="picture" name="picture" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, GIF (Max 5MB). Kosongkan jika tidak ingin mengubah foto.</small>
                        </div>                        
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="/users" class="btn btn-secondary"><?= icon('cancel', 'me-1 mb-1', 18) ?>Batal</a>
                        <button type="submit" class="btn btn-primary"><?= icon('save', 'me-1 mb-1', 18) ?>Update User</button>
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
			if (roleSelect.value !== 'guru') {
				idGuruInput.value = '';
			}
            idGuruRequired.style.display = 'none';
        }
    }
    
    toggleIdGuru();
    roleSelect.addEventListener('change', toggleIdGuru);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

