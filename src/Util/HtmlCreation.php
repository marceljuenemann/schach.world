<?php

namespace Nsv\Util;

/**
 * Methods to create HTML by doing the Spaghetti-Code here
 */
class HtmlCreation {
  public function internalLink($uri, $text) {
    $link = '<a href ="';
    $link .= $uri;
    $link .= '">';
    $link .= $text;
    $link .= '</a>';

    return $link;
  }

}