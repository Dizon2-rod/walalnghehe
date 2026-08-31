<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: ' . app_url('pages/history.php'));
	exit;
}
require_csrf();

$id = $_POST['id'] ?? '';
try { $oid = mongo_object_id($id); } catch (Throwable $e) { $oid = null; }
if (!$oid) {
	flash_set('error', 'Invalid gift.');
	header('Location: ' . app_url('pages/history.php'));
	exit;
}

$gift = col_gifts()->findOne(['_id' => $oid]);
if (!$gift) {
	flash_set('error', 'Gift not found.');
	header('Location: ' . app_url('pages/history.php'));
	exit;
}

$title = trim($_POST['title'] ?? '');
$message = trim($_POST['message'] ?? '');
$music = trim($_POST['music'] ?? '');
$isLocked = isset($_POST['is_locked']);
$newPassword = trim($_POST['lock_password'] ?? '');
$hint = trim($_POST['hint'] ?? '');

if ($title === '' || $message === '') {
	flash_set('error', 'Please provide a title and a love message.');
	header('Location: ' . app_url('pages/edit_gift.php?id=' . urlencode($id)));
	exit;
}

$set = [
	'title' => $title,
	'message' => $message,
	'music_url' => $music ?: null,
	'music' => $music ?: null,
	'is_locked' => $isLocked,
	'lock_hint' => $hint ?: null,
];
try {
	$musicFile = secure_upload($_FILES['music_file'] ?? [], 'audio');
	$voiceNote = secure_upload($_FILES['voice_note'] ?? [], 'audio');
	if ($musicFile) $set['music_url'] = $musicFile;
	if ($voiceNote) $set['voice_note_url'] = $voiceNote;
} catch (Throwable $e) { flash_set('error', $e->getMessage()); header('Location: ' . app_url('pages/edit_gift.php?id=' . urlencode($id))); exit; }

// Image replacement (optional)
if (!empty($_FILES['image']['name'])) {
	try { $set['image'] = secure_upload($_FILES['image'], 'image'); }
	catch (Throwable $e) { flash_set('error', $e->getMessage()); header('Location: ' . app_url('pages/edit_gift.php?id=' . urlencode($id))); exit; }
	// Optionally remove old image
	$old = (string)($gift['image'] ?? '');
	if ($old && str_starts_with($old, '/uploads/')) {
		$oldPath = __DIR__ . '/../public' . $old;
		@unlink($oldPath);
	}
}

// Lock transitions
if ($isLocked) {
	// If newly locking and no prior hash, or user provided new password
	if ($newPassword !== '') {
		$set['lock_password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
		$accepted = default_password_formats();
		$accepted[] = $newPassword;
		$set['accepted_formats'] = array_values(array_unique($accepted));
	} elseif (empty($gift['lock_password_hash'])) {
		// Locking a previously unlocked gift without password: use default
		$default = '09-14-2022';
		$set['lock_password_hash'] = password_hash($default, PASSWORD_DEFAULT);
		$set['accepted_formats'] = default_password_formats();
	}
} else {
	// Unlocking: remove lock data
	$set['lock_password_hash'] = null;
	$set['accepted_formats'] = null;
}

col_gifts()->updateOne(['_id' => $oid], ['$set' => $set]);
flash_set('success', 'Gift updated.');
header('Location: ' . app_url('pages/history.php'));
exit;
