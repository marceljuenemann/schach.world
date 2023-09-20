<?php
define('NSV2020_PATH', ABSPATH . '../core/nsv2020');

// Include some parts of the legacy NSV system for functions like NsvDb().
// TODO: Stop doing that once the widgets no longer require this. 
$nsv['basedir'] = ABSPATH . '..';
$nsv['utf8'] = true;
require_once(ABSPATH . '../libs/mysql-shim.php');
require_once(ABSPATH . '../core/config.inc.php');
require_once(ABSPATH . '../core/functions.inc.php');

add_action('wp_enqueue_scripts', function() {
  wp_enqueue_script("jquery");
});

add_action('after_setup_theme', function() {
  add_theme_support('title-tag');
});

add_action( 'widgets_init', function() {
  register_sidebar([
    'name'          => 'Startseiten Sidebar',
    'id'            => 'frontpage_sidebar',
    'before_widget' => "<div class='card shadow nsv-card nsv-sidebar-card' data-widget-title='%2\$s'><div class='card-body'>",
    'after_widget'  => "</div></div>",
    'before_title'  => "<h5 class='card-title'>",
    'after_title'   => '</h5>',
  ]);
});
