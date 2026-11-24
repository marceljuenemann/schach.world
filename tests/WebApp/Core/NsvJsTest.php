<?php

namespace Tests\WebApp\Core;

use Nsv\WebApp\Core\NsvJs;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NsvJsTest extends KernelTestCase
{
  private NsvJs $nsvJs;
  private array $originalEnv;

  protected function setUp(): void {
    $this->nsvJs = static::getContainer()->get(NsvJs::class);
    $this->originalEnv = $_ENV;
  }

  protected function tearDown(): void {
    $_ENV = $this->originalEnv;
    parent::tearDown();
  }

  public function testScriptUrl_devServer() {
    $_ENV['NSV_JS_DEV'] = 'true';
    $this->assertSame(NsvJs::DEV_SERVER, $this->nsvJs->scriptUrl());
  }

  public function testScriptUrl_build() {
    $_ENV['NSV_JS_DEV'] = 'false';
    $manifest = json_decode(file_get_contents(static::getContainer()->getParameter('kernel.project_dir') . NsvJs::BUILD_MANIFEST), true);

    $url = $this->nsvJs->scriptUrl();

    $this->assertSame('/core/js-build' . $manifest['files']['main.js'], $url);
    $this->assertMatchesRegularExpression('#^/core/js-build/static/js/main\.[0-9a-f]+\.js$#', $url);
  }

  public function testNsvNgTags_devServer() {
    $_ENV['NSV_NG_DEV'] = 'true';
    $html = $this->nsvJs->nsvNgTags();
    $this->assertStringContainsString('<script src="http://localhost:4200/main.js" type="module"></script>', $html);
    $this->assertStringContainsString('http://localhost:4200/polyfills.js', $html);
  }

  public function testNsvNgTags_build() {
    $_ENV['NSV_NG_DEV'] = 'false';
    $html = $this->nsvJs->nsvNgTags();
    $this->assertMatchesRegularExpression(
      '#^<script defer src="/core/ng-build/browser/polyfills-\w+\.js" type="module"></script>'
        . '<script defer src="/core/ng-build/browser/main-\w+\.js" type="module"></script>$#',
      $html
    );
  }
}
