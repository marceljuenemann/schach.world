<?php

namespace Tests\WebApp\Core;

use Nsv\WebApp\Core\NsvJs;
use Nsv\WebApp\Core\TwigExtension;
use Nsv\WebApp\Core\WordPress\Auth;
use Nsv\WebApp\Core\WordPress\TwigExtension as WordPressTwigExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

require_once __DIR__ . '/../../Support/wordpress-stubs.php';

class TwigExtensionTest extends TestCase
{
  private function render(string $template, Auth $auth): string {
    $nsvJs = $this->createStub(NsvJs::class);
    $nsvJs->method('nsvNgTags')->willReturn('<script src="ng.js"></script>');
    $nsvJs->method('scriptUrl')->willReturn('/nsv.js');

    $twig = new Environment(new ArrayLoader(['test' => $template]));
    $twig->addExtension(new TwigExtension($nsvJs, $auth));
    $twig->addExtension(new WordPressTwigExtension($auth));
    return $twig->render('test');
  }

  private function loggedInAuth(): Auth {
    $auth = $this->createStub(Auth::class);
    $auth->method('isLoggedIn')->willReturn(true);
    $auth->method('isAdmin')->willReturn(true);
    $auth->method('userName')->willReturn('marcel');
    $auth->method('loginRedirect')->willReturnCallback(fn($uri) => "/login?to=$uri");
    $auth->method('logoutRedirect')->willReturnCallback(fn($uri) => "/logout?to=$uri");
    return $auth;
  }

  public function testNsvFunctions() {
    $html = $this->render(
      '{{ include_nsv_ng() }}|{{ nsv_js_src() }}|{{ nsv_is_logged_in() ? "yes" : "no" }}|{{ nsv_username() }}'
        . '|{{ nsv_login_redirect("/a") }}|{{ nsv_logout_redirect("/b") }}|{{ nsv_md5("abc") }}',
      $this->loggedInAuth()
    );

    $this->assertSame(
      '<script src="ng.js"></script>|/nsv.js|yes|marcel|/login?to=/a|/logout?to=/b|' . md5('abc'),
      $html
    );
  }

  public function testStacktrace() {
    $twig = new Environment(new ArrayLoader(['test' => '{{ stacktrace(e)|raw }}']));
    $twig->addExtension(new TwigExtension($this->createStub(NsvJs::class), $this->createStub(Auth::class)));
    $exception = new \Exception('test');

    $html = $twig->render('test', ['e' => $exception]);

    $this->assertSame(PHP_EOL . '<pre>' . PHP_EOL . $exception->getTraceAsString() . PHP_EOL . '</pre>' . PHP_EOL, $html);
  }

  public function testWordPressFunctions() {
    $GLOBALS['wp_stub_filters'] = [];
    $html = $this->render(
      '{{ set_title("Termine") }}{{ get_header() }}{{ get_template_part("sidebar") }}{{ is_admin() ? "admin" : "" }}{{ get_footer() }}',
      $this->loggedInAuth()
    );

    $this->assertSame('<!-- wp:header --><!-- wp:template-part sidebar -->admin<!-- wp:footer -->', $html);

    // set_title() registers a filter that replaces the document title.
    $filter = $GLOBALS['wp_stub_filters']['document_title_parts'][0];
    $this->assertSame(['title' => 'Termine', 'site' => 'NSV'], $filter(['title' => 'Alt', 'site' => 'NSV']));
  }
}
