<?php
/**
 * Plugin Name: NSV Miscellaneous
 * Plugin URI: https://nsv-online.de/
 * Description: Verschiedene kleinere Funktionen für die NSV Seite
 * Version: 1.0
 * Author: Marcel Jünemann
 * Author URI: http://marcel.world
 */

add_action('nsv_router_init', function($router) {
  $router->addRoute('^kontakt/?$', 'NSV\\Misc\\Kontakt');
  // CAUTION: When changing routes, go to Admin > Settings > Permalinks > Save Changes to rebuild the route cache!
});
