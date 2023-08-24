<div id="newsarchiv" class="card shadow nsv-card d-print-none">
  <div class="card-body">
    <a name="newsarchiv" class="anchor"></a>
    <h5 class="card-title">News-Archiv</h5>
    <?php
       // Archive selection.
      $months = array('Jan', 'Feb', 'M&auml;r', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez');
      $current_year = (int) date("Y");
      $first_year = 2005;
      for ($year = $current_year; $year >= $first_year; $year--) {
        echo "<b style='min-width: 40px; display: inline-block'>$year:</b> ";
        $last_month = $year < $current_year ? 12 : (int) date('m');
        for ($month = 1; $month <= $last_month; $month++) {
          echo "<a href='/$year/$month/'>" . $months[$month - 1] . '</a> ';
        }
        echo "<br>";
      }
    ?>
  </div>
</div>
