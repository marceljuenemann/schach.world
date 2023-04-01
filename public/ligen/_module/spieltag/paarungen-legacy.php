<?php
  // Vorbereitungen
  echo "<table class='$_class' style='margin-left:auto;margin-right:auto' cellspacing='0' cellpadding='2'>";

  // Paarungen ausgeben
  for ( $i = 0; $i < count ( $daten ['paarungen'] ); ++$i )
  {
      // Muss die PassNr angezeigt werden?
      $_passNrXtra1 = ( $staffel ['showPassNr'] ? 3 : 2 );
      $_passNrXtra2 = ( $staffel ['showPassNr'] ? "&nbsp;" : "" );
      $_colspan = ( $staffel ['showPassNr'] ? 9 : 7 );

      // Anker, um direkt zur Paarung zu kommen
      $anker1 = "p".$daten ['paarungen'][$i]['mid1']."x".$daten ['paarungen'][$i]['mid2'];
      $anker2 = "p".$daten ['paarungen'][$i]['mid2']."x".$daten ['paarungen'][$i]['mid1'];

      // Mannschaftsnamen und Ergebnisse ausgeben
      echo "<tr>";
      echo "<th colspan='$_passNrXtra1' style='$_al $_bgclr;'><a name='$anker1'></a><a name='$anker2'></a><a href='?mannschaft=".$daten ['paarungen'][$i]['mid1']."'>".$daten ['paarungen'][$i]['m1']."</a>"."</th>";
      echo "<th style='$_ar $_br $_bgclr;'>".$daten ['paarungen'][$i]['erg1']."</th>";
      echo "<th style='$_ac $_bl $_br $_bgclr;'>:</th>";
      echo "<th style='$_al $_bl $_bgclr;'>".$daten ['paarungen'][$i]['erg2']."</th>";
      echo "<th colspan='$_passNrXtra1' style='$_ar $_bgclr;'><a href='?mannschaft=".$daten ['paarungen'][$i]['mid2']."'>".$daten ['paarungen'][$i]['m2']."</a>"."</th>";
      echo "</tr>";

      // ggf. Infos über Verlegung & Ausrichter
      if ( count ( $daten ['paarungen'][$i]['paarungen'] ) == 0 )
      {
          if ( $daten ['paarungen'][$i]['datum'] != $daten ['datum'] )
              echo "<tr><td colspan='$_colspan'>Findet statt am ".$daten ['paarungen'][$i]['datum']."</td></tr>";
          if ( $daten ['paarungen'][$i]['ausrichterId'] != $daten ['paarungen'][$i]['mid1'] )
              echo "<tr><td colspan='$_colspan'>Ausgerichtet von: <a href='?mannschaft=".$daten ['paarungen'][$i]['ausrichterId']."'>".$daten ['paarungen'][$i]['ausrichter']."</a></td></tr>";
      }

      // kampflos gewonnen?
      elseif ( $daten ['paarungen'][$i]['kampflos'] ){
          echo "<tr><td colspan='$_colspan'>(kampflos)</td></tr>";
      }

      // Spielerpaarungen
      else
          for ( $j = 0; $j < count ( $daten ['paarungen'][$i]['paarungen'] ); ++$j )
          {
              echo "<tr>";

              // ggf. PassNr
              if ( $staffel ['showPassNr'] )
                  echo "<td style='$_ac'>".$daten ['paarungen'][$i]['paarungen'][$j]['s1pass']."</td>";

              // Spielername
              if ( $daten ['paarungen'][$i]['paarungen'][$j]['sid1'] )
                  echo "<td style='$_al $_br'>$_passNrXtra2<a href='?spieler=".$daten ['paarungen'][$i]['paarungen'][$j]['sid1']."'>".$daten ['paarungen'][$i]['paarungen'][$j]['s1fullname']."</a>"."</td>";
              else
                  echo "<td style='$_br'>&nbsp;</td>";

              // DWZ und Ergebnisse
              echo "<td style='$_ar $_bl'>".sprintf ( $daten ['paarungen'][$i]['paarungen'][$j]['dwz1'] ? "(%s)" : "%s", $daten ['paarungen'][$i]['paarungen'][$j]['dwz1'] )."</td>";
              echo "<td style='$_ar $_br'>".$daten ['paarungen'][$i]['paarungen'][$j]['erg1']."</td>";
              echo "<td style='$_ac $_bl $_br'>:</td>";
              echo "<td style='$_al $_bl'>".$daten ['paarungen'][$i]['paarungen'][$j]['erg2']."</td>";
              echo "<td style='$_al $_br'>".sprintf ( $daten ['paarungen'][$i]['paarungen'][$j]['dwz2'] ? "(%s)" : "%s", $daten ['paarungen'][$i]['paarungen'][$j]['dwz2'] )."</td>";

              // Spielername
              if ( $daten ['paarungen'][$i]['paarungen'][$j]['sid2'] )
                  echo "<td style='$_ar $_bl'>"."<a href='?spieler=".$daten ['paarungen'][$i]['paarungen'][$j]['sid2']."'>".$daten ['paarungen'][$i]['paarungen'][$j]['s2fullname']."</a>$_passNrXtra2</td>";
              else
                  echo "<td style='$_bl'></td>";

              // ggf. Pass-Nr
              if ( $staffel ['showPassNr'] )
                  echo "<td style='$_ac'>".$daten ['paarungen'][$i]['paarungen'][$j]['s2pass']."</td>";
              echo "</tr>";
          }

      // Bemerkung anzeigen
      if ( $daten ['paarungen'][$i]['bemerkung'] ){
          echo "<tr><td style='$_al $_br $_bl $_bb font-style:italic;' colspan='$_colspan'>".$daten['paarungen'][$i]['bemerkung']."<br />&nbsp;</td></tr>";
      }
      else {
          // Einfachen Abstand zwischen den Paarungen
          echo "<tr><td colspan='9' style='$_bl $_br $_bb $_bt'>&nbsp; &nbsp; &nbsp;</td></tr>";
      }
  }

  // Ende
  echo "</table>";
