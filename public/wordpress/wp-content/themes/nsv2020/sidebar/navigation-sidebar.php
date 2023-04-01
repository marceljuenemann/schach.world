<?php
  require(ABSPATH . '../verband/index.menu.php' );
  foreach ( $nsvMenu as $label => $menuitem ) {
    $label = utf8_encode($label);
    echo "<div class='card shadow nsv-card nsv-sidebar-card'>";
    echo "<div class='card-body'>";
    echo "<h5 class='card-title'>$label</h5>";

    foreach ( $menuitem as $sublabel => $url ) {
      if ( !is_numeric ( $sublabel ) )
      {
        $sublabel = utf8_encode($sublabel);
        if ( substr ( $url, 0, 4 ) == "http" )
            echo "<a href=\"$url\">$sublabel</a><br>";
        else
            echo "<a href=\"/$url\">$sublabel</a><br>";
      }
    }
    
    echo "</div></div>";
  }
