<?php
// The following functions need to be defined by the caller.

// The name of the theme. Supported values are 'nsv', 'ergebnisdienst' and 'bezirk1'.
function_exists('nsv2020_theme') or die();

// URL to the nsv2020 directory without trailing slash.
function_exists('nsv2020_url') or die();

// Callback for outputting additional head tags. Must output title tag as well as include jQuery.
function_exists('nsv2020_head') or die();

// Whether to use UTF-8 or ISO character encoding.
function_exists('nsv2020_is_utf8') or die();

// The items of the menu navbar, in the old nsv format.
function_exists('nsv2020_navbar') or die();

$url = nsv2020_url();

?><!doctype html>
<html lang="de">
  <head>
    <meta charset="<?= nsv2020_is_utf8() ? 'utf-8' : 'ISO-8859-1'?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="<?= nsv2020_url() ?>/vendor/bootstrap-4.3.1.min.css">
    <link rel="stylesheet" href="/wp-includes/css/dashicons.min.css">

    <link rel="stylesheet" href="<?= nsv2020_url() ?>/nsv.css">
    <?php if (nsv2020_theme() != 'nsv'): ?>
      <link rel="stylesheet" href="<?= nsv2020_url() ?>/nsv-blue.css">
    <?php endif; ?>

    <?= nsv2020_head() ?>

    <script src="<?= nsv2020_url() ?>/vendor/popper-1.14.7.min.js"></script>
    <script src="<?= nsv2020_url() ?>/vendor/bootstrap-4.3.1.min.js"></script>

    <?php if (nsv2020_theme() == 'nsv'): ?>
      <link rel="icon" href="https://nsv-online.de/wp-content/uploads/2019/05/favicon-1.gif" sizes="32x32">
    <?php elseif (nsv2020_theme() == 'bezirk1'): ?>
      <link rel="icon" href="<?= nsv2020_url() ?>/bezirk1/favicon.ico" sizes="16x16">
      <link rel="icon" href="<?= nsv2020_url() ?>/bezirk1/favicon.ico" sizes="32x32">
    <?php endif; ?>
  </head>
  <body>

    <div id="nsv-header" class="d-none d-lg-block">
      <?php if (nsv2020_theme() == 'nsv'): ?>
        <div class="container d-flex">
          <div><a href="/"><img src="<?= nsv2020_url() ?>/images/nsv.png"></a></div>
          <div id="nsv-header-brand" class="align-self-center flex-fill">Nieders&auml;chsischer<br>Schachverband</div>
          <div>
            <div style="color: black; margin-bottom: 5px; margin-top: 20px">Gef&ouml;rdert durch:</div>
            <div>
              <a href="https://www.lotto-sport-stiftung.de/"><img src="<?= nsv2020_url() ?>/images/lotto.svg" style="height: 60px; margin-right: 20px"></a>
              <a href="http://chessbase.de"><img src="<?= nsv2020_url() ?>/images/chessbase.svg" style="height: 60px"></a>
              <!--<a href="https://www.chessemy.com/"><img src="<?= nsv2020_url() ?>/images/chessemy.png" style="height: 60px"></a>-->
            </div>
          </div>
        </div>
      <?php elseif (nsv2020_theme() == 'bezirk1'): ?>
        <div class="container">
          <div style="margin-top: -15px; margin-left: -10px">
            <a href="https://schachbezirk-hannover.de/"><img src="<?= nsv2020_url() ?>/bezirk1/logo.png"></a>
          </div>
        </div>
      <?php elseif (nsv2020_theme() == 'ergebnisdienst'): ?>
        <div class="container">
          <div class="row">
            <div class="col-3"><img src="<?= nsv2020_url() ?>/images/ergebnisdienst.png" style="height: 50px"></div>
            <div class="align-self-center col-9" style="font-size: 2em"><?= nsv2020_custom_sitename() ?></div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <nav id="nsv-navbar" class="navbar navbar-expand-lg navbar-dark sticky-top shadow-lg">
      <div class="container">
        <?php if (nsv2020_theme() == 'nsv'): ?>
          <a class="navbar-brand d-lg-none d-flex" href="/">
            <div class="align-self-center">Nieders&auml;chsischer Schachverband</div>
          </a>
        <?php elseif (nsv2020_theme() == 'bezirk1'): ?>
          <a class="navbar-brand d-lg-none d-flex" href="https://schachbezirk-hannover.de/">
            <div class="align-self-center">Schachbezirk Hannover</div>
          </a>
        <?php elseif (nsv2020_theme() == 'ergebnisdienst'): ?>
          <span class="navbar-brand d-lg-none d-flex">
            <div class="align-self-center"><?= nsv2020_custom_sitename() ?></div>
          </span>
        <?php endif; ?>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav">
            <?php
              function _nsv2020_fix_url($url) {
                if (substr($url, 0, 1) != '?' && substr($url, 0, 1) != '/' && substr($url, 0, 4) != 'http') {
                  $url = "/" . $url;
                }
                return $url;
              }
            
              $id = 0;
              foreach (nsv2020_navbar() as $title => $menuitem) {
                $url = _nsv2020_fix_url($menuitem[0]);
                $is_dropdown = count ($menuitem) > 2;

                echo "<li class='nav-item" . ($is_dropdown ? " dropdown" : "" ) . "'>";
                echo "<a class='nav-link";
                if ($is_dropdown) echo " dropdown-toggle' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false";
                echo "' href='$url' id='navbarDropdownMenuLink$id'>$title";
                echo "</a>";

                if ($is_dropdown) {
                  echo "<div class='dropdown-menu' aria-labelledby='navbarDropdownMenuLink$id'>";
                  foreach ($menuitem as $title => $url) {
                    if (is_numeric($title)) continue;
                    $url = _nsv2020_fix_url($url);
                    echo "<a class='dropdown-item' href='$url'>$title</a>";
                  }
                  echo "</div>";
                }

                echo "</li>";
                $id++;
              }
            ?>

            <?php if (nsv2020_theme() == 'nsv'): ?>
            <li class="nav-item nsv-navbar-search">
              <a class="nav-link" href="/suche">
                <span class="dashicons dashicons-search"></span>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>

    <div id="nsv-main" class="container">
      <div class="row">
