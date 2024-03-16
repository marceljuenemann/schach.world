<?php

namespace Nsv\WebApp\Core\WordPress;

use Symfony\Component\HttpFoundation\RedirectResponse;

class Auth {

  /**
   * Returns whether we are running in a WordPress context.
   * 
   * TODO: Instead of checking for WordPress manually, have a "Bridge" interface
   * that can be implemented for different environments. That way we can easily
   * integreate with a CMS other than WordPress as well in the future.
   */
  static function isWordPress() {
    return defined('ABSPATH');
  }

  static function isLoggedIn() {
    return Auth::isWordPress() && is_user_logged_in();
  }

  static function isAdmin() {
    return Auth::isWordPress() && current_user_can('manage_options');
  }

  static function isAuthor() {
    return Auth::isWordPress() && current_user_can('publish_posts');
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
