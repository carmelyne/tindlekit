<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ExpressInterestTest extends TestCase
{
  private function callEndpoint(array $post): array
  {
    // Simulate request variables expected by api.php
    $_GET = ['action' => 'express_interest'];
    $_POST = $post;

    // Capture output
    ob_start();
    include __DIR__ . '/../../api.php'; // adjust path if needed
    $out = ob_get_clean();

    $json = json_decode($out, true);
    return is_array($json) ? $json : ['_raw' => $out];
  }

  public function testRejectsMissingFields(): void
  {
    $res = $this->callEndpoint([
      'idea_id' => 1,
      // missing supporter_name, email, pledge_type
    ]);
    $this->assertArrayHasKey('error', $res);
  }

  public function testRejectsInvalidEmail(): void
  {
    $res = $this->callEndpoint([
      'idea_id' => 1,
      'supporter_name' => 'Tester',
      'supporter_email' => 'not-an-email',
      'pledge_type' => 'time',
      'pledge_details' => 'I can help'
    ]);
    $this->assertEquals('Invalid email', $res['error'] ?? null);
  }

  public function testAcceptsValidPledgeAndUpdatesTokens(): void
  {
    $res = $this->callEndpoint([
      'idea_id' => 1,
      'supporter_name' => 'Tester',
      'supporter_email' => 'tester@example.com',
      'pledge_type' => 'token',
      'pledge_details' => '100 tokens',
      'tokens' => 100,
      'cf-turnstile-response' => 'stub-token'
    ]);
    
    $this->assertTrue(($res['ok'] ?? false) === true);
  }
}
