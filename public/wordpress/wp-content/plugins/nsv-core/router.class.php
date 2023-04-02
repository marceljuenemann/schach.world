<?php
namespace NSV\Core;

/**
 * Logic for registering routes of custom dynamic pages.
 */
class Router {
  const QUERY_PARAM = 'nsvpage';

  private static $instance = null;

  private $routes = array();

  private function __construct() {}

  /**
   * Registers all the necessary WordPress hooks for the router to work.
   */
  public function setup() {
    // Allow NSV plugins to register routes.
    add_action('init', function() {
      do_action('nsv_router_init', $this);
    });

    // Make sure we can access the QUERY_PARAM.
    add_filter('query_vars', function($vars) {
      array_push($vars, self::QUERY_PARAM);
      return $vars;
    });

    // Forward to nsv-page.php file in the theme when needed.
    add_filter('template_include', function($template) {
      if (get_query_var(self::QUERY_PARAM)) {
        $template = locate_template('nsv-page.php');
      }
      return $template;
    });
  }

  /**
   * Registers a new route.
   *
   * @param $route the route to handle, see add_rewrite_rule for regex format.
   * @param $classname the name of a NSV\Core\Page class that should handle the route.
   */
  public function addRoute($route, $classname, $params = null) {
    $this->routes[$classname] = $route;
    $target = 'index.php?' . self::QUERY_PARAM . '=' . urlencode($classname);
    if ($params) {
      $i = 1;
      foreach ($params as $param) {
        $target .= '&' . $param . '=$matches[' . ($i++) . ']'; 
      }
    }
    add_rewrite_rule($route, $target, 'top');
  }

  public function getPage() {
    $classname = get_query_var(self::QUERY_PARAM);
    if (!isset($this->routes[$classname])) {
      throw new \Exception('Not a valid route');
    }
    $page = new $classname();
    if (!$page instanceof \NSV\Core\Page) {
      throw new \Exception('Not a page handler');
    }
    return $page;
  }

  public static function getInstance() {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }
}
