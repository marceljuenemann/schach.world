<?php

namespace Nsv\WebApp\Core;

use Nsv\WebApp\Core\WordPress\Auth;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Runtime\SymfonyRuntime;

class Kernel extends BaseKernel
{
  use MicroKernelTrait;

  /**
   * Run the NSV WebApp, letting it handle the current request.
   */
  static function run(string $projectDir) {
    // Use SymfonyRuntime to load config from .env files.
    // This is similar to vendor/autoload_runtime.php, which we can't use directly
    // from our WordPress plugin.
    $runtime = new SymfonyRuntime(['project_dir' => $projectDir]);
    [$app, $args] = $runtime->getResolver(function (array $context) {
      $env = $context[0];
      return new Kernel($env['APP_ENV'], (bool) $env['APP_DEBUG']);
    })->resolve();
    $app = $app($args);
    $runtime->getRunner($app)->run();
  }
}
