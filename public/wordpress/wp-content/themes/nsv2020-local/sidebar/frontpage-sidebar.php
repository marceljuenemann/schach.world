<div class='card shadow nsv-card nsv-sidebar-card'>
  <div class='card-body'>
    <h5 class='card-title'>Hello World!</h5>
    The following sidebar widgets are currently not supported in this local version:
    <ul>
      <li>Downloads</li>
      <li>Chessbase</li>
      <li>Termine</li>
      <li>Schlagzeilen</li>
    </ul>
  </div>
</div>

<div id="widget-grandprix" class='card shadow nsv-card nsv-sidebar-card'>
  <div class='card-body'>
    <h5 class='card-title'>NSV-Grandprix</h5>
    <?php
      include(ABSPATH . '../infobox-grandprix.php');
    ?>
  </div>
</div>

<div id="widget-lem" class='card shadow nsv-card nsv-sidebar-card'>
  <div class='card-body'>
    <h5 class='card-title'>LEM</h5>
    <?php
      include(ABSPATH . '../infobox-lem.php');
    ?>
  </div>
</div>

<div id="widget-dwz" class='card shadow nsv-card nsv-sidebar-card'>
  <div class='card-body'>
    <h5 class='card-title'>DWZ Suche</h5>
    <?php
      get_template_part( 'sidebar/dwz-widget' );
    ?>
  </div>
</div>
