<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

// Flash messaging
function flash_set(string $key, string $message): void {
	$_SESSION['flash'][$key] = $message;
}
function flash_get(string $key): ?string {
	if (!empty($_SESSION['flash'][$key])) {
		$msg = $_SESSION['flash'][$key];
		unset($_SESSION['flash'][$key]);
		return $msg;
	}
	return null;
}

// Auth
function current_user(): ?array {
	return $_SESSION['user'] ?? null;
}
function require_login(): void {
	if (!current_user()) {
		header('Location: /pages/login.php');
		exit;
	}
}

// Initialize default user once
function ensure_default_user(): void {
	$users = col_users();
	$existing = $users->findOne(['email' => 'admin@example.com']);
	if (!$existing) {
		$users->insertOne([
			'email' => 'admin@example.com',
			'password_hash' => password_hash('love-you-forever', PASSWORD_DEFAULT),
			'name' => 'My Love',
			'created_at' => new MongoDB\BSON\UTCDateTime()
		]);
	}
}

// Gift lock password handling
function default_password_formats(): array {
	return [
		'09-14-2022',
		'09/14/2022',
		'September 14, 2022',
		'09.14.2022',
		'09142022'
	];
}

function is_password_valid_against_formats(string $input, array $accepted): bool {
	$normalized = trim($input);
	foreach ($accepted as $candidate) {
		if (hash_equals(strtolower($candidate), strtolower($normalized))) {
			return true;
		}
	}
	return false;
}

function can_attempt_unlock(string $giftId, int $maxAttempts = 5, int $cooldownSeconds = 60): bool {
	$key = 'unlock_' . $giftId;
	$state = $_SESSION[$key] ?? ['count' => 0, 'cooldown_until' => 0];
	if (time() < ($state['cooldown_until'] ?? 0)) {
		return false;
	}
	return true;
}

function register_unlock_failure(string $giftId, int $maxAttempts = 5, int $cooldownSeconds = 60): void {
	$key = 'unlock_' . $giftId;
	$state = $_SESSION[$key] ?? ['count' => 0, 'cooldown_until' => 0];
	$state['count'] = ($state['count'] ?? 0) + 1;
	if ($state['count'] >= $maxAttempts) {
		$state['cooldown_until'] = time() + $cooldownSeconds;
		$state['count'] = 0; // reset after cooldown start
	}
	$_SESSION[$key] = $state;
}

function clear_unlock_state(string $giftId): void {
	$key = 'unlock_' . $giftId;
	unset($_SESSION[$key]);
}

// Theming defaults
function theme_defaults(): array {
	return [
		'primary' => '#EFBBCF', // blush pink
		'secondary' => '#E8DADA', // beige/cream
		'accent' => '#CDB4DB', // lavender
		'dark' => '#1f1a24',
		'light' => '#ffffff',
		'font_primary' => 'Poppins',
		'font_display' => 'Playfair Display'
	];
}

// Get app setting
function app_setting(string $key, $default = null) {
	$doc = col_settings()->findOne(['_id' => 'app']);
	if (!$doc) return $default;
	return $doc[$key] ?? $default;
}

// Save app setting
function save_app_setting(array $data): void {
	col_settings()->updateOne(['_id' => 'app'], ['$set' => $data], ['upsert' => true]);
}

?>
