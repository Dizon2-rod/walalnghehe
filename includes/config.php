<?php

declare(strict_types=1);

session_start();

// Load simple KEY=value pairs from the project root without an extra dependency.
$envFile = __DIR__ . '/../.env';
if (is_readable($envFile)) {
	foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
		$line = trim($line);
		if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
		[$key, $value] = explode('=', $line, 2);
		$key = trim($key);
		$value = trim($value);
		if ($value !== '' && (($value[0] ?? '') === '"' || ($value[0] ?? '') === "'")) $value = trim($value, "\"'");
		if ($key !== '' && getenv($key) === false) putenv($key . '=' . $value);
	}
}

// Composer autoload
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
	require_once $autoloadPath;
}
require_once __DIR__ . '/mysql_collection.php';

// Env helpers (Windows friendly)
function env_get(string $key, ?string $default = null): ?string {
	$value = getenv($key);
	return ($value === false || $value === '') ? $default : $value;
}

// Base configuration
$MYSQL_HOST = env_get('MYSQL_HOST', '127.0.0.1');
$MYSQL_PORT = env_get('MYSQL_PORT', '3306');
$MYSQL_DB = env_get('MYSQL_DB', 'monthsary');
$MYSQL_USER = env_get('MYSQL_USER', 'root');
$MYSQL_PASSWORD = env_get('MYSQL_PASSWORD', '');

$APP_BASE_URL = env_get('APP_BASE_URL', null);
if ($APP_BASE_URL === null) {
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
	$base   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
	$APP_BASE_URL = $scheme . '://' . $host . ($base ? $base . '/' : '/');
}

$APP_BASE_PATH = rtrim((string)env_get('APP_BASE_PATH', '/walalnghehe'), '/');

static $pdo = null;
function db(): PDO {
	global $pdo, $MYSQL_HOST, $MYSQL_PORT, $MYSQL_DB, $MYSQL_USER, $MYSQL_PASSWORD;
	if ($pdo === null) {
		$dsn = "mysql:host={$MYSQL_HOST};port={$MYSQL_PORT};dbname={$MYSQL_DB};charset=utf8mb4";
		$pdo = new PDO($dsn, $MYSQL_USER, $MYSQL_PASSWORD, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
	}
	return $pdo;
}

// Collections
function col_users(): \MysqlCollection { return new \MysqlCollection(db(), 'users'); }
function col_gifts(): \MysqlCollection { return new \MysqlCollection(db(), 'gifts'); }
function col_settings(): \MysqlCollection { return new \MysqlCollection(db(), 'settings'); }
function col_pets(): \MysqlCollection { return new \MysqlCollection(db(), 'pets'); }

// Ensure upload dir exists
$uploads = __DIR__ . '/../public/uploads';
if (!is_dir($uploads)) {
	@mkdir($uploads, 0775, true);
}

?>
