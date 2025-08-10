-- The Andrej Effect — database schema (MySQL/MariaDB)
-- Charset + collation
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Ideas submitted by users
CREATE TABLE IF NOT EXISTS ideas (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title         VARCHAR(200) NOT NULL,
  summary       TEXT NOT NULL,
  tokens        INT UNSIGNED NOT NULL DEFAULT 0,
  category      ENUM('Education', 'Environment', 'Health', 'Open Source', 'Art', 'Other') NOT NULL DEFAULT 'Other',
  tags          TEXT NULL, -- comma-separated tags (treat as hashtags, store without #)
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  submit_ip     VARBINARY(16) NULL, -- store IPv4/IPv6 in binary
  url           VARCHAR(500) NULL,
  video_url     VARCHAR(500) NULL,
  file_path     VARCHAR(500) NULL,
  file_mime     VARCHAR(100) NULL,
  file_size     INT UNSIGNED NULL,
  submitter_name VARCHAR(100) NOT NULL,
  submitter_email VARCHAR(255) NOT NULL,
  license_type VARCHAR(50) NOT NULL,
  support_needs TEXT,
  PRIMARY KEY (id),
  KEY idx_tokens (tokens DESC),
  KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Token allocation events (audit trail)
CREATE TABLE IF NOT EXISTS token_events (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  idea_id       INT UNSIGNED NOT NULL,
  delta         TINYINT NOT NULL DEFAULT 1, -- usually +1, but flexible
  reason        VARCHAR(120) NULL,
  actor_ip      VARBINARY(16) NULL,
  user_agent    VARCHAR(255) NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_idea_created (idea_id, created_at),
  CONSTRAINT fk_token_events_idea
    FOREIGN KEY (idea_id) REFERENCES ideas(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: simple daily rate-limit helper (one row per idea+IP per day)
CREATE TABLE IF NOT EXISTS token_ip_daily (
  idea_id       INT UNSIGNED NOT NULL,
  ip            VARBINARY(16) NOT NULL,
  day           DATE NOT NULL,
  count         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (idea_id, ip, day),
  CONSTRAINT fk_token_ip_daily_idea
    FOREIGN KEY (idea_id) REFERENCES ideas(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed example (safe to remove)
-- INSERT INTO ideas (title, summary, tokens) VALUES
--   ('Local OSS map of public clinics', 'Crowd-sourced availability and hours for rural clinics, exported to JSON/CSV.', 3),
--   ('Low-cost edge AI kit for farmers', 'Solar + LoRa node with crop health detection; docs + CAD files open-sourced.', 5);

-- Table for idea supporters/pledges
CREATE TABLE idea_interest (
  id INT AUTO_INCREMENT PRIMARY KEY,
  idea_id INT UNSIGNED NOT NULL,
  supporter_name VARCHAR(100) NOT NULL,
  supporter_email VARCHAR(255) NOT NULL,
  pledge_type ENUM('time','mentorship') NOT NULL,
  pledge_details TEXT,
  FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
