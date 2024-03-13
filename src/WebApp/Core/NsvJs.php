<?php

namespace Nsv\WebApp\Core;

/**
 * Utility for integrating the React components from /javascript.
 */
class NsvJs {
  const DEV_SERVER = 'http://localhost:6460/static/js/bundle.js';
  const BUILD_MANIFEST = '/public/core/js-build/asset-manifest.json';
  const BUILD_PATH = '/core/js-build';

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
    // outdated version from cache. We find out the current filename from the manifest.
    $manifest = json_decode(file_get_contents($this->projectDir . self::BUILD_MANIFEST), JSON_OBJECT_AS_ARRAY);
    return self::BUILD_PATH . $manifest['files']['main.js'];
  }
}
