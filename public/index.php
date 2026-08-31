<?php
require_once __DIR__ . '/../includes/helpers.php';
ensure_default_user();
if (current_user()) {
	header('Location: /pages/dashboard.php');
} else {
	header('Location: /pages/login.php');
}
exit;
