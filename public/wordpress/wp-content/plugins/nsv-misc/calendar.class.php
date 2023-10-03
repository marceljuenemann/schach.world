<?php

namespace NSV\Misc;

class Calendar {

  /**
   * Returns the HTML for the calendar widget.
   */
  public function widget() {
    $content = "<div class='NsvTermine'>";

    $curDate = "";
    foreach ($this->fetchEvents() as $termin) {
      // Datum umwandeln
      $date = $termin['date_W'] . ', ' . $termin['date_d'] . '.' . $termin['date_m'];
  
      // Tagesueberschrift
      if ( $date != $curDate )
      {
        if ( $curDate )
          $content .= "</ul>";
          $content .= "<b>$date</b><ul>";
        $curDate = $date;
      }
  
      $content .= "<li><a href='$termin[url]'>$termin[name]</a></li>";
    }
  
    $content .= "</ul></div>";
    $content .= "<a style='font-size: smaller;' href='/termine/'>Alle Termine</a>";
    return $content;
  }

  private function fetchEvents() {
    \NSV\Core\Database::query("SET lc_time_names = 'de_DE'");
    $events = \NSV\Core\Database::query(" 
        SELECT name, url, is_nsv,
          DATE_FORMAT(date, '%a') date_a, 
          DATE_FORMAT(date, '%W') date_W, 
          DATE_FORMAT(date, '%d') date_d, 
          DATE_FORMAT(date, '%m') date_m,
          DATE_FORMAT(date, '%M') date_M,
          DATE_FORMAT(date, '%Y') date_Y
        FROM termine2
        WHERE date >= CURDATE() AND is_approved=1
        ORDER BY date ASC
        LIMIT 5")
      ->fetchAll(\PDO::FETCH_NAMED);
    return $events;
  }
}
