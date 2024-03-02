<?php
define('NSV2020_DOMAIN', 'https://schachbezirk-hannover.de');
define('NSV2020_PATH', ABSPATH . 'nsv2020');

function nsv2020_url() {
  return '/nsv2020';
}

function nsv2020_head() {
  wp_head();
  echo '<link rel="stylesheet" href="' . get_template_directory_uri() . '/style.css">';
}

function nsv2020_is_utf8() {
  return TRUE;
}

function nsv2020_navbar() {
  $locations = get_nav_menu_locations();
  $items = wp_get_nav_menu_items($locations['header-menu']);
  $items = wp_menu_to_nsv_menu($items);
  echo "<!--\n";
  echo json_encode($items, JSON_PRETTY_PRINT);
  echo "\n-->\n";
  return $items;
}

function nsv2020_theme() {
  return 'bezirk1';
}

function nsv2020_title() {
   add_theme_support( 'title-tag' );
}
add_action( 'after_setup_theme', 'nsv2020_title' );


function wp_menu_to_nsv_menu($menu) {
  $parents = array();
  $children = array();
  foreach ($menu as $item) {
    // Add full domain to make sure the link works from the NSV server as well.
    if (substr($item->url, 0, 4) != 'http') {
      $item->url = NSV2020_DOMAIN . $item->url;
    }
    
    if (!$item->menu_item_parent) {
      $parents[] = $item;
    } else {
      $parent_id = $item->menu_item_parent;
      if (!isset($children[$parent_id])) {
        $children[$parent_id] = array();
      }
      $children[$parent_id][] = $item;
    }
  }

  $result = array();
  foreach ($parents as $item) {
    $result[$item->title] = array($item->url);
    
    if (isset($children[$item->ID])) {
      $result[$item->title][] = "";
      foreach ($children[$item->ID] as $child) {
        $result[$item->title][htmlentities($child->title)] = $child->url;
      }
    }
  }
  return $result;
}


function bezirk1_register_menus() {
  register_nav_menus(
    array(
      'header-menu' => __( 'Header Menu' ),
      'left-menu' => __( 'Left Menu' )
     )
  );
}
add_action( 'init', 'bezirk1_register_menus' );

function bezirk1_register_widgets() {
	register_sidebar( array(
		'name'          => 'Left sidebar',
		'id'            => 'left',
		'before_widget' => '<div class="card shadow nsv-card nsv-sidebar-card"><div class="card-body">',
		'after_widget'  => '</div></div>',
		'before_title'  => '<h5 class="card-title">',
		'after_title'   => '</h5>',
	) );
}
add_action( 'widgets_init', 'bezirk1_register_widgets' );
