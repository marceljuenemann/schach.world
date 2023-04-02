<?php
namespace NSV\Core;

/**
 * Authentication and authorization utilities.
 */
class Auth {
  private function __construct() {}

  public static function requireLoggedIn() {
    if (!is_user_logged_in()) {
      throw new \Exception("Bitte <a href='/wp-login.php'>loggen Sie sich ein</a> um diese Aktion durchzuführen");
    }
  }

  public static function requireCapability($capability) {
    Auth::requireLoggedIn();
    if (!current_user_can($capability)) {
      throw new \Exception("Sie sind nicht berechtigt diese Aktion durchzuführen");
    }
  }

  public static function requireAdmin() {
    Auth::requireCapability('manage_options');
  }

  public static function requireAuthKey(...$args) {
    $auth = get_query_var('auth');
    if (!$auth || $auth !== Auth::generateAuthKey(...$args)) {
      throw new \Exception("Sie sind nicht berechtigt diese Aktion durchzuführen");
    }
  }
  
  public static function generateAuthKey(...$args) {
    return hash('sha256', wp_salt() . '-nsv-auth-' . implode('-', $args));
  }
}
