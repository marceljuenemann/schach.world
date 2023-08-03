<div class='d-flex justify-content-center'><div class='d-inline-block overflow-auto'>
  <div class="form-check form-switch d-sm-none mb-2">
    <input type="checkbox" class="form-check-input" id="dwzSwitch" onclick="$(this).parent().parent().toggleClass('nsv-details-show')">
    <label class="form-check-label" for="dwzSwitch">DWZ anzeigen</label>
  </div>
  <div class='nsv-table sed-ergebnisse'>
  <?php

  // Paarungen ausgeben
  for ( $i = 0; $i < count ( $daten ['paarungen'] ); ++$i )
  {
    // Anker, um direkt zur Paarung zu kommen
    $anker1 = "p".$daten ['paarungen'][$i]['mid1']."x".$daten ['paarungen'][$i]['mid2'];
    $anker2 = "p".$daten ['paarungen'][$i]['mid2']."x".$daten ['paarungen'][$i]['mid1'];

    // Mannschaftsnamen und Ergebnisse ausgeben
    echo "<div class='nsv-table-header d-flex'>";
    echo "<a name='$anker1'></a><a name='$anker2'></a>";
    echo "<div class='sed-mannschaft text-start'><a href='?mannschaft=".$daten ['paarungen'][$i]['mid1']."'>".$daten ['paarungen'][$i]['m1']."</a></div>";
    echo "<div class='text-center'>&nbsp;".$daten ['paarungen'][$i]['erg1']."&nbsp;:&nbsp;".$daten ['paarungen'][$i]['erg2']."&nbsp;</div>";
    echo "<div class='sed-mannschaft text-end'><a href='?mannschaft=".$daten ['paarungen'][$i]['mid2']."'>".$daten ['paarungen'][$i]['m2']."</a></div>";
    echo "</div>";

    // ggf. Infos über Verlegung & Ausrichter
    if ( count ( $daten ['paarungen'][$i]['paarungen'] ) == 0 )
    {
        if ( $daten ['paarungen'][$i]['datum'] != $daten ['datum'] )
            echo "<div class='nsv-table-cell text-center'>Findet statt am ".$daten ['paarungen'][$i]['datum']."</div>";
        if ( $daten ['paarungen'][$i]['ausrichterId'] != $daten ['paarungen'][$i]['mid1'] )
            echo "<div class='nsv-table-cell text-center'>Ausgerichtet von: <a href='?mannschaft=".$daten ['paarungen'][$i]['ausrichterId']."'>".$daten ['paarungen'][$i]['ausrichter']."</a></div>";
    }

    // kampflos gewonnen?
    elseif ( $daten ['paarungen'][$i]['kampflos'] ){
        echo "<div class='nsv-table-cell text-center'>(kampflos)</div>";
    }

    // Spielerpaarungen
    else {
      for ( $j = 0; $j < count ( $daten ['paarungen'][$i]['paarungen'] ); ++$j )
      {
        $spielerpaarung = $daten ['paarungen'][$i]['paarungen'][$j];
        echo "<div class='d-flex'>";

        // Spieler 1
        if ( $staffel ['showPassNr'] ) {
          echo "<div class='nsv-table-cell sed-passnr d-none d-sm-block'>".$spielerpaarung["s1pass"]."</div>";
        }
        echo "<div class='nsv-table-cell sed-spieler sed-spieler1'>";
        if ( $spielerpaarung["sid1"] ) {
          echo "<div class='sed-name'><a href='?spieler=". $spielerpaarung["sid1"]."'>". $spielerpaarung["s1fullname"]."</a></div>";
          echo "<div class='sed-dwz nsv-details align-self-end'>".sprintf ( $spielerpaarung["dwz1"] ? "(%s)" : "%s", $spielerpaarung["dwz1"] )."</div>";
        }  
        echo "</div>";

        // Ergebnisse
        echo "<div class='nsv-table-cell sed-ergebnis'>";
        echo $daten ['paarungen'][$i]['paarungen'][$j]['erg1'] . "&nbsp;:&nbsp;" . $daten ['paarungen'][$i]['paarungen'][$j]['erg2'];
        echo "</div>";

        // Spieler 2
        echo "<div class='nsv-table-cell sed-spieler sed-spieler2'>";
        if ( $spielerpaarung["sid2"] ) {
          echo "<div class='sed-dwz nsv-details align-self-end'>".sprintf ( $spielerpaarung["dwz2"] ? "(%s)" : "%s", $spielerpaarung["dwz2"] )."</div>";
          echo "<div class='sed-name'><a href='?spieler=". $spielerpaarung["sid2"]."'>". $spielerpaarung["s2fullname"]."</a></div>";
        }  
        echo "</div>";
        if ( $staffel ['showPassNr'] ) {
          echo "<div class='nsv-table-cell sed-passnr d-none d-sm-block'>".$spielerpaarung["s2pass"]."</div>";
        }

        echo "</div>";
      }
    }

    // Bemerkung anzeigen
    if ( $daten ['paarungen'][$i]['bemerkung'] ){
        echo "<i>".$daten['paarungen'][$i]['bemerkung']."<br /></i>";
    }
    echo "<br>";
  }

?></div></div></div>
