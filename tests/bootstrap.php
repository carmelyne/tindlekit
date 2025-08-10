<?php
// Minimal bootstrap for tests

// Optional: autoload if you add src/ later
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
  require __DIR__ . '/../vendor/autoload.php';
}

// Provide stubs for globals used in api.php if needed
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit';

// Hard stub: allow tests to bypass external Turnstile call
if (!function_exists('verify_turnstile')) {
  function verify_turnstile($token, $ip = null)
  {
    if (getenv('BYPASS_TURNSTILE') === '1')
      return true;
    // If you want: integrate the real function by requiring the file that defines it.
    return false;
  }
}

// Create a temporary SQLite file for tests (cleaned up after)
$testDbFile = sys_get_temp_dir() . '/tindlekit_test_' . uniqid() . '.db';

// Set environment variables that config.php will read
putenv('DB_DSN=sqlite:' . $testDbFile);
putenv('DB_USER=');  
putenv('DB_PASS=');
putenv('ENV=test');

// Store test DB file path for cleanup
$GLOBALS['test_db_file'] = $testDbFile;

// Create in-memory database and populate it
// This will be recreated by api.php when it includes config.php
$testPdo = new PDO(getenv('DB_DSN'), getenv('DB_USER'), getenv('DB_PASS'));
$testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create minimal schema required by express_interest
$testPdo->exec("
  CREATE TABLE ideas (
    id INTEGER PRIMARY KEY, 
    tokens INTEGER DEFAULT 0,
    title TEXT DEFAULT 'Test Idea',
    summary TEXT DEFAULT 'Test summary',
    submitter_name TEXT DEFAULT 'Test User',
    submitter_email TEXT DEFAULT 'test@example.com',
    license_type TEXT DEFAULT 'MIT',
    category TEXT DEFAULT 'Other',
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
  );
  CREATE TABLE idea_interest (
    id INTEGER PRIMARY KEY,
    idea_id INTEGER, 
    supporter_name TEXT, 
    supporter_email TEXT,
    pledge_type TEXT CHECK (pledge_type IN ('time', 'mentorship', 'token')), 
    pledge_details TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
  );
  CREATE TABLE token_events (
    id INTEGER PRIMARY KEY,
    idea_id INTEGER, 
    delta INTEGER, 
    reason TEXT, 
    actor_ip BLOB, 
    user_agent TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
  );
");

// Seed an idea
$testPdo->exec("INSERT INTO ideas (id, tokens) VALUES (1, 0)");
