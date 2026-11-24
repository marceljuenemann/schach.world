<?php

namespace Tests\WebApp\Core\WordPress;

use Nsv\WebApp\Core\WordPress\Auth;
use PHPUnit\Framework\TestCase;

/**
 * Tests run outside of WordPress, so only the behavior without WordPress can be tested here.
 */
class AuthTest extends TestCase
{
  public function testOutsideOfWordPress() {
    $auth = new Auth();
    $this->assertFalse($auth->isWordPress());
    $this->assertFalse($auth->isLoggedIn());
    $this->assertFalse($auth->isAdmin());
    $this->assertFalse($auth->isAuthor());
    $this->assertNull($auth->userName());
  }

  public function testRedirects() {
    $auth = new Auth();
    $this->assertSame('/wp-login.php?redirect_to=%2Ftermine%2F%3Fa%3D1', $auth->loginRedirect('/termine/?a=1'));
    $this->assertSame('/wp-login.php?redirect_to=%2Ftermine%2F&action=logout', $auth->logoutRedirect('/termine/'));
  }
}
