<?php
/**
 * Plugin Name: NSV v3
 * Plugin URI: https://nsv-online.de/
 * Description: Core functionality turning nsv-online.de into an actual web app.
 * Version: 1.0
 * Author: Marcel Jünemann
 * Author URI: http://marcel.world
 */

require_once(ABSPATH . '../../vendor/autoload.php');

// Activate the Symfony based webapp? 
add_filter('template_include', function($template) {
  global $wp;
  $prefixes = [
    'v3',
    'ligen',
    '_error'
  ];
  foreach ($prefixes as $prefix) {
    if (str_starts_with($wp->request, $prefix)) {
      http_response_code(200);  // WordPress might have set to 404 already.
      return locate_template('symfony.php');
    }
  }
  return $template;
});
