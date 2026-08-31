<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/helpers.php';

if (PHP_SAPI !== 'cli') { http_response_code(403); exit("CLI only.\n"); }
$pdo = db();
$columns = [
	'music_url' => 'VARCHAR(500) NULL', 'voice_note_url' => 'VARCHAR(500) NULL',
	'unlock_at' => "DATETIME NOT NULL DEFAULT '2026-09-14 00:00:00'",
	'timeline_milestones' => "JSON NOT NULL DEFAULT (JSON_ARRAY())", 'scratch_coupons' => "JSON NOT NULL DEFAULT (JSON_ARRAY())",
	'recipient_reaction' => 'VARCHAR(20) NULL', 'recipient_note' => 'TEXT NULL', 'is_unlocked' => 'BOOLEAN NOT NULL DEFAULT FALSE',
	'month_created' => 'TINYINT UNSIGNED NULL', 'year_created' => 'SMALLINT UNSIGNED NULL', 'owner_id' => 'BIGINT UNSIGNED NULL',
	'lock_hint' => 'VARCHAR(500) NULL', 'coupons' => 'JSON NULL', 'accepted_formats' => 'JSON NULL',
];
$existing = $pdo->query('SHOW COLUMNS FROM gifts')->fetchAll(PDO::FETCH_COLUMN);
foreach ($columns as $name => $definition) if (!in_array($name, $existing, true)) $pdo->exec("ALTER TABLE gifts ADD COLUMN `{$name}` {$definition}");
echo "MySQL gift schema migration complete.\n";