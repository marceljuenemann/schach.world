<?php

namespace Nsv\WebApp\Core\WordPress;

use Symfony\Component\HttpFoundation\RedirectResponse;

class Auth {

  static function isLoggedIn() {
    return is_user_logged_in();
  }

  static function isAdmin() {
    return current_user_can('manage_options');
  }

  static function isAuthor() {
    return current_user_can('publish_posts');
  }

  /**
   * Creates a redirect response that redirects the user to the login page.
   * 
   * @param redirectTo the URL to redirect to after login
   */
  static function loginRedirect(string $redirectTo) {
    return new RedirectResponse('/wp-login.php?redirect_to=' . urlencode($redirectTo));
  }
}
