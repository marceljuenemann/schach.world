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

add_shortcode( 'schach-in-niedersachsen-liste', 'nsvMiscSiNList' );

function nsvMiscSiNList($atts, $content, $shortcode_tag) {
  $result = "";
  $path = ABSPATH . '../schachzeitung/';
  for ($year = 2016; is_dir($path . $year); $year++) {
    $files = scandir($path . $year);
    foreach ($files as $file) {
      if ($file[0] != '.') {
        $pw = substr($file, 0, strlen('NSVyyyyMM'));
        $month = substr($file, strlen('NSVyyyy'), 2);
        $month = utf8_encode(strftime("%B", mktime(0, 0, 0, $month, 10)));
        $result = "<a href='/schachzeitung/$year/$file'>"
                . "SinN $month $year</a>"
                . " (Passwort:&nbsp;$pw)<br>"
                . $result;
      }
    }
  }
  return $result;
}  
