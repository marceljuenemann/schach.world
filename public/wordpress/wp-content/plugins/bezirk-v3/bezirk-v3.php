<?php
/**
 * Plugin Name: Bezirk Hannover v3
 * Plugin URI: https://nsv-online.de/
 * Description: Symfony integration
 * Version: 1.0
 * Author: Marcel Jünemann
 * Author URI: http://marcel.world
 */

require_once(ABSPATH . '../../vendor/autoload.php');

// Check for redirects.
require_once(dirname(__FILE__) . '/redirects.php' );

// Forward to the Symfony based WebApp for specific route prefixes. 
add_filter('template_include', function($template) {
  global $wp;
  $prefixes = [
    'anmeldung',
    'dwz',
    'vereine',
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

// Enable automatic updates despite .git folder.
add_filter('automatic_updates_is_vcs_checkout', function($checkout, $context) {
  return false;
}, 10, 2);

// Disable permalink guessing. By default, WordPress is very eager to try to find a post that matches the
// given URL. However, when we have various custom URLs, then that often gets in the way and WordPress
// intercepts an URL that we meant to handle by a custom route.
add_filter('do_redirect_guess_404_permalink', '__return_false');
