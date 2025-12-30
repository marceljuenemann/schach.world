<?php
// Theme can be configured via $args param of get_header().
global $nsv2020theme;
$nsv2020theme = isset($args['nsv2020theme']) ? $args['nsv2020theme'] : 'nsv';

if (!function_exists('nsv2020_theme')) {
  function nsv2020_theme() {
    global $nsv2020theme;
    return $nsv2020theme;
  }
}

function nsv2020_url() {
  return '/core/nsv2020';
}

function nsv2020_head() {
  wp_head();
  echo '<link rel="stylesheet" href="' . get_template_directory_uri() . '/style.css">';
  echo '<meta name="google-site-verification" content="fCTPZ_YWLoqdfrq1ds0nbmhYcqV27kuOW6aHjUuHpsA" />';
}

function nsv2020_is_utf8() {
  return TRUE;
}

function nsv2020_navbar() {
  if (nsv2020_theme() === 'bezirk1') {
    require_once(ABSPATH . '../core/nsv2020/bezirk1/navbar.php');
    return nsv2020_bezirk1_navbar();
  } else {
    include (ABSPATH . '../mainmenu.menu.php');
    return $nsvMainmenu;
  }
}

include (NSV2020_PATH . '/header.php');
