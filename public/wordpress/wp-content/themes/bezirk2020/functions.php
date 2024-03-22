<?php
define('NSV2020_DOMAIN', 'https://schachbezirk-hannover.de');

function nsv2020_theme() {
  return 'bezirk1';
}

function bezirk1_register_widgets() {
	register_sidebar( array(
		'name'          => 'Right sidebar',
		'id'            => 'left',  // Historic reasons :)
		'before_widget' => '<div class="card shadow nsv-card nsv-sidebar-card"><div class="card-body">',
		'after_widget'  => '</div></div>',
		'before_title'  => '<h5 class="card-title">',
		'after_title'   => '</h5>',
	) );
}
add_action( 'widgets_init', 'bezirk1_register_widgets' );
