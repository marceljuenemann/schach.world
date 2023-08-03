<?php
define('NSV2020_PATH', ABSPATH . '../core/nsv2020');

// Include some parts of the legacy NSV system for functions like NsvDb().
// TODO: Stop doing that once the widgets no longer require this. 
$nsv['basedir'] = ABSPATH . '..';
$nsv['utf8'] = true;
require_once(ABSPATH . '../libs/mysql-shim.php');
require_once(ABSPATH . '../core/config.inc.php');
require_once(ABSPATH . '../core/functions.inc.php');

wp_enqueue_script("jquery");

function nsv2020_title() {
   add_theme_support( 'title-tag' );
}
add_action( 'after_setup_theme', 'nsv2020_title' );
