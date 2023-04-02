<?php
/**
 * Plugin Name: NSV Turniere
 * Plugin URI: https://nsv-online.de/
 * Description: Ergebnisdienst für Turniere
 * Version: 1.0
 * Author: Marcel Jünemann
 * Author URI: http://marcel.world
 */

add_action('nsv_router_init', function($router) {
  $router->addRoute('^turniere/([a-z0-9-]+)/([a-z0-9]+)/?$', 'NSV\\Turniere\\Ergebnisse\\Home', array('turnier', 'jahr'));
  $router->addRoute('^turniere/([a-z0-9-]+)/([a-z0-9]+)/admin/?$', 'NSV\\Turniere\\Ergebnisse\\Admin', array('turnier', 'jahr'));
  $router->addRoute('^turniere/([a-z0-9-]+)/([a-z0-9]+)/upload/?$', 'NSV\\Turniere\\Ergebnisse\\Upload', array('turnier', 'jahr'));
  $router->addRoute('^turniere/([a-z0-9-]+)/([a-z0-9]+)/vereine/([a-z0-9_-]+)/?$', 'NSV\\Turniere\\Ergebnisse\\Club', array('turnier', 'jahr', 'name'));
  $router->addRoute('^turniere/([a-z0-9-]+)/([a-z0-9]+)/([A-Za-z0-9_-]+)/([a-z]+)/?([0-9]+)?/?$', 'NSV\\Turniere\\Ergebnisse\\Table', array('turnier', 'jahr', 'gruppe', 'typ', 'runde'));
  $router->addRoute('^turniere/([a-z0-9-]+)/([a-z0-9]+)/([A-Za-z0-9_-]+)/spieler/([a-z0-9_-]+)/?$', 'NSV\\Turniere\\Ergebnisse\\Player', array('turnier', 'jahr', 'gruppe', 'name'));
  // CAUTION: When changing routes, go to Admin > Settings > Permalinks > Save Changes to rebuild the route cache!
});

add_filter('query_vars', function($vars) {
  return array_merge($vars, array(
    'turnier',
    'jahr',
    'gruppe',
    'typ',
    'runde',
    'attr',
    'land',
    'name'
  ));
});
