<?php
// tests/bootstrap_e2e.php — Initialize a SQLite DB compatible with E2E flows
// Creates the tables/columns used by create_idea, idea view, and pledging.

// Allow external env to set DB_DSN/USER/PASS; default to file in repo
$dsn  = getenv('DB_DSN') ?: 'sqlite:' . __DIR__ . '/../tindlekit_e2e.db';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';

// Helpful defaults for tests
if (getenv('BYPASS_TURNSTILE') === false) {
  putenv('BYPASS_TURNSTILE=1');
}

try {
  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (Throwable $e) {
  fwrite(STDERR, "Failed to connect for E2E bootstrap: " . $e->getMessage() . "\n");
  exit(1);
}

// Create tables with a schema that matches what api.php and idea.php expect (SQLite-friendly)
$sql = <<<SQL
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS ideas (
  id                   INTEGER PRIMARY KEY AUTOINCREMENT,
  title                TEXT NOT NULL,
  slug                 TEXT,
  summary              TEXT NOT NULL,
  tokens               INTEGER NOT NULL DEFAULT 0,
  likes                INTEGER NOT NULL DEFAULT 0,
  category             TEXT NOT NULL DEFAULT 'Other',
  tags                 TEXT NULL,
  created_at           TEXT NOT NULL DEFAULT (datetime('now')),
  updated_at           TEXT NULL,
  submit_ip            BLOB NULL,
  url                  TEXT NULL,
  video_url            TEXT NULL,
  file_path            TEXT NULL,
  file_mime            TEXT NULL,
  file_size            INTEGER NULL,
  submitter_name       TEXT NOT NULL,
  submitter_email      TEXT NOT NULL,
  submitter_user_id    INTEGER NULL,
  license_type         TEXT NOT NULL,
  support_needs        TEXT NULL
);
CREATE INDEX IF NOT EXISTS idx_ideas_slug ON ideas(slug);
CREATE INDEX IF NOT EXISTS idx_ideas_created_at ON ideas(created_at);
CREATE INDEX IF NOT EXISTS idx_ideas_submitter_email ON ideas(submitter_email);

-- Public users table used for profile lookups
CREATE TABLE IF NOT EXISTS users (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id        INTEGER NULL,  -- optional link to auth_users.id
  email          TEXT NOT NULL UNIQUE,
  display_name   TEXT NULL,
  username       TEXT NULL,
  created_at     TEXT NOT NULL DEFAULT (datetime('now')),
  updated_at     TEXT NULL
);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);

-- Optional auth table referenced by submitter_user_id
CREATE TABLE IF NOT EXISTS auth_users (
  id             INTEGER PRIMARY KEY AUTOINCREMENT,
  email          TEXT NOT NULL UNIQUE,
  password_hash  TEXT NULL,
  email_verified_at TEXT NULL,
  created_at     TEXT NOT NULL DEFAULT (datetime('now')),
  updated_at     TEXT NULL
);

-- Idea supporters / pledges
CREATE TABLE IF NOT EXISTS idea_interest (
  id               INTEGER PRIMARY KEY AUTOINCREMENT,
  idea_id          INTEGER NOT NULL,
  supporter_name   TEXT NOT NULL,
  supporter_email  TEXT NOT NULL,
  pledge_type      TEXT NOT NULL, -- 'time' | 'mentorship' | 'token'
  pledge_details   TEXT NULL,
  created_at       TEXT NOT NULL DEFAULT (datetime('now')),
  FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE
);

-- Token events log
CREATE TABLE IF NOT EXISTS token_events (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  idea_id     INTEGER NOT NULL,
  delta       INTEGER NOT NULL DEFAULT 1,
  reason      TEXT NULL,
  actor_ip    BLOB NULL,
  user_agent  TEXT NULL,
  created_at  TEXT NOT NULL DEFAULT (datetime('now')),
  FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_token_events_idea_created ON token_events(idea_id, created_at);

-- Per-IP daily token tap limit helper (used by add_token endpoint)
CREATE TABLE IF NOT EXISTS token_ip_daily (
  idea_id   INTEGER NOT NULL,
  ip        BLOB NOT NULL,
  day       TEXT NOT NULL,
  count     INTEGER NOT NULL DEFAULT 0,
  PRIMARY KEY (idea_id, ip, day),
  FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE
);

-- Like events (rate limit helper for likes)
CREATE TABLE IF NOT EXISTS like_events (
  id        INTEGER PRIMARY KEY AUTOINCREMENT,
  idea_id   INTEGER NOT NULL,
  ip        BLOB NOT NULL,
  day       TEXT NOT NULL,
  user_agent TEXT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_like_events_key ON like_events(idea_id, ip, day);
SQL;

$pdo->exec($sql);

echo "E2E SQLite database initialized at DSN={$dsn}\n";

