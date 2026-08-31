<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: /pages/create_gift.php');
	exit;
}

$title = trim($_POST['title'] ?? '');
$message = trim($_POST['message'] ?? '');
$music = trim($_POST['music'] ?? '');
$isLocked = isset($_POST['is_locked']);
$lockPassword = trim($_POST['lock_password'] ?? '');
$hint = trim($_POST['hint'] ?? '');

if ($title === '' || $message === '') {
	flash_set('error', 'Please provide a title and a love message.');
	header('Location: /pages/create_gift.php');
	exit;
}

// Handle image upload
$imgPath = null;
if (!empty($_FILES['image']['name'])) {
	$uploads = __DIR__ . '/../public/uploads/';
	@mkdir($uploads, 0775, true);
	$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
	$filename = 'gift_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext ?: 'jpg');
	$dest = $uploads . $filename;
	if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
		flash_set('error', 'Failed to upload image.');
		header('Location: /pages/create_gift.php');
		exit;
	}
	$imgPath = '/uploads/' . $filename;
}

$now = new DateTimeImmutable('now');
$monthCreated = (int)$now->format('n');
$yearCreated = (int)$now->format('Y');

$doc = [
	'title' => $title,
	'message' => $message,
	'image' => $imgPath,
	'music' => $music ?: null,
	'is_locked' => $isLocked,
	'lock_hint' => $hint ?: null,
	'created_at' => new MongoDB\BSON\UTCDateTime(),
	'month_created' => $monthCreated,
	'year_created' => $yearCreated,
	'owner_id' => current_user()['_id'] ?? null
];

if ($isLocked) {
	if ($lockPassword === '') {
		$lockPassword = '09-14-2022';
	}
	$doc['lock_password_hash'] = password_hash($lockPassword, PASSWORD_DEFAULT);
	$doc['accepted_formats'] = default_password_formats();
	if ($lockPassword !== '09-14-2022') {
		$doc['accepted_formats'][] = $lockPassword;
	}
}

col_gifts()->insertOne($doc);
flash_set('success', 'Your gift has been saved!');
header('Location: /pages/history.php');
exit;
