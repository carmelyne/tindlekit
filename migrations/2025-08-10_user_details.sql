-- 2025-08-10_user_details.sql
-- Purpose: support user.php by adding a lightweight users table,
--          fast lookups by submitter_email, and an aggregate signals view.
-- Notes:
-- - Safe to run multiple times if guarded with IF NOT EXISTS.
-- - Assumes existing table: ideas(id, title, summary, category, tokens, likes, submitter_name, submitter_email, created_at, ...)
-- - No destructive changes.

START TRANSACTION;

-- 1) Speed up lookups for profiles by email
-- Create index if it does not exist (MariaDB-safe)
SET @idx_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'ideas'
    AND INDEX_NAME = 'idx_ideas_submitter_email'
);
SET @sql := IF(@idx_exists = 0,
  'CREATE INDEX idx_ideas_submitter_email ON ideas (submitter_email)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS users (
  email VARCHAR(255) NOT NULL PRIMARY KEY,
  display_name VARCHAR(255) NULL,
  bio TEXT NULL,
  avatar_url VARCHAR(1024) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A) Authentication users (future-ready identity layer)
CREATE TABLE IF NOT EXISTS auth_users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NULL,
  email_verified_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- B) Link public users → auth_users (nullable during transition) — MariaDB-safe
-- Add users.user_id if missing
SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'user_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE users ADD COLUMN user_id BIGINT UNSIGNED NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add FK users.user_id → auth_users(id) if missing
SET @fk_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND CONSTRAINT_NAME = 'fk_users_auth'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE users ADD CONSTRAINT fk_users_auth FOREIGN KEY (user_id) REFERENCES auth_users(id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- C) Link ideas → auth_users (nullable during transition); keep submitter_email for reporting — MariaDB-safe
-- Add ideas.submitter_user_id if missing
SET @col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ideas' AND COLUMN_NAME = 'submitter_user_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE ideas ADD COLUMN submitter_user_id BIGINT UNSIGNED NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add FK ideas.submitter_user_id → auth_users(id) if missing
SET @fk_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ideas' AND CONSTRAINT_NAME = 'fk_ideas_auth'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE ideas ADD CONSTRAINT fk_ideas_auth FOREIGN KEY (submitter_user_id) REFERENCES auth_users(id) ON DELETE SET NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2a) Backfill users from existing ideas submitter data (idempotent)
INSERT INTO users (email, display_name)
SELECT DISTINCT i.submitter_email, NULLIF(i.submitter_name, '')
FROM ideas i
LEFT JOIN users u ON u.email = i.submitter_email
WHERE i.submitter_email IS NOT NULL AND i.submitter_email <> '' AND u.email IS NULL;

-- D) Backfill auth_users from existing submitter emails (idempotent)
INSERT IGNORE INTO auth_users (email)
SELECT DISTINCT LOWER(TRIM(i.submitter_email))
FROM ideas i
WHERE i.submitter_email IS NOT NULL AND i.submitter_email <> '';

-- E) Attach users.user_id via normalized email
UPDATE users u
JOIN auth_users a ON LOWER(u.email) = a.email
SET u.user_id = a.id
WHERE u.user_id IS NULL;

-- F) Attach ideas.submitter_user_id via normalized email
UPDATE ideas i
JOIN auth_users a ON LOWER(i.submitter_email) = a.email
SET i.submitter_user_id = a.id
WHERE i.submitter_user_id IS NULL;


-- 3) Signals view: aggregate tokens/likes/ideas per user (auth-aware)
DROP VIEW IF EXISTS user_signals_view;
CREATE VIEW user_signals_view AS
SELECT
  COALESCE(a.id, NULL)            AS user_id,
  COALESCE(a.email, i.submitter_email) AS email,
  COALESCE(NULLIF(u.display_name, ''), NULLIF(i.submitter_name, ''), a.email) AS display_name,
  COUNT(*) AS ideas_count,
  COALESCE(SUM(i.tokens), 0) AS total_tokens,
  COALESCE(SUM(i.likes), 0)  AS total_likes,
  MIN(i.created_at) AS first_idea_at,
  MAX(i.created_at) AS last_idea_at
FROM ideas i
LEFT JOIN auth_users a ON a.id = i.submitter_user_id OR a.email = LOWER(TRIM(i.submitter_email))
LEFT JOIN users u ON u.user_id = a.id OR u.email = i.submitter_email
WHERE i.submitter_email IS NOT NULL AND i.submitter_email <> ''
GROUP BY COALESCE(a.id, 0), COALESCE(a.email, i.submitter_email);

COMMIT;

-- ROLLBACK plan (manual):
--   DROP VIEW IF EXISTS user_signals_view;
--   DROP INDEX idx_ideas_submitter_email ON ideas; -- if created
--   DROP TABLE IF EXISTS users; -- only if not yet used elsewhere

-- QA checklist:
-- [ ] SELECT * FROM user_signals_view LIMIT 5;
-- [ ] SELECT * FROM users WHERE email = 'hello@carmelyne.com';
-- [ ] EXPLAIN SELECT * FROM ideas WHERE submitter_email = 'hello@carmelyne.com'; -- uses idx

-- [ ] SELECT * FROM user_signals_view WHERE email='hello@carmelyne.com';
-- [ ] SELECT u.*, a.* FROM users u LEFT JOIN auth_users a ON a.id=u.user_id WHERE u.email='hello@carmelyne.com';
