<?php
require_once __DIR__ . '/../includes/helpers.php';
require_login();

$id = $_GET['id'] ?? '';
try { $oid = mongo_object_id($id); } catch (Throwable $e) { $oid = null; }
if (!$oid) {
	flash_set('error', 'Invalid gift.');
	header('Location: ' . app_url('pages/history.php'));
	exit;
}

$gift = col_gifts()->findOne(['_id' => $oid]);
if ($gift) {
	// Remove image file if local
	$img = (string)($gift['image'] ?? '');
	if ($img && str_starts_with($img, '/uploads/')) {
		$imgPath = __DIR__ . '/../public' . $img;
		@unlink($imgPath);
	}
	col_gifts()->deleteOne(['_id' => $oid]);
	flash_set('success', 'Gift deleted.');
} else {
	flash_set('error', 'Gift not found.');
}

header('Location: ' . app_url('pages/history.php'));
exit;
