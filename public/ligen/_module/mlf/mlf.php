<?
/* Elobase Export
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage export
 */
// TODO: Zwei Mannschaften in einer Staffel
// TODO: Bez 4 Export

    require_once ( "turnier.inc.php" );
    
    if ( !isset ( $_GET ['staffel'] ) ){
        // Staffeln auflisten
        require_once ( "gui.inc.php" );
        echo "<span class='sed_hl1'>MLF-Export</span><br /><br />";
        foreach ( $globals ["staffeln"] as $id=>$name )
            echo "<a href='?m=mlf&staffel=$id'>$name</a><br />";
        SED_Error ( "", true );
    }
    
    require_once ( "mannschaft.class.php" );
    header ( "Content-type: text/plain" );

    // MLF-interne IDs
    $reg_mannschaften = array (); // mid => mlf-id
    $reg_spieler = array ( ""=>"" ); // sid => mlf-id
    $reg_spieler_iterator = 1; // Zum Zählen der ID

    // Mannschaften sammeln TODO: Alle Mannschaften, die in der Staffel gespielt haben
    $rsrc_mannschaften = mysql_query ( "select m.id from mannschaften as m where staffel=$_GET[staffel] order by m.name" );
    $count_mannschaften = mysql_num_rows ( $rsrc_mannschaften );

    // Mannschaften durchgehen
    for ( $i = 1; $entry_mannschaft = mysql_fetch_array ( $rsrc_mannschaften, MYSQL_ASSOC ); ++$i )
    {
        // Mannschaftsdaten laden
        $team = new SED_Mannschaft ( $entry_mannschaft["id"] );
        $einzelergebnisse = $team->getErgebnisse ();
        
        // Mannschaft registrieren
        $reg_mannschaften [$entry_mannschaft['id']] = $i;
        echo "R|$i|0|$i|".$team->get("mannschaftsname")."\r\n";

        // Spieler durchgehen
        $j = 1; // Mannschaftsinterne Spieler-Nummer
        foreach ( $team->getAufstellung () as $spieler ){
            // Wurde der Spieler gar nicht eingesetzt?
            if ( $spieler["istErsatz"] && ((int) @$einzelergebnisse[$spieler["id"]]["einsaetze"]) == 0 )
            { continue; }
                
            // ZPS zusammensetzen
            $tmp = explode ( "-", $spieler ["zps"] );
            $zps = @sprintf ( "%s%04s", $tmp [0], $tmp [1] );
            $zps = str_replace ( "0000", "****", $zps ); // unbekannt
            
            // Registrieren und ausgeben
            $reg_spieler [$spieler['id']] = $reg_spieler_iterator++;
            echo "R|$i|$j|".$reg_spieler [$spieler['id']]."|$spieler[nachname],$spieler[vorname]|$zps\r\n";
            ++$j;
        }
    }

  // ===========================


  //D|MANNSCHAFTEN|22
  echo "D|MANNSCHAFTEN|$count_mannschaften\r\n";



  // Paarungen sammeln
  $rsrc_paar = mysql_query ( "select p.id, p.runde, p.mannschaft1, p.mannschaft2 from paarungen as p where p.staffel=$_GET[staffel] order by p.runde" );

  // Paarungen durchgehen
  for ( $i = 1; $entry_paar = mysql_fetch_array ( $rsrc_paar, MYSQL_ASSOC ); ++$i )
  {
    // Ausgeben
    echo "B|$i|0|".$reg_mannschaften [$entry_paar ['mannschaft1']]."|".$reg_mannschaften [$entry_paar ['mannschaft2']]."|$entry_paar[runde]\r\n";

    // Spieler-Paarungen suchen
    $rsrc_spaar = mysql_query ( $x = "SELECT * FROM spielerpaarungen WHERE paarung=$entry_paar[id] ORDER BY brett" );

    // Spielerpaarungen durchgehen
    for ( $j = 1; $entry_spaar = mysql_fetch_array ( $rsrc_spaar, MYSQL_ASSOC ); ++$j )
    {
      // Ausgeben
      if ( $entry_spaar ['ergebnis1'] == SED_REMIS ) $entry_spaar ['ergebnis1'] = "r";
      if ( $entry_spaar ['ergebnis2'] == SED_REMIS ) $entry_spaar ['ergebnis2'] = "r";
      echo "B|$i|$entry_spaar[brett]|".$reg_spieler [$entry_spaar ['spieler1']]."|".$reg_spieler [$entry_spaar ['spieler2']]."|$entry_spaar[ergebnis1]|$entry_spaar[ergebnis2]\r\n";
    }
  }



  echo "D|BRETTER|".SED_GetBrettzahl ($_GET ['staffel'])."\r\n";
  echo "D|RUNDEN|".SED_GetRundenzahl ($_GET ['staffel'])."\r\n";
  $staffelname = reset ( mysql_fetch_array ( mysql_query ( "select name from staffeln where id=$_GET[staffel]" ), MYSQL_ASSOC ) );
  echo "D|LIGANAME|$staffelname\r\n";



?>

