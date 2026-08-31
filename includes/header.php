<?php
require_once __DIR__ . '/helpers.php';
$theme = theme_defaults();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Monthsary Gifts</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="/assets/css/style.css" rel="stylesheet">
	<style>
		:root {
			--color-primary: <?= htmlspecialchars($theme['primary']) ?>;
			--color-secondary: <?= htmlspecialchars($theme['secondary']) ?>;
			--color-accent: <?= htmlspecialchars($theme['accent']) ?>;
			--color-dark: <?= htmlspecialchars($theme['dark']) ?>;
			--color-light: <?= htmlspecialchars($theme['light']) ?>;
			--font-primary: '<?= htmlspecialchars($theme['font_primary']) ?>', sans-serif;
			--font-display: '<?= htmlspecialchars($theme['font_display']) ?>', serif;
		}
	</style>
</head>
<body class="bg-rose-veil">
	<nav class="navbar navbar-expand-lg bg-white border-0 shadow-soft rounded-4 mt-3 mx-3">
		<div class="container-fluid">
			<a class="navbar-brand romantic-title" href="/pages/dashboard.php">💖 Monthsary</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="nav">
				<ul class="navbar-nav me-auto mb-2 mb-lg-0">
					<li class="nav-item"><a class="nav-link" href="/pages/dashboard.php">Dashboard</a></li>
					<li class="nav-item"><a class="nav-link" href="/pages/create_gift.php">Create Gift</a></li>
					<li class="nav-item"><a class="nav-link" href="/pages/gallery.php">Gallery</a></li>
					<li class="nav-item"><a class="nav-link" href="/pages/history.php">History</a></li>
					<li class="nav-item"><a class="nav-link" href="/pages/settings.php">Settings</a></li>
				</ul>
				<div class="d-flex align-items-center gap-2">
					<?php if (current_user()): ?>
						<span class="small text-muted">Hi, <?= htmlspecialchars(current_user()['email']) ?></span>
						<a class="btn btn-outline-dark rounded-pill" href="/pages/logout.php">Logout</a>
					<?php else: ?>
						<a class="btn btn-primary rounded-pill" href="/pages/login.php">Login</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</nav>
	<main class="container my-4">
