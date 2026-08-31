<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ' . app_url('pages/create_gift.php'));
	exit;
}
require_csrf();

$title = trim($_POST['title'] ?? '');
$message = trim($_POST['message'] ?? '');
$music = trim($_POST['music'] ?? '');
$isLocked = isset($_POST['is_locked']);
$lockPassword = trim($_POST['lock_password'] ?? '');
$hint = trim($_POST['hint'] ?? '');

if ($title === '' || $message === '') {
	flash_set('error', 'Please provide a title and a love message.');
	header('Location: ' . app_url('pages/create_gift.php'));
	exit;
}

// Handle image upload
$imgPath = null;
try { $imgPath = secure_upload($_FILES['image'] ?? [], 'image'); }
catch (Throwable $e) { flash_set('error', $e->getMessage()); header('Location: ' . app_url('pages/create_gift.php')); exit; }
$musicFile = null; $voiceNote = null;
try { $musicFile = secure_upload($_FILES['music_file'] ?? [], 'audio'); $voiceNote = secure_upload($_FILES['voice_note'] ?? [], 'audio'); }
catch (Throwable $e) { flash_set('error', $e->getMessage()); header('Location: ' . app_url('pages/create_gift.php')); exit; }

$now = new DateTimeImmutable('now');
$monthCreated = (int)$now->format('n');
$yearCreated = (int)$now->format('Y');

$doc = [
	'title' => $title,
	'message' => $message,
	'image' => $imgPath,
	'music_url' => $musicFile ?: ($music ?: null),
	'music' => $music ?: null,
	'voice_note_url' => $voiceNote,
	'unlock_at' => '2026-09-14T00:00:00+08:00',
	'timeline_year' => 4,
	'coupons' => [
		['id' => 1, 'title' => 'Unlimited Cuddle Pass', 'icon' => '♥', 'is_redeemed' => false],
		['id' => 2, 'title' => 'Late Night Food Trip Ticket', 'icon' => 'Food', 'is_redeemed' => false],
		['id' => 3, 'title' => '1 Day No-Tampo Pass', 'icon' => 'Forever', 'is_redeemed' => false],
	],
	'recipient_reply' => null,
	'is_locked' => $isLocked,
	'lock_hint' => $hint ?: null,
	'created_at' => mongo_utc_now(),
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
header('Location: ' . app_url('pages/history.php'));
exit;
