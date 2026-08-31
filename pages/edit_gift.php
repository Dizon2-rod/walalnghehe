<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$id = $_GET['id'] ?? '';
try { $oid = new MongoDB\BSON\ObjectId($id); } catch (Throwable $e) { $oid = null; }
if (!$oid) {
	flash_set('error', 'Gift not found.');
	header('Location: /pages/history.php');
	exit;
}
$gift = col_gifts()->findOne(['_id' => $oid]);
if (!$gift) {
	flash_set('error', 'Gift not found.');
	header('Location: /pages/history.php');
	exit;
}

include __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
	<div class="col-lg-8">
		<div class="card card-romance p-4 p-md-5 fade-in">
			<h2 class="romantic-title mb-3">Edit Gift</h2>
			<p class="text-muted">Update details, replace the image, or adjust lock settings.</p>
			<?php if ($msg = flash_get('error')): ?>
				<div class="alert alert-danger rounded-4"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>
			<?php if ($msg = flash_get('success')): ?>
				<div class="alert alert-success rounded-4"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>
			<form method="post" action="/pages/update_gift.php" enctype="multipart/form-data">
				<input type="hidden" name="id" value="<?= (string)$gift['_id'] ?>">
				<div class="mb-3">
					<label class="form-label">Title</label>
					<input type="text" name="title" class="form-control rounded-4" value="<?= htmlspecialchars((string)$gift['title']) ?>" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Love Message</label>
					<textarea name="message" class="form-control rounded-4" rows="5" required><?= htmlspecialchars((string)$gift['message']) ?></textarea>
				</div>
				<div class="mb-3">
					<label class="form-label">Current Image</label>
					<?php if (!empty($gift['image'])): ?>
						<img class="w-100 rounded-4 mb-2" src="<?= htmlspecialchars((string)$gift['image']) ?>" alt="Gift image">
					<?php else: ?>
						<div class="text-muted">No image uploaded.</div>
					<?php endif; ?>
					<label class="form-label mt-2">Replace Image (optional)</label>
					<input type="file" name="image" class="form-control rounded-4" accept="image/*">
				</div>
				<div class="mb-3">
					<label class="form-label">Music Link (YouTube/Spotify)</label>
					<input type="url" name="music" class="form-control rounded-4" value="<?= htmlspecialchars((string)($gift['music'] ?? '')) ?>">
				</div>
				<div class="form-check form-switch mb-3">
					<input class="form-check-input" type="checkbox" role="switch" id="lockSwitch" name="is_locked" <?= ($gift['is_locked'] ?? false) ? 'checked' : '' ?>>
					<label class="form-check-label" for="lockSwitch">Lock this gift</label>
				</div>
				<div class="row g-3 align-items-end lock-settings" style="display: <?= ($gift['is_locked'] ?? false) ? 'flex' : 'none' ?>;">
					<div class="col-md-6">
						<label class="form-label">New Password (optional)</label>
						<input type="text" name="lock_password" class="form-control rounded-4" placeholder="Leave blank to keep existing">
					</div>
					<div class="col-md-6">
						<label class="form-label">Hint (optional)</label>
						<input type="text" name="hint" class="form-control rounded-4" value="<?= htmlspecialchars((string)($gift['lock_hint'] ?? '')) ?>">
					</div>
				</div>
				<div class="d-flex gap-2 mt-4">
					<a class="btn btn-outline-dark rounded-pill" href="/pages/history.php">Cancel</a>
					<button class="btn btn-primary rounded-pill">Save Changes</button>
				</div>
			</form>
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
