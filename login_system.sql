CREATE DATABASE IF NOT EXISTS login_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE login_system;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(24) NOT NULL DEFAULT 'Farm Staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(24) NOT NULL DEFAULT 'Administrator';

CREATE TABLE IF NOT EXISTS sensor_thresholds (
    sensor_key VARCHAR(32) PRIMARY KEY,
    display_name VARCHAR(64) NOT NULL,
    unit VARCHAR(16) NOT NULL DEFAULT '',
    scale_min DECIMAL(10,2) NOT NULL,
    scale_max DECIMAL(10,2) NOT NULL,
    ideal_min DECIMAL(10,2) NOT NULL,
    ideal_max DECIMAL(10,2) NOT NULL,
    decimals TINYINT UNSIGNED NOT NULL DEFAULT 0,
    display_order TINYINT UNSIGNED NOT NULL DEFAULT 0
);

INSERT INTO sensor_thresholds (sensor_key, display_name, unit, scale_min, scale_max, ideal_min, ideal_max, decimals, display_order) VALUES
    ('temperature', 'Temperature', 'C', 10, 40, 18, 24, 1, 1),
    ('humidity', 'Humidity', '%', 30, 100, 50, 70, 0, 2),
    ('ph', 'pH level', '', 4, 9, 5.5, 6.5, 1, 3),
    ('tds', 'TDS', 'ppm', 0, 1500, 560, 840, 0, 4)
ON DUPLICATE KEY UPDATE display_name = VALUES(display_name);

CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    attempt_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
    verified_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_resets_user_id (user_id),
    INDEX idx_password_resets_expires_at (expires_at),
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE password_resets
    ADD COLUMN IF NOT EXISTS attempt_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS verified_at DATETIME NULL;

-- No demo user seeded: open register.php to create an account.
