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
  function isWordPress() {
    return defined('ABSPATH');
  }

  function isLoggedIn() {
    return $this->isWordPress() && is_user_logged_in();
  }

  function isAdmin() {
    return $this->isWordPress() && current_user_can('manage_options');
  }

  function isAuthor() {
    return $this->isWordPress() && current_user_can('publish_posts');
  }

  function userName() {
    return $this->isLoggedIn() ? wp_get_current_user()->user_login : null;
  }

  /**
   * Return the URI that redirects the user to the login page.
   *
   * @param redirectTo the URL to redirect to after login
   */
  function loginRedirect(string $redirectTo) {
    return '/wp-login.php?redirect_to=' . urlencode($redirectTo);
  }

  function logoutRedirect(string $redirectTo) {
    return $this->loginRedirect($redirectTo) . '&action=logout';
  }
}
