<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();
include __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
	<div class="col-lg-8">
		<div class="card card-romance p-4 p-md-5 fade-in">
			<h2 class="romantic-title mb-3">Create a Gift</h2>
			<p class="text-muted">Add a heartfelt message, image, and optional music link.</p>
			<?php if ($msg = flash_get('error')): ?>
				<div class="alert alert-danger rounded-4"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>
			<?php if ($msg = flash_get('success')): ?>
				<div class="alert alert-success rounded-4"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>
			<form method="post" action="<?= htmlspecialchars(app_url('pages/save_gift.php')) ?>" enctype="multipart/form-data">
				<?= csrf_field() ?>
				<div class="mb-3">
					<label class="form-label">Title</label>
					<input type="text" name="title" class="form-control rounded-4" placeholder="Our Sweet Memory" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Love Message</label>
					<textarea name="message" class="form-control rounded-4" rows="5" placeholder="Write something romantic..." required></textarea>
				</div>
				<div class="mb-3">
					<label class="form-label">Image</label>
					<input type="file" name="image" class="form-control rounded-4" accept="image/*" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Music Link (YouTube/Spotify)</label>
					<input type="url" name="music" class="form-control rounded-4" placeholder="https://open.spotify.com/... or https://youtube.com/...">
				</div>
				<div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Ambient MP3 (optional)</label><input type="file" name="music_file" class="form-control rounded-4" accept="audio/mpeg,audio/mp4,audio/wav"></div><div class="col-md-6"><label class="form-label">Voice Note (optional)</label><input type="file" name="voice_note" class="form-control rounded-4" accept="audio/mpeg,audio/mp4,audio/wav"></div></div>
				<div class="form-check form-switch mb-3">
					<input class="form-check-input" type="checkbox" role="switch" id="lockSwitch" name="is_locked">
					<label class="form-check-label" for="lockSwitch">Lock this gift</label>
				</div>
				<div class="row g-3 align-items-end lock-settings" style="display:none;">
					<div class="col-md-6">
						<label class="form-label">Password (optional)</label>
						<input type="text" name="lock_password" class="form-control rounded-4" placeholder="Default is 09-14-2022">
					</div>
					<div class="col-md-6">
						<label class="form-label">Hint (optional)</label>
						<input type="text" name="hint" class="form-control rounded-4" placeholder="e.g. Our special date format">
					</div>
				</div>
				<div class="d-flex gap-2 mt-4">
					<button type="button" class="btn btn-outline-dark rounded-pill" data-bs-toggle="modal" data-bs-target="#previewModal">Preview Gift</button>
					<button class="btn btn-primary rounded-pill">Save Gift</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content rounded-4">
			<div class="modal-header">
				<h5 class="modal-title romantic-title">Gift Preview</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p class="text-muted">This is a quick preview of how your gift will feel. Fill the form and imagine the magic!</p>
				<div class="card card-romance p-4">
					<h4 class="romantic-title">Your Title Here</h4>
					<p>Your beautiful message will appear here with pastel glow and soft animation.</p>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary rounded-pill" data-bs-dismiss="modal">Looks Lovely</button>
			</div>
		</div>
	</div>
</div>

<script>
const lockSwitch = document.getElementById('lockSwitch');
const lockSettings = document.querySelector('.lock-settings');
if (lockSwitch) {
	lockSwitch.addEventListener('change', ()=>{
		lockSettings.style.display = lockSwitch.checked ? 'flex' : 'none';
	});
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
