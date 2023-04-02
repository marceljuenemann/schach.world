<?php
/**
 * Plugin Name: NSV Core
 * Plugin URI: https://nsv-online.de/
 * Description: Kernfunktionalität für alle NSV plugins
 * Version: 1.0
 * Author: Marcel Jünemann
 * Author URI: http://marcel.world
 */

// Enable automatic class loading for classes in NSV namespace.
spl_autoload_register(function($classname) {
  $parts = explode('\\', strtolower($classname));
  if ($parts[0] != 'nsv') return;
  $filename = WP_PLUGIN_DIR . '/nsv-' . $parts[1] . '/' . implode('/', array_slice($parts, 2)) . '.class.php';
  if (file_exists($filename)) {
    require_once($filename);
  }
});

// Enable custom routes that can be processed by NSV\Core\Page clases.
$router = NSV\Core\Router::getInstance();
$router->setup();

add_action('nsv_router_init', function($router) {
  $router->addRoute('^api/dwz/spieler/?$', 'NSV\\Core\\Dwz\\PlayerSearch');
  $router->addRoute('^api/dwz/vereine/?$', 'NSV\\Core\\Dwz\\ClubSearch');
  $router->addRoute('^sandbox/?$', 'NSV\\Core\\TestPage');
  $router->addRoute('^sandbox2/?$', 'NSV\\Core\\TestPage');
  // CAUTION: When changing routes, go to Admin > Settings > Permalinks > Save Changes to rebuild the route cache!
});

add_filter('query_vars', function($vars) {
  return array_merge($vars, array(
    'auth'
  ));
});

// Disable permalink guessing. By default, WordPress is very eager to try to find a post that matches the
// given URL. However, when we have various custom URLs, then that often gets in the way and WordPress
// intercepts an URL that we meant to handle by a custom route.
add_filter('do_redirect_guess_404_permalink', '__return_false');
