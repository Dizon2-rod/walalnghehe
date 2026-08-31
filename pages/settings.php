<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	require_csrf();
	$data = [
		'greeting' => trim($_POST['greeting'] ?? ''),
		'anniversary_date' => trim($_POST['anniversary_date'] ?? '2026-09-14'),
		'partner_nickname' => trim($_POST['partner_nickname'] ?? 'My Love'),
		'theme_primary' => trim($_POST['theme_primary'] ?? ''),
		'theme_secondary' => trim($_POST['theme_secondary'] ?? ''),
		'theme_accent' => trim($_POST['theme_accent'] ?? ''),
		'default_format_note' => trim($_POST['default_format_note'] ?? ''),
	];
	save_app_setting($data);
	flash_set('success', 'Settings saved.');
	header('Location: ' . app_url('pages/settings.php'));
	exit;
}

$greeting = (string)app_setting('greeting', 'Hi my love!');
$anniversaryDate = (string)app_setting('anniversary_date', '2026-09-14');
$partnerNickname = (string)app_setting('partner_nickname', 'My Love');
$themePrimary = (string)app_setting('theme_primary', theme_defaults()['primary']);
$themeSecondary = (string)app_setting('theme_secondary', theme_defaults()['secondary']);
$themeAccent = (string)app_setting('theme_accent', theme_defaults()['accent']);
$defaultFormatNote = (string)app_setting('default_format_note', 'Default is 09-14-2022');

include __DIR__ . '/../includes/header.php';
?>
<h2 class="romantic-title mb-3">Settings</h2>
<?php if ($msg = flash_get('success')): ?>
	<div class="alert alert-success rounded-4"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<form method="post" class="card card-romance p-4 p-md-5">
	<?= csrf_field() ?>
	<div class="mb-3">
		<label class="form-label">Greeting</label>
		<input name="greeting" class="form-control rounded-4" value="<?= htmlspecialchars($greeting) ?>">
	</div>
	<div class="mb-3">
		<label class="form-label">Anniversary Date</label>
		<input name="anniversary_date" type="date" class="form-control rounded-4" value="<?= htmlspecialchars($anniversaryDate) ?>">
	</div>
	<div class="mb-3">
		<label class="form-label">Partner Nickname</label>
		<input name="partner_nickname" class="form-control rounded-4" value="<?= htmlspecialchars($partnerNickname) ?>">
	</div>
	<div class="mb-3">
		<label class="form-label">Theme Primary Color</label>
		<input name="theme_primary" type="color" class="form-control form-control-color" value="<?= htmlspecialchars($themePrimary) ?>">
	</div>
	<div class="mb-3"><label class="form-label">Theme Secondary Color</label><input name="theme_secondary" type="color" class="form-control form-control-color" value="<?= htmlspecialchars($themeSecondary) ?>"></div>
	<div class="mb-3"><label class="form-label">Theme Accent Color</label><input name="theme_accent" type="color" class="form-control form-control-color" value="<?= htmlspecialchars($themeAccent) ?>"></div>
	<div class="mb-3">
		<label class="form-label">Default Password Format Note</label>
		<input name="default_format_note" class="form-control rounded-4" value="<?= htmlspecialchars($defaultFormatNote) ?>">
		<div class="form-text">This note appears as guidance for the lock screen.</div>
	</div>
	<button class="btn btn-primary rounded-pill">Save</button>
</form>
<?php include __DIR__ . '/../includes/footer.php'; ?>
