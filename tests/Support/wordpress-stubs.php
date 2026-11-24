<?php

/**
 * Minimal stand-ins for the WordPress functions called by the WebApp Twig templates
 * (see Nsv\WebApp\Core\WordPress\TwigExtension), so pages can be rendered without WordPress.
 *
 * Note that Nsv\WebApp\Core\WordPress\Auth checks for ABSPATH rather than these functions,
 * so defining them doesn't make the application believe it runs inside WordPress.
 */

if (!function_exists('get_header')) {
  function get_header() {
    echo '<!-- wp:header -->';
  }
}

if (!function_exists('get_footer')) {
  function get_footer() {
    echo '<!-- wp:footer -->';
  }
}

if (!function_exists('get_template_part')) {
  function get_template_part($slug) {
    echo "<!-- wp:template-part $slug -->";
  }
}

if (!function_exists('add_filter')) {
  function add_filter($hookName, $callback) {
    $GLOBALS['wp_stub_filters'][$hookName][] = $callback;
    return true;
  }
}
