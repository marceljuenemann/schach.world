<div id="widget-chessbase" class='card shadow nsv-card nsv-sidebar-card'>
  <div class='card-body'>
    <h5 class='card-title'>Aktuelles</h5>
    <?php
      require_once(ABSPATH . '../core/apps/downloads/main.inc.php');
      foreach (NsvDownload_GetCurrentDownloads() as $dl) {
        $title = /*utf8_encode( */ $dl['title']; //);
        echo "<a href='$dl[url]'>$title</a>";
        if (isset($dl['pw'])) {
          echo " (PW:&nbsp;$dl[pw])";
        }
        echo "<br>";
      }
    ?>
  </div>
</div>


<div id="widget-chessbase" class='card shadow nsv-card nsv-sidebar-card'>
  <div class='card-body'>
    <h5 class='card-title'>ChessBase</h5>
    <?php
      $cbByWeekday = array(
        '1' => array('url' => 'mega_database_2022', 'img' => 'Mega2022'),
        '2' => array('url' => 'fritz_18', 'img' => 'Fritz18'),
        '3' => array('url' => 'corr_2022', 'img' => 'Corr2022'),
        '4' => array('url' => 'chessbase_16_mega_package', 'img' => 'CB16'),
        '5' => array('url' => 'mega_database_2022', 'img' => 'Mega2022'),
        '6' => array('url' => 'fritz_18', 'img' => 'Fritz18'),
        '7' => array('url' => 'chessbase_16_mega_package', 'img' => 'CB16')
      );
      $cb = $cbByWeekday[date('N')];
      echo "<a href='https://shop.chessbase.com/de/products/$cb[url]?ReF=RF310-OONJK95SZC'><img src='/images/chessbase/$cb[img].png' alt='ChessBase'></a>";
    ?>
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


<div id="widget-termine" class='card shadow nsv-card nsv-sidebar-card'>
  <div class='card-body'>
    <h5 class='card-title'>Termine</h5>
    <?php
      require_once(ABSPATH . '../termine/main.inc.php');
      NsvTermineSidebox();
    ?>
  </div>
</div>


<div id="widget-schlagzeilen" class='card shadow nsv-card nsv-sidebar-card'>
  <div class='card-body'>
    <h5 class='card-title'>
      Schlagzeilen
      <a href="/core/modules/schlagzeilen.xml"><span class="dashicons dashicons-rss"></span></a>
    </h5>
    <?php
      echo utf8_encode(file_get_contents(ABSPATH . '../core/modules/schlagzeilen.html'));
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
