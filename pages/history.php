<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$cursor = col_gifts()->find([], ['sort' => ['created_at' => -1]]);
$gifts = iterator_to_array($cursor, false);
include __DIR__ . '/../includes/header.php';
?>
<h2 class="romantic-title mb-3">Gift History</h2>
<div class="row g-4">
	<?php foreach ($gifts as $g): ?>
		<div class="col-sm-6 col-lg-4">
			<div class="card card-romance p-3 position-relative">
				<?php if (!empty($g['image'])): ?>
					<img class="gallery-thumb w-100 mb-2" src="<?= htmlspecialchars((string)$g['image']) ?>" alt="thumb">
				<?php endif; ?>
				<div class="d-flex align-items-center justify-content-between">
					<h5 class="mb-0 romantic-title"><?= htmlspecialchars((string)$g['title']) ?></h5>
					<?php if ($g['is_locked'] ?? false): ?><span class="badge badge-lock rounded-pill">🔒</span><?php endif; ?>
				</div>
				<p class="text-muted small mb-3"><?= htmlspecialchars(substr((string)$g['message'],0,80)) ?><?= strlen((string)$g['message'])>80?'…':'' ?></p>
				<div class="d-flex gap-2">
					<a class="btn btn-primary btn-sm rounded-pill" href="<?= htmlspecialchars(app_url('pages/view_gift.php?id=' . urlencode((string)$g['_id']))) ?>">View</a>
					<a class="btn btn-outline-dark btn-sm rounded-pill" href="<?= htmlspecialchars(app_url('pages/edit_gift.php?id=' . urlencode((string)$g['_id']))) ?>">Edit</a>
					<a class="btn btn-outline-dark btn-sm rounded-pill" href="<?= htmlspecialchars(app_url('pages/delete_gift.php?id=' . urlencode((string)$g['_id']))) ?>" onclick="return confirm('Delete this gift?')">Delete</a>
					<button class="btn btn-outline-dark btn-sm rounded-pill" onclick="navigator.clipboard.writeText(location.origin + '/pages/view_gift.php?id=<?= (string)$g['_id'] ?>'); this.textContent='Copied!'; setTimeout(()=>this.textContent='Share',1200);">Share</button>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
