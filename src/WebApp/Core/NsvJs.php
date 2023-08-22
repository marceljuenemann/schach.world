<?php

namespace Nsv\WebApp\Core;

/**
 * Utility for integrating the React components from /javascript.
 */
class NsvJs {
  const DEV_SERVER = 'http://localhost:6464/static/js/bundle.js';
  const PROD_PATH = '/core/js-build/';
  const SCRIPT_PATTERN = '/^main\.[0-9a-z]+\.js$/';

  private string $projectDir;

  function __construct(Kernel $kernel) {
    $this->projectDir = $kernel->getProjectDir();
  }

  /**
   * Returns the URL of the JavaScript build.
   */
  function scriptUrl(): string {
    if ($_ENV['NSV_JS_DEV'] === 'true') {
      return self::DEV_SERVER;
    }

    // The filename contains a hash in order to ensure browsers don't use an
    // outdated version from cache. We find out the current filename here. 
    $jsDir = $this->projectDir . '/public' . self::PROD_PATH;
    foreach (scandir($jsDir) as $filename) {
      if (preg_match(self::SCRIPT_PATTERN, $filename)) {
        return self::PROD_PATH . $filename;
      }
    }
    throw new \Exception("nsv.js not found");
  }
}
