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

// Forward to the Symfony based WebApp for specific route prefixes.
add_filter('template_include', function($template) {
  global $wp;
  $prefixes = [
    'v3',
    'anmeldung',
    'dwz',
    'ligen',
    'termine',
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

// Shortcodes.
add_action('init', function() {

  // ChessBase Widget.
  add_shortcode('nsv-chessbase', function() {
    $cbByWeekday = array(
      '1' => array('url' => 'chessbase_18_mega_package', 'img' => 'CB18'),
      '2' => array('url' => 'chessbase_18_mega_package', 'img' => 'CB18'),
      '3' => array('url' => 'chessbase_18_mega_package', 'img' => 'CB18'),
      '4' => array('url' => 'chessbase_18_mega_package', 'img' => 'CB18'),
      '5' => array('url' => 'chessbase_18_mega_package', 'img' => 'CB18'),
      '6' => array('url' => 'chessbase_18_mega_package', 'img' => 'CB18'),
      '7' => array('url' => 'chessbase_18_mega_package', 'img' => 'CB18')
    );
    $cb = $cbByWeekday[date('N')];
    return "
      <div class='nsv-widget' id='widget-chessbase'>
        <a href='https://shop.chessbase.com/de/products/$cb[url]?ReF=RF310-OONJK95SZC'>
          <img src='https://nsv-online.de/images/chessbase/$cb[img].png' alt='ChessBase'>
        </a>
      </div>
    ";
  });

  // Calendar Widget.
  add_shortcode('nsv-termine', function() {
    $calendar = new \NSV\Misc\Calendar();
    return "<div class='nsv-widget' id='widget-termine'>" . $calendar->widget() . "</div>";
  });

  // Headlines Widget.
  add_shortcode('nsv-schlagzeilen', function() {
    $content = file_get_contents(ABSPATH . '../../data/headlines/headlines.html');
    return "<div class='nsv-widget' id='widget-schlagzeilen'>$content</div>";
  });

  // DWZ Widget.
  add_shortcode('nsv-dwz-suche', function() {
    ob_start();
    get_template_part('sidebar/dwz-widget');
    return ob_get_clean();
  });
});

// Enable automatic updates despite .git folder.
add_filter('automatic_updates_is_vcs_checkout', function($checkout, $context) {
  return false;
}, 10, 2);
