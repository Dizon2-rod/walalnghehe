<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$data = [
		'greeting' => trim($_POST['greeting'] ?? ''),
		'theme_primary' => trim($_POST['theme_primary'] ?? ''),
		'default_format_note' => trim($_POST['default_format_note'] ?? ''),
	];
	save_app_setting($data);
	flash_set('success', 'Settings saved.');
	header('Location: /pages/settings.php');
	exit;
}

$greeting = (string)app_setting('greeting', 'Hi my love!');
$themePrimary = (string)app_setting('theme_primary', theme_defaults()['primary']);
$defaultFormatNote = (string)app_setting('default_format_note', 'Default is 09-14-2022');

include __DIR__ . '/../includes/header.php';
?>
<h2 class="romantic-title mb-3">Settings</h2>
<?php if ($msg = flash_get('success')): ?>
	<div class="alert alert-success rounded-4"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<form method="post" class="card card-romance p-4 p-md-5">
	<div class="mb-3">
		<label class="form-label">Greeting</label>
		<input name="greeting" class="form-control rounded-4" value="<?= htmlspecialchars($greeting) ?>">
	</div>
	<div class="mb-3">
		<label class="form-label">Theme Primary Color</label>
		<input name="theme_primary" type="color" class="form-control form-control-color" value="<?= htmlspecialchars($themePrimary) ?>">
	</div>
	<div class="mb-3">
		<label class="form-label">Default Password Format Note</label>
		<input name="default_format_note" class="form-control rounded-4" value="<?= htmlspecialchars($defaultFormatNote) ?>">
		<div class="form-text">This note appears as guidance for the lock screen.</div>
	</div>
	<button class="btn btn-primary rounded-pill">Save</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
