-- Decouple lightweight Likes from pledged Tokens
-- Run this in phpMyAdmin on the production database.

-- 1) Add likes column to ideas (if missing)
ALTER TABLE ideas
  ADD COLUMN IF NOT EXISTS likes INT UNSIGNED NOT NULL DEFAULT 0 AFTER tokens;

-- 2) Create like_events table (audit + rate-limit support)
CREATE TABLE IF NOT EXISTS like_events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  idea_id INT UNSIGNED NOT NULL,
  ip VARBINARY(16) NULL,
  user_agent VARCHAR(255) NULL,
  day DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (idea_id, day),
  CONSTRAINT fk_like_events_idea
    FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note:
-- We are NOT backfilling likes from existing token_events, because
-- previous “+Token” clicks represented tokens (not likes).
-- Likes begin at 0 under the new model.

-- (Optional) If you want token provenance later, add this:
-- ALTER TABLE token_events
--   ADD COLUMN IF NOT EXISTS source ENUM('tap','pledge') NOT NULL DEFAULT 'pledge' AFTER delta;
-- Then ensure future pledges write source='pledge'.
