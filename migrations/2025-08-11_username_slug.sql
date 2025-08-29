
-- 2025-08-11_username_slug.sql (MySQL/MariaDB)
-- Purpose: Add ideas.slug and users.username (nullable), with NON-UNIQUE indexes for backfill phase.
-- Note: Uses INFORMATION_SCHEMA + dynamic SQL for idempotency. No UNIQUE constraints yet.

START TRANSACTION;

-- 1) ideas.slug (nullable for backfill)
SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ideas' AND COLUMN_NAME = 'slug'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `ideas` ADD COLUMN `slug` VARCHAR(160) NULL AFTER `title`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Non-unique index for slug (safe prior to backfill)
SET @idx_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ideas' AND INDEX_NAME = 'idx_ideas_slug'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX `idx_ideas_slug` ON `ideas` (`slug`)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- 2) users.username (nullable for backfill)
SET @users_tbl := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
);
-- Only proceed if users table exists
SET @sql := IF(@users_tbl = 1, 'SELECT 1', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'username'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) NULL AFTER `display_name`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Non-unique index for username (safe prior to backfill)
SET @idx_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_users_username'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX `idx_users_username` ON `users` (`username`)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

COMMIT;

-- Follow-up after backfill:
--   ALTER TABLE `ideas` ADD CONSTRAINT `uq_ideas_slug` UNIQUE (`slug`);
--   ALTER TABLE `users` ADD CONSTRAINT `uq_users_username` UNIQUE (`username`);
