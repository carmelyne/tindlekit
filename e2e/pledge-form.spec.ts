import { test, expect } from '@playwright/test';

test('pledge form shows and validates', async ({ page }) => {
  // First create a test idea via API
  const response = await page.request.post('/api.php?action=create_idea', {
    form: {
      submitter_name: 'Test Creator',
      submitter_email: 'creator@test.com',
      title: 'Test AI Idea',
      summary: 'A test idea for E2E testing',
      license_type: 'MIT',
      support_needs: 'Testing support',
      category: 'Other',
      tags: 'test,ai'
    }
  });
  
  const data = await response.json();
  expect(data.ok).toBe(true);
  const ideaId = data.id;
  
  await page.goto(`/idea.php?id=${ideaId}`);
  
  // Wait for page to load and check for key elements
  await expect(page.getByText('Test AI Idea')).toBeVisible();
  await expect(page.getByText('Send Pledge')).toBeVisible();

  // Fill the form
  await page.getByPlaceholder('Your name').fill('Test User');
  await page.getByPlaceholder('Your email').fill('test@example.com');

  // Choose Token pledge and wait for token input to become visible
  await page.getByRole('button', { name: /Token/i }).click();
  await expect(page.getByPlaceholder('Number of AI Tokens to pledge')).toBeVisible();
  await page.getByPlaceholder('Number of AI Tokens to pledge').fill('5');

  // Bypass Turnstile for CI (optional): if you expose a testing hook, e.g. window.__bypassTurnstile = true
  // Otherwise, annotate this test to run only in environments where Turnstile is disabled/bypassed.

  // Submit
  await page.getByRole('button', { name: /Send Pledge/i }).click();

  // Expect success toast/message
  await expect(page.getByText(/Thank you for your pledge/i)).toBeVisible();
});
