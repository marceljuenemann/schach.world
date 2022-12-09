<?
  /*
    --- Methoden ---
    DwzLib ( Gegner, Punkte )
    CalcFirstDwz ()
    CalcDwz ( AlteDwz, Alter )
    CalcFirstElo ()
    CalcElo ( AlteElo )

    --- Beispiel ---
    $dwz = new DwzLib ( "1953;1506;1235", 1.5 );
    $dwz->CalcDwz ( 1600, 32 );
    foreach ( $dwz->results as $k => v )
      echo "<strong>$k:</strong> $v<br />";
  */

  class DwzLib
  {
    // Vorbereitungen
    function DwzLib ( $gegner, $punkte )
    {
      // Initialisierung
      $this->InitPdTab ();

      // n, W und rc berechnen
      $this->opps = explode ( ";", $gegner );
      $this->result ['n'] = count ( $this->opps );
      $this->result ['w'] = $punkte;
      $this->result ['rc'] = round ( array_sum ( $this->opps ) / $this->result ['n'] );

      // Leistung rh berechnen
      if ( $this->result ['w'] == 0.0 )
        $this->result ['rh'] = $this->result ['rc'] - 677;
      elseif ( $this->result ['w'] == $this->result ['n'] )
        $this->result ['rh'] = $this->result ['rc'] + 677;
      else
      {
        // Näherungswert
        $Rp = $this->result ['rc'] + $this->D ( $this->result ['w'] / $this->result ['n'] );

        // Iterationsverfahren: Verbesserte Leistung berechnen
        $iterator = 0;
        do {
          $last = round ( $Rp );
          $dWe = 0.0;
          for ( $i = 0; $i < $this->result ['n']; ++$i )
          {
            if ( $this->opps [$i] > 0 )
              $dWe += $this->pD ( $Rp, $this->opps [$i] );
          }
          $Rp += $this->D ( ( $this->result ['w'] - $dWe ) / $this->result ['n'] + 0.5 );
        } while  ( $last != round ( $Rp ) && ++$iterator < 50 );
        $this->result ['rh'] = round ( $Rp );
      }
    }

    // Erste DWZ berechnen
    function CalcFirstDwz ()
    {
      // Halbzahl = Leistung
      $this->result ['rn'] = $this->result ['rh'];
      if ( $this->result ['rn'] < 800 )
        $this->result ['rn'] = round ( 700 + ( $this->result ['rh'] / 8 ) );
      if ( $this->result ['n'] < 5 || $this->result ['w'] == 0 || $this->result ['w'] == $this->result ['n'] )
        $Rn = "Berechnung unm&ouml;glich";
    }

    // DWZ Veränderung berechnen
    function CalcDwz ( $ro, $age )
    {
      // Vorbereitung
      $this->result ['ro'] = $ro;
      $this->result ['we'] = 0;

      // Gewinnerwartung We
      for ( $i = 0; $i < $this->result ['n']; ++$i )
        if ( $this->opps [$i] > 0 )
          $this->result ['we'] += $this->pD ( $ro, $this->opps [$i] );

      // Entwicklungskeoffizienten berechnen
      $dfB = 1.0;
      $dSBr = $dE = 0.0;;

      // Alterskoeffizient J
      $j = 15;
      if ( $age <= 25 ) $j = 10; // Unter 26 ?
      if ( $age <= 20 ) $j = 5; // Unter 21 ?

      // Beschleunigungsfaktor
      if ( $j == 5 && $this->result ['w'] >= $this->result ['we'] )
        $dfB = min ( array ( 1.0, max ( array ( 0.5, $ro / 2000.0 ) ) ) );

      // Bremszuschlag
      if ( $ro < 1300 && $this->result ['w'] <= $this->result ['we'] )
        $dSBr = pow ( M_E, ( 1300 - $ro ) / 150.0 ) - 1;

      // E und seine Grenzen
      $dE = max ( array ( 5.0, ( pow ( $ro / 1000.0, 4.0 ) + $j ) * $dfB + $dSBr  ) );
      if ( $dSBr == 0 )
        $dE = round ( min ( array ( 150.0, min ( array ( 5 * $this->result ['n'], $dE ) ) ) ) );
      else
        $dE = round ( min ( array ( 30.0, $dE ) ));

      // Neue DWZ berechnen
      $this->result ['rn'] = round ( $ro + 800.0 * ( $this->result ['w'] - $this->result ['we'] ) / ( $dE + $this->result ['n'] ) );
    }

    // Erste ELO berechnen
    function CalcFirstElo ()
    {
      // Wertungsdifferenz berechnen
      if ( $this->result ['w'] >= $this->result ['n'] / 2.0 )
        $this->result ['rn'] = round ( $this->result ['rc'] + 25 * ( $this->result ['w'] - $this->result ['n'] / 2.0 ) );
      else
        $this->result ['rn'] = round ( $this->result ['rc'] + $this->D ( $this->result ['w'] /$this->result ['n'] ) );
    }

    // ELO Veränderung berechnen
    function CalcElo ( $ro )
    {
      // Rc2 wegen 350-Differenz berechnen
      $rc2 = 0;
      for ( $i = 0; $i < 50; ++$i )
        if ( $this->opps [$i] > 0 && $this->opps [$i] < 4000 )
        {
          if ( $this->opps [$i] < $ro - 350 ) $rc2 += $ro - 350;
          else if ( $ro + 350 < $this->opps [$i] ) $rc2 += $ro + 350;
          else $rc2 += $this->opps [$i];
        }
      $rc2 /= $this->result ['n'];

      // Gewinnerwartung We
      $this->result ['we'] = $this->result ['n'] * $this->pD ( $ro, $rc2 );

      // Neue DWZ berechnen
      $this->result ['rn'] = round ( $ro + 25 * ( $this->result ['w'] - $this->result ['we'] ) );
    }

    ////////////////////////////////////

    var $result = array ();
    var $opps = array ();
    /*static*/ var $PdTab;

    // Gibt eine Gewinnerwartung für eine Wertungsdifferenz zurück
    function pD ( $ro, $gegner )
    {
      // Differenz berechnen und Wert suchen
      $D = abs ( $ro - $gegner );
      foreach ( $this->PdTab as $row )
      {
        if ( $row [0] <= $D && $D <= $row [1] )
          return ( $ro > $gegner ) ? $row [2] : $row [3];
      }
      return 0;
    }

    // Gibt eine Wertungsdifferenz für eine Gewinnerwartung zurück
    function D ( $dP )
    {
      // Auf 2 Stellen runden
      $dP = round ( $dP, 2 );

      // Sonderbehandlung für besondere Werte
      if ( $dP == 0.5 ) return 0;
      if ( $dP == 0.0 ) return -677;
      if ( $dP == 1.0 ) return 677;

      foreach ( $this->PdTab as $row )
      {
        // Wenn Prozentualgewinn der Tabelle entspricht, Mittelwert zurückgeben
        if ( $dP == $row [2] )
          return round ( ( $row [0] + $row [1] ) / 2 );
        if ( $dP == $row [3] )
          return - round ( ( $row [0] + $row [1] ) / 2 );
      }
      return 0;
    }

    // Erwartungstabelle initialisieren
    function InitPdTab ()
    {
      $this->PdTab = array (
        array(0,3,0.5,0.5),
        array(4,10,0.51,0.49),
        array(11,17,0.52,0.48),
        array(18,25,0.53,0.47),
        array(26,32,0.54,0.46),
        array(33,39,0.55,0.45),
        array(40,46,0.56,0.44),
        array(47,53,0.57,0.43),
        array(54,61,0.58,0.42),
        array(62,68,0.59,0.41),
        array(69,76,0.6,0.4),
        array(77,83,0.61,0.39),
        array(84,91,0.62,0.38),
        array(92,98,0.63,0.37),
        array(99,106,0.64,0.36),
        array(107,113,0.65,0.35),
        array(114,121,0.66,0.34),
        array(122,129,0.67,0.33),
        array(130,137,0.68,0.32),
        array(138,145,0.69,0.31),
        array(146,153,0.7,0.3),
        array(154,162,0.71,0.29),
        array(163,170,0.72,0.28),
        array(171,179,0.73,0.27),
        array(180,188,0.74,0.26),
        array(189,197,0.75,0.25),
        array(198,206,0.76,0.24),
        array(207,215,0.77,0.23),
        array(216,225,0.78,0.22),
        array(226,235,0.79,0.21),
        array(236,245,0.8,0.2),
        array(246,256,0.81,0.19),
        array(257,267,0.82,0.18),
        array(268,278,0.83,0.17),
        array(279,290,0.84,0.16),
        array(291,302,0.85,0.15),
        array(303,315,0.86,0.14),
        array(316,328,0.87,0.13),
        array(329,344,0.88,0.12),
        array(345,357,0.89,0.11),
        array(358,374,0.9,0.1),
        array(375,391,0.91,0.09),
        array(392,411,0.92,0.08),
        array(412,432,0.93,0.07),
        array(433,456,0.94,0.06),
        array(457,484,0.95,0.05),
        array(485,517,0.96,0.04),
        array(518,559,0.97,0.03),
        array(560,619,0.98,0.02),
        array(620,734,0.99,0.01),
        array(735,9999,1,0)
      );
    }
}

?>
