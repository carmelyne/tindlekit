<?php
/**
 * config.php
 * Loads database credentials from a local .env file (no Composer deps).
 * Falls back to defaults for local dev if .env is absent.
 */

// Minimal .env loader (supports KEY=VALUE, ignores comments/blank lines)
if (!function_exists('load_env')) {
function load_env($path)
{
    if (!file_exists($path) || !is_readable($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(ltrim($line), '#') === 0) continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        // Strip surrounding quotes
        if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
            (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
            $val = substr($val, 1, -1);
        }
        // Do not overwrite existing env
        if (getenv($key) === false) {
            putenv("$key=$val");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
}
}

// Load .env from project root (same dir as this config)
load_env(__DIR__ . '/.env');

// Read from env with defaults (safe for local dev)
$DB_HOST    = getenv('DB_HOST') ?: '127.0.0.1';
$DB_PORT    = getenv('DB_PORT') ?: '3306';
$DB_NAME    = getenv('DB_NAME') ?: '';
$DB_USER    = getenv('DB_USER') ?: 'root';
$DB_PASS    = getenv('DB_PASS') ?: '';
$DB_CHARSET = getenv('DB_CHARSET') ?: 'utf8mb4';

// Environment-based database configuration
$ENV = getenv('ENV') ?: 'production';

if ($ENV === 'local') {
    // Local development with SQLite
    $DB_DSN = getenv('DB_DSN_LOCAL') ?: 'sqlite:./tindlekit.db';
    $DB_USER = '';
    $DB_PASS = '';
} else {
    // Production with MySQL
    $DB_DSN = getenv('DB_DSN');
    if (!$DB_DSN) {
        $DB_DSN = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset={$DB_CHARSET}";
    }
}

// Cloudflare Turnstile keys (read from env; exposed for templates)
$CF_TURNSTILE_SITE_KEY = getenv('CF_TURNSTILE_SITE_KEY') ?: '';
$CF_TURNSTILE_SECRET   = getenv('CF_TURNSTILE_SECRET') ?: '';
