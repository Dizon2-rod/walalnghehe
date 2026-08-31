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
function app_url(string $path = ''): string {
	global $APP_BASE_PATH;
	return $APP_BASE_PATH . '/' . ltrim($path, '/');
}
function app_base_path(): string {
	global $APP_BASE_PATH;
	return $APP_BASE_PATH;
}
function require_login(): void {
	if (!current_user()) {
		header('Location: ' . app_url('pages/login.php'));
		exit;
	}
}

function is_admin(): bool {
	$user = current_user();
	return !empty($user) && (($user['role'] ?? '') === 'admin' || ($user['email'] ?? '') === 'admin@example.com');
}

function require_admin(): void {
	require_login();
	if (!is_admin()) {
		http_response_code(403);
		exit('Administrator access required.');
	}
}

function mongo_object_id(string $id): string {
	if (!preg_match('/^[a-f0-9-]{36}$/i', $id)) throw new InvalidArgumentException('Invalid identifier.');
	return $id;
}

function mongo_utc_now(): string {
	return date('Y-m-d H:i:s');
}

function csrf_token(): string {
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['csrf_token'];
}

function csrf_field(): string {
	return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_validate(?string $token): bool {
	return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf(): void {
	if (!csrf_validate($_POST['csrf_token'] ?? null)) {
		http_response_code(419);
		exit('Invalid or expired request token.');
	}
}

function secure_upload(array $file, string $kind): ?string {
	if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
	if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
		throw new RuntimeException('Invalid upload.');
	}
	$allowed = $kind === 'audio'
		? ['audio/mpeg' => 'mp3', 'audio/mp4' => 'm4a', 'audio/x-m4a' => 'm4a', 'audio/wav' => 'wav', 'audio/x-wav' => 'wav']
		: ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
	$mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
	if (!isset($allowed[$mime])) throw new RuntimeException('Unsupported media type.');
	$uploads = __DIR__ . '/../public/uploads/';
	if (!is_dir($uploads) && !mkdir($uploads, 0775, true) && !is_dir($uploads)) throw new RuntimeException('Upload directory unavailable.');
	$name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
	if (!move_uploaded_file($file['tmp_name'], $uploads . $name)) throw new RuntimeException('Failed to store upload.');
	return app_url('public/uploads/' . $name);
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
			'role' => 'admin',
			'created_at' => mongo_utc_now()
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
		$state['cooldown_until'] = time() + ($cooldownSeconds * min($state['count'] - $maxAttempts + 1, 5));
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
	$defaults = [
		'primary' => '#EFBBCF', // blush pink
		'secondary' => '#E8DADA', // beige/cream
		'accent' => '#CDB4DB', // lavender
		'dark' => '#1f1a24',
		'light' => '#ffffff',
		'font_primary' => 'Poppins',
		'font_display' => 'Playfair Display'
	];
	try {
		$settings = col_settings()->findOne(['_id' => 'app']);
		if ($settings) {
			$defaults['primary'] = (string)($settings['theme_primary'] ?? $defaults['primary']);
			$defaults['secondary'] = (string)($settings['theme_secondary'] ?? $defaults['secondary']);
			$defaults['accent'] = (string)($settings['theme_accent'] ?? $defaults['accent']);
		}
	} catch (Throwable $e) { /* MongoDB may be unavailable on the login screen. */ }
	return $defaults;
}

function anniversary_settings(): array {
	$settings = col_settings()->findOne(['_id' => 'app']);
	return [
		'anniversary_date' => (string)($settings['anniversary_date'] ?? '2026-09-14'),
		'partner_nickname' => (string)($settings['partner_nickname'] ?? 'My Love'),
		'theme_primary' => (string)($settings['theme_primary'] ?? theme_defaults()['primary']),
	];
}

function gift_schema_defaults(): array {
	return [
		'music_url' => null, 'voice_note_url' => null, 'unlock_at' => '2026-09-14T00:00:00+08:00',
		'timeline_year' => 4, 'coupons' => [], 'recipient_reply' => null,
	];
}

function gift_with_schema_defaults(mixed $gift): array {
	$gift = is_array($gift) ? $gift : ($gift ? iterator_to_array($gift) : []);
	return array_merge(gift_schema_defaults(), $gift);
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
