<?php
require_once __DIR__ . '/../includes/helpers.php';
ensure_default_user();
if (current_user()) {
	header('Location: ' . app_url('pages/dashboard.php'));
} else {
	header('Location: ' . app_url('pages/login.php'));
}
exit;
