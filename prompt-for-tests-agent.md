Tindlekit — Add Tests & CI (Do Not Change APIs)

Context

- Repo root: andrej-tokens/
- Backend endpoints must remain unchanged. No DB/API shape changes.
- Turnstile already integrated on the pledge form and API.

Goal
Add a minimal-yet-real test suite + GitHub Actions CI:

- PHP unit tests for api.php?action=express_interest
- Basic Playwright E2E for the pledge form flow
- CI workflow that runs both on PRs and pushes
- No inline CSS; no UI rewrites; keep current selectors/IDs working

⸻

Tasks

1) PHP Unit Tests (PHPUnit)

- Create:
- /composer.json (dev deps only if missing)
- /phpunit.xml.dist
- /tests/bootstrap.php
- /tests/Api/ExpressInterestTest.php
- Use SQLite in-memory with a tiny schema that matches express_interest expectations:
- ideas(id, tokens)
- idea_interest(...)
- token_events(...)
- Stub/feature-flag Turnstile:
- If BYPASS_TURNSTILE=1, verify_turnstile() returns true.
- Cover cases:

 1. Missing required fields → error
 2. Invalid email → error: Invalid email
 3. Valid token pledge increments ideas.tokens and returns { ok: true }
 4. (Optional) Turnstile failure when BYPASS_TURNSTILE=0 → error: turnstile_failed

- Add a Composer script:
- "test:api": "phpunit --colors=always"

2) E2E Test (Playwright)

- Create:
- /package.json with @playwright/test and "test:e2e": "playwright test"
- /playwright.config.ts (use E2E_BASE_URL or default <http://127.0.0.1:8080>)
- /e2e/pledge-form.spec.ts
- E2E scenario:
- Visit an idea page (assume /idea.php?id=1)
- Fill name + email
- Choose Token pledge, enter a small amount (e.g., 5)
- Submit → expect success message already present in the app (don’t invent UI)
- CI compatibility:
- Start a PHP built-in server in CI
- Run with BYPASS_TURNSTILE=1 so tests don’t hit Cloudflare

3) GitHub Actions (CI)

- Create: /.github/workflows/ci.yml
- Jobs:
- php-tests: setup PHP 8.2, install Composer deps, run composer test:api
- e2e (needs php-tests): setup Node 20, install, npx playwright install --with-deps, start PHP server, run npm run test:e2e
- lint placeholder step (we’ll wire ESLint later)
- Trigger: on PR and push to main and dev

4) Env Sample

- Ensure /.env.example includes:

CF_TURNSTILE_SITE_KEY=your_site_key_here
CF_TURNSTILE_SECRET=your_secret_here
APP_ENV=production

- Do not commit live secrets.

⸻

Constraints / Don’ts

- Do not modify API contracts or DB schema beyond the test bootstrap schema.
- Do not change existing selectors, IDs, or DOM contracts used by the app.
- No inline CSS; no UI refactors.
- Keep all new files minimal and documented.

⸻

Deliverables

 1. The files listed above, committed with meaningful messages.
 2. Green CI run on the PR.
 3. A short TESTING.md explaining how to run:

- composer install && composer test:api
- npm i && npx playwright install && npm run test:e2e (requires php -S 127.0.0.1:8080 -t .)

 4. Notes on any selectors you had to adjust (ideally none).

⸻

Hints (use them, or rewrite as you see fit)

- In tests/bootstrap.php, set:
- $_SERVER['REMOTE_ADDR']='127.0.0.1', $_SERVER['HTTP_USER_AGENT']='PHPUnit'
- $GLOBALS['pdo'] = new PDO('sqlite::memory:') and build the minimal schema
- A stub verify_turnstile() that honors BYPASS_TURNSTILE
- In E2E, keep locators aligned with current labels (e.g., “Send Pledge”, “Tokens”).
- In CI, sleep ~2s after starting php -S before running tests.

⸻

Acceptance Criteria

- composer test:api passes locally and in CI.
- npm run test:e2e passes locally and in CI (with BYPASS_TURNSTILE=1).
- No regressions to runtime app behavior.
- CI blocks merges on test failures.
