<?php
require_once __DIR__ . '/../includes/helpers.php';
ensure_default_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$password = (string)($_POST['password'] ?? '');
	$user = col_users()->findOne(['email' => $email]);
	if ($user && password_verify($password, $user['password_hash'])) {
		$_SESSION['user'] = ['_id' => (string)$user['_id'], 'email' => $user['email']];
		header('Location: /pages/dashboard.php');
		exit;
	}
	flash_set('error', 'Incorrect email or password.');
	header('Location: /pages/login.php');
	exit;
}

include __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
	<div class="col-md-6 col-lg-5">
		<div class="card card-romance p-4 p-md-5 fade-in">
			<h1 class="romantic-title mb-3">Welcome, my love</h1>
			<p class="text-muted">Sign in to see your monthsary surprises.</p>
			<?php if ($msg = flash_get('error')): ?>
				<div class="alert alert-danger rounded-4"><?= htmlspecialchars($msg) ?></div>
			<?php endif; ?>
			<form method="post" class="mt-3">
				<div class="mb-3">
					<label class="form-label">Email</label>
					<input type="email" name="email" class="form-control rounded-4" placeholder="you@example.com" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Password</label>
					<input type="password" name="password" class="form-control rounded-4" placeholder="••••••••" required>
				</div>
				<button class="btn btn-primary rounded-pill px-4">Sign In</button>
			</form>
		</div>
	</div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
