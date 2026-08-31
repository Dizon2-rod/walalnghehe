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

$unlockedKey = 'unlocked_' . (string)$gift['_id'];
$unlocked = $_SESSION[$unlockedKey] ?? false;

if ($gift['is_locked'] ?? false) {
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlock_password'])) {
		if (!can_attempt_unlock((string)$gift['_id'])) {
			flash_set('error', 'Please wait a moment before trying again.');
			header('Location: /pages/view_gift.php?id=' . urlencode((string)$gift['_id']));
			exit;
		}
		$input = (string)($_POST['unlock_password'] ?? '');
		$accepted = (array)($gift['accepted_formats'] ?? default_password_formats());
		$ok = password_verify($input, $gift['lock_password_hash']) || is_password_valid_against_formats($input, $accepted);
		if ($ok) {
			$_SESSION[$unlockedKey] = true;
			clear_unlock_state((string)$gift['_id']);
			header('Location: /pages/view_gift.php?id=' . urlencode((string)$gift['_id']));
			exit;
		} else {
			register_unlock_failure((string)$gift['_id']);
			flash_set('error', 'Incorrect password.');
			header('Location: /pages/view_gift.php?id=' . urlencode((string)$gift['_id']));
			exit;
		}
	}
}

include __DIR__ . '/../includes/header.php';
?>
<?php if (($gift['is_locked'] ?? false) && !$unlocked): ?>
	<div class="row justify-content-center">
		<div class="col-md-7">
			<div class="card card-romance p-4 p-md-5 position-relative">
				<div class="d-flex align-items-center gap-2 mb-2">
					<span class="badge badge-lock rounded-pill">Locked</span>
					<span class="text-muted">Enter monthsary password to unlock</span>
				</div>
				<h3 class="romantic-title mb-3">This gift is lovingly locked 🔐</h3>
				<?php if ($msg = flash_get('error')): ?>
					<div class="alert alert-danger rounded-4"><?= htmlspecialchars($msg) ?></div>
				<?php endif; ?>
				<form method="post" class="row g-2">
					<div class="col-12 col-md">
						<input class="form-control rounded-4" name="unlock_password" placeholder="e.g. 09-14-2022" required>
					</div>
					<div class="col-12 col-md-auto">
						<button class="btn btn-primary rounded-pill px-4">Unlock</button>
					</div>
				</form>
				<?php if (!empty($gift['lock_hint'])): ?>
					<div class="small text-muted mt-2">Hint: <?= htmlspecialchars((string)$gift['lock_hint']) ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php else: ?>
	<div class="row justify-content-center">
		<div class="col-lg-9">
			<div class="card card-romance p-4 p-md-5 fade-in">
				<h2 class="romantic-title mb-3"><?= htmlspecialchars((string)$gift['title']) ?></h2>
				<?php if (!empty($gift['image'])): ?>
					<img class="w-100 rounded-4 mb-3" src="<?= htmlspecialchars((string)$gift['image']) ?>" alt="Gift image">
				<?php endif; ?>
				<p class="lead"><?= nl2br(htmlspecialchars((string)$gift['message'])) ?></p>
				<?php if (!empty($gift['music'])): ?>
					<a class="btn btn-outline-dark rounded-pill mt-3" href="<?= htmlspecialchars((string)$gift['music']) ?>" target="_blank" rel="noopener">Play Music</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
