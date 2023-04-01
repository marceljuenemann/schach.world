<div class="card shadow nsv-card d-print-none">
  <div class="card-body">
    <h5 class="card-title">Weitere Artikel</h5>
    <?php
      while (have_posts()) {
        the_post();
        echo '<i style="font-family: verdana; font-size: smaller">';
        the_time( 'd. F' );
        echo '</i> - ';
        the_title(sprintf('<a href="#post-%s">', get_the_ID()), '</a><br>');
      }
    ?>
    <div style='padding-top: 5px; font-size: smaller'>Ältere Artikel finden sich im <a href="#newsarchiv">Newsarchiv</a></div>
  </div>
</div>
