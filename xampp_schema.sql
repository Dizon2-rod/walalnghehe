-- Monthsary Sanctuary local database for XAMPP MySQL/MariaDB
-- Import this file in phpMyAdmin.

CREATE DATABASE IF NOT EXISTS monthsary
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE monthsary;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(120) NOT NULL DEFAULT 'My Love',
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pets (
    id CHAR(36) NOT NULL PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    breed_type ENUM('white_heterochromia', 'tuxedo', 'gray_tabby') NOT NULL,
    avatar_url VARCHAR(500) NULL,
    level INT UNSIGNED NOT NULL DEFAULT 1,
    exp INT UNSIGNED NOT NULL DEFAULT 0,
    hunger TINYINT UNSIGNED NOT NULL DEFAULT 100,
    hygiene TINYINT UNSIGNED NOT NULL DEFAULT 100,
    happiness TINYINT UNSIGNED NOT NULL DEFAULT 100,
    energy TINYINT UNSIGNED NOT NULL DEFAULT 100,
    mood ENUM('ecstatic', 'happy', 'sleepy', 'hungry', 'dirty', 'sad') NOT NULL DEFAULT 'happy',
    last_fed DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_bathed DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_petted DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_slept DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pets_hunger_chk CHECK (hunger BETWEEN 0 AND 100),
    CONSTRAINT pets_hygiene_chk CHECK (hygiene BETWEEN 0 AND 100),
    CONSTRAINT pets_happiness_chk CHECK (happiness BETWEEN 0 AND 100),
    CONSTRAINT pets_energy_chk CHECK (energy BETWEEN 0 AND 100)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pet_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    pet_id CHAR(36) NOT NULL,
    action_type ENUM('feed', 'bath', 'pet', 'play', 'sleep') NOT NULL,
    stat_deltas JSON NOT NULL,
    actor VARCHAR(120) NOT NULL DEFAULT 'partner',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pet_logs_pet_fk FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    INDEX pet_logs_pet_created_idx (pet_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gifts (
    id CHAR(36) NOT NULL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    letter_content TEXT NOT NULL,
    polaroid_image_url VARCHAR(500) NULL,
    audio_bg_url VARCHAR(500) NULL,
    voice_note_url VARCHAR(500) NULL,
    unlock_password_hash VARCHAR(255) NULL,
    unlock_at DATETIME NOT NULL DEFAULT '2026-09-14 00:00:00',
    timeline_milestones JSON NOT NULL,
    scratch_coupons JSON NOT NULL,
    recipient_reaction VARCHAR(20) NULL,
    recipient_note TEXT NULL,
    is_unlocked BOOLEAN NOT NULL DEFAULT FALSE,
    month_created TINYINT UNSIGNED NULL,
    year_created SMALLINT UNSIGNED NULL,
    owner_id BIGINT UNSIGNED NULL,
    lock_hint VARCHAR(500) NULL,
    music_url VARCHAR(500) NULL,
    coupons JSON NULL,
    accepted_formats JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX gifts_unlock_at_idx (unlock_at),
    INDEX gifts_created_at_idx (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS app_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value JSON NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO pets (id, name, breed_type, avatar_url, hunger, hygiene, happiness, energy, mood)
VALUES
    (UUID(), 'Molly', 'white_heterochromia', 'assets/cats/molly.png', 90, 85, 95, 100, 'happy'),
    (UUID(), 'Mitch', 'tuxedo', 'assets/cats/mitch.png', 80, 95, 90, 85, 'happy'),
    (UUID(), 'Raica', 'gray_tabby', 'assets/cats/raica.png', 85, 80, 90, 90, 'happy')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO app_settings (setting_key, setting_value)
VALUES
    ('anniversary_date', '"2026-09-14"'),
    ('partner_nickname', '"My Love"'),
    ('theme_primary', '"#EFBBCF"'),
    ('theme_secondary', '"#E8DADA"'),
    ('theme_accent', '"#CDB4DB"')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);
