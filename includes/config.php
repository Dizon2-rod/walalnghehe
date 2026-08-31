<?php

declare(strict_types=1);

session_start();

// Composer autoload
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
	require_once $autoloadPath;
}

use MongoDB\Client;

// Env helpers (Windows friendly)
function env_get(string $key, ?string $default = null): ?string {
	$value = getenv($key);
	return ($value === false || $value === '') ? $default : $value;
}

// Base configuration
$MONGODB_URI = env_get('MONGODB_URI', 'mongodb://127.0.0.1:27017');
$MONGODB_DB  = env_get('MONGODB_DB', 'monthsary');

$APP_BASE_URL = env_get('APP_BASE_URL', null);
if ($APP_BASE_URL === null) {
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
	$base   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
	$APP_BASE_URL = $scheme . '://' . $host . ($base ? $base . '/' : '/');
}

// MongoDB client and database singleton
static $mongoClient = null;
static $mongoDb = null;

function db_client(): Client {
	global $mongoClient, $MONGODB_URI;
	if ($mongoClient === null) {
		$mongoClient = new Client($MONGODB_URI);
	}
	return $mongoClient;
}

function db() {
	global $mongoDb, $MONGODB_DB;
	if ($mongoDb === null) {
		$mongoDb = db_client()->selectDatabase($MONGODB_DB);
	}
	return $mongoDb;
}

// Collections
function col_users() { return db()->selectCollection('users'); }
function col_gifts() { return db()->selectCollection('gifts'); }
function col_settings() { return db()->selectCollection('settings'); }

// Ensure upload dir exists
$uploads = __DIR__ . '/../public/uploads';
if (!is_dir($uploads)) {
	@mkdir($uploads, 0775, true);
}

?>
