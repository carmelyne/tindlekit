import { test, expect } from '@playwright/test';

test('pledge form shows and validates', async ({ page }) => {
  await page.goto('/idea.php?id=1'); // adjust route
  await expect(page.getByText('Send a Pledge')).toBeVisible();

  // Fill the form
  await page.getByLabel('Your name').fill('Test User');
  await page.getByLabel('Email').fill('test@example.com');

  // Choose Token pledge
  await page.getByRole('button', { name: /Token/i }).click();
  await page.getByLabel('Tokens').fill('5');

  // Bypass Turnstile for CI (optional): if you expose a testing hook, e.g. window.__bypassTurnstile = true
  // Otherwise, annotate this test to run only in environments where Turnstile is disabled/bypassed.

  // Submit
  await page.getByRole('button', { name: /Send Pledge/i }).click();

  // Expect success toast/message
  await expect(page.getByText(/Thank you for your pledge/i)).toBeVisible();
});
