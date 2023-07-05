<?php

namespace Nsv\WebApp\Core\WordPress;

class Auth {

  static function isAdmin() {
    return current_user_can('manage_options');
  }

}
