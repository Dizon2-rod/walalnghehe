<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$giftCount = col_gifts()->countDocuments([]);
include __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
	<div class="col-12">
		<div class="p-5 rounded-5 hero-gradient card-romance">
			<h2 class="romantic-title mb-2">Hello, Beautiful 💗</h2>
			<p class="text-muted mb-4">Here's our monthsary space — crafted with love.</p>
			<div class="d-flex flex-wrap gap-2">
				<a class="btn btn-primary rounded-pill" href="/pages/create_gift.php">Create Gift</a>
				<a class="btn btn-outline-dark rounded-pill" href="/pages/gallery.php">View Gallery</a>
				<a class="btn btn-outline-dark rounded-pill" href="/pages/history.php">Gift History</a>
				<a class="btn btn-outline-dark rounded-pill" href="/pages/settings.php">Settings</a>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card card-romance p-4 h-100">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<div class="text-muted">Total Gifts</div>
					<div class="display-6 romantic-title"><?= (int)$giftCount ?></div>
				</div>
				<div class="badge bg-light text-dark rounded-pill">💝</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card card-romance p-4 h-100">
			<div class="text-muted">Next Monthsary</div>
			<div class="h3 romantic-title" data-countdown>—</div>
			<div class="small text-muted">Counting down to the 14th</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card card-romance p-4 h-100">
			<div class="text-muted">Quick Actions</div>
			<ul class="list-unstyled mt-2 mb-0">
				<li><a href="/pages/create_gift.php">➕ New Gift</a></li>
				<li><a href="/pages/gallery.php">🖼️ Gallery</a></li>
				<li><a href="/pages/history.php">📜 History</a></li>
			</ul>
		</div>
	</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
