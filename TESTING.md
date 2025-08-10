# Testing Guide for Tindlekit

This document explains how to run the test suite for Tindlekit (Andrej Tokens platform).

## Prerequisites

- PHP 8.2+ with extensions: `mbstring`, `curl`, `pdo_sqlite`
- Node.js 20+
- Composer

## Test Structure

The project includes two types of tests:

1. **PHP Unit Tests** - Test API endpoints using PHPUnit with SQLite in-memory database
2. **E2E Tests** - Test user flows using Playwright with a local PHP server

## Running PHP Unit Tests

### Install dependencies
```bash
composer install
```

### Run API tests
```bash
composer test:api
```

This will:
- Use SQLite in-memory database (no external dependencies)
- Bypass Turnstile validation (`BYPASS_TURNSTILE=1`)
- Test the `express_interest` API endpoint with various scenarios

### Test Cases Covered
- ✅ Missing required fields → error
- ✅ Invalid email → error: "Invalid email"
- ✅ Valid token pledge increments ideas.tokens and returns `{ ok: true }`
- ✅ Turnstile bypass when `BYPASS_TURNSTILE=1`

## Running E2E Tests

### Install dependencies
```bash
npm install
npx playwright install --with-deps
```

### Start local development server
```bash
php -S 127.0.0.1:8080 -t .
```

### Run E2E tests (in another terminal)
```bash
npm run test:e2e
```

### Environment Variables for E2E
- `E2E_BASE_URL` - Base URL for tests (default: `http://127.0.0.1:8080`)
- `BYPASS_TURNSTILE=1` - Skip Turnstile validation for testing

### E2E Test Coverage
- ✅ Pledge form visibility and interaction
- ✅ Form validation (name, email, pledge type)
- ✅ Token pledge submission flow
- ✅ Success message display

## CI/CD

The project uses GitHub Actions for continuous integration:

### Workflow Triggers
- Push to `main` or `dev` branches
- Pull requests

### Jobs
1. **php-tests** - Runs PHP unit tests with PHPUnit
2. **e2e** - Runs Playwright E2E tests (depends on php-tests)
3. **lint** - Placeholder for future ESLint integration

### Environment Setup in CI
- PHP 8.2 with required extensions
- Node.js 20
- Automatic Playwright browser installation
- Test server starts on `127.0.0.1:8080`
- `BYPASS_TURNSTILE=1` for both test suites

## Local Development Setup

### 1. Clone and install
```bash
git clone <repo-url>
cd andrej-tokens
composer install
npm install
```

### 2. Environment configuration
```bash
cp .env.example .env
# Edit .env with your configuration
```

### 3. Database setup
```bash
# Create database and run migrations
# See schema.sql and migrations/ directory
```

### 4. Run tests
```bash
# PHP unit tests
composer test:api

# E2E tests (start server first)
php -S 127.0.0.1:8080 -t . &
npm run test:e2e
```

## Test Configuration Files

- `phpunit.xml.dist` - PHPUnit configuration
- `playwright.config.ts` - Playwright configuration
- `tests/bootstrap.php` - Test database setup and mocks
- `.github/workflows/ci.yml` - CI configuration

## Debugging

### PHPUnit Debug
```bash
# Verbose output
./vendor/bin/phpunit --verbose

# Stop on failure
./vendor/bin/phpunit --stop-on-failure
```

### Playwright Debug
```bash
# Run with browser UI
npx playwright test --headed

# Debug mode
npx playwright test --debug

# Generate test report
npx playwright show-report
```

## Database Schema for Tests

The test bootstrap creates a minimal SQLite schema:
```sql
CREATE TABLE ideas (id INTEGER PRIMARY KEY, tokens INTEGER DEFAULT 0);
CREATE TABLE idea_interest (
  id INTEGER PRIMARY KEY,
  idea_id INTEGER, supporter_name TEXT, supporter_email TEXT,
  pledge_type TEXT, pledge_details TEXT
);
CREATE TABLE token_events (
  id INTEGER PRIMARY KEY,
  idea_id INTEGER, delta INTEGER, reason TEXT, actor_ip BLOB, user_agent TEXT
);
```

## API Endpoint Testing

The `express_interest` endpoint is tested with:

### Valid Request
```php
POST /api.php?action=express_interest
{
  "idea_id": 1,
  "supporter_name": "Test User",
  "supporter_email": "test@example.com",
  "pledge_type": "token",
  "pledge_details": "Supporting this idea",
  "tokens": 100
}
```

### Expected Response
```json
{
  "ok": true
}
```

## Troubleshooting

### Common Issues

1. **Database connection errors**: Ensure SQLite extension is installed
2. **Playwright browser errors**: Run `npx playwright install --with-deps`
3. **Server port conflicts**: Change port in test configuration if 8080 is busy
4. **Turnstile errors**: Ensure `BYPASS_TURNSTILE=1` is set in test environment

### Getting Help

- Check CI logs in GitHub Actions
- Review test output for specific error messages
- Ensure all dependencies are installed correctly
- Verify environment variables are set properly