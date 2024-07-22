<?php

namespace Nsv\WebApp\Core;

/**
 * Utility for integrating the React components from /javascript.
 */
class NsvJs {
  const DEV_SERVER = 'http://localhost:6460/static/js/bundle.js';
  const BUILD_MANIFEST = '/public/core/js-build/asset-manifest.json';
  const BUILD_PATH = '/core/js-build';
  const NG_BUILD_PATH = '/core/ng-build/browser';

  private string $projectDir;

  function __construct(Kernel $kernel) {
    $this->projectDir = $kernel->getProjectDir();
  }

  /**
   * Returns the HTML for bootstraping nsv-ng (the Angular frontend).
   */
  function nsvNgTags() {
    // Use local angular development server.
    if ($_ENV['NSV_NG_DEV'] === 'true') {
      return <<<'ENDHTML'
        <link rel="stylesheet" href="http://localhost:4200/styles.css">
        <script src="http://localhost:4200/polyfills.js" type="module"></script>
        <script src="http://localhost:4200/main.js" type="module"></script>
        <!-- For auto-reload -->
        <script type="module" src="http://localhost:4200/@vite/client"></script>
      ENDHTML;
    }

    // Find production filenames, which contain a hash.
    $files = scandir($this->projectDir . '/public' . self::NG_BUILD_PATH);
    $findPath = function($prefix) use ($files) {
      foreach ($files as $file) {
        if (str_starts_with($file, $prefix)) {
          return self::NG_BUILD_PATH . '/' . $file;
        }
      }
    };

    $html = '<link rel="stylesheet" href="'.$findPath('styles-').'"></head>';
    $html .= '<script src="'.$findPath('polyfills-').'" type="module"></script>';
    $html .= '<script src="'.$findPath('main-').'" type="module"></script>';
    return $html;
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
