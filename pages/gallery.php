<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$year = (int)($_GET['year'] ?? date('Y'));
$month = (int)($_GET['month'] ?? 0);

$filter = ['year_created' => $year];
if ($month >= 1 && $month <= 12) $filter['month_created'] = $month;
$cursor = col_gifts()->find($filter, ['sort' => ['created_at' => -1]]);
$gifts = iterator_to_array($cursor, false);

include __DIR__ . '/../includes/header.php';
?>
<h2 class="romantic-title mb-3">Monthly Gallery</h2>
<form class="row g-2 mb-3" method="get">
	<div class="col-auto">
		<select name="month" class="form-select rounded-4">
			<option value="0">All Months</option>
			<?php for($m=1;$m<=12;$m++): ?>
				<option value="<?= $m ?>" <?= $month===$m?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
			<?php endfor; ?>
		</select>
	</div>
	<div class="col-auto">
		<select name="year" class="form-select rounded-4">
			<?php for($y=date('Y')-3;$y<=date('Y')+1;$y++): ?>
				<option value="<?= $y ?>" <?= $year===$y?'selected':'' ?>><?= $y ?></option>
			<?php endfor; ?>
		</select>
	</div>
	<div class="col-auto">
		<button class="btn btn-outline-dark rounded-pill">Filter</button>
	</div>
</form>

<div class="row g-4">
<?php foreach ($gifts as $g): ?>
	<div class="col-sm-6 col-lg-4">
		<div class="card card-romance p-3 position-relative">
			<div class="position-relative">
				<?php if (!empty($g['image'])): ?>
					<img class="gallery-thumb w-100" src="<?= htmlspecialchars((string)$g['image']) ?>" alt="thumb">
				<?php else: ?>
					<div class="gallery-thumb w-100 d-flex align-items-center justify-content-center bg-light">No Image</div>
				<?php endif; ?>
				<?php if ($g['is_locked'] ?? false): ?>
					<div class="lock-overlay"><span class="badge badge-lock rounded-pill">🔒 Locked</span></div>
				<?php endif; ?>
			</div>
			<div class="mt-2 d-flex align-items-center justify-content-between">
				<div>
					<div class="fw-semibold"><?= htmlspecialchars((string)$g['title']) ?></div>
					<div class="small text-muted"><?= date('F', mktime(0,0,0,(int)$g['month_created'],1)) ?> <?= (int)$g['year_created'] ?></div>
				</div>
				<a class="btn btn-primary btn-sm rounded-pill" href="/pages/view_gift.php?id=<?= (string)$g['_id'] ?>">Open</a>
			</div>
		</div>
	</div>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
