<?
/* DWZ Bibliothek
 * 
 * Diese Biblithek stellt Funktionen zur DWZ-Berechnung zur Verfügung.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage libs
 */

  /*
    --- Zu übergebende Parameter ---
    Alte DWZ,
    Gegner (Array mit Gegner-DWZs; WICHTIG! Keine Null-DWZ Spieler!),
    Punkte (Array mit Ergebnisen),
    Geburt (Beliebiges Datumsformat)

    --- Rückgabe ---
    Array mit auszugebenden Werten oder FALSE
  */

  // Berechnung über isewase.de
  function SED_DWZ ( $ro, $gegner, $punkte, $geburt )
  {
    // Argumente zusammen suchen
    $_ro = $ro;
    $_geburt = substr ( "19" . $geburt, -4 );  // Funktioniert bei: 90, 1990, 2001, 18.01.1990, nicht: 18.1.90
    $_pkt = array_sum ( $punkte );
    $_opps = implode ( ";", $gegner );

    // URL zusammensetzen
    $url = "http://d2.isewase.de/dwzxml2.php?ed=$_ro&gj=$_geburt&pu=$_pkt&gd=$_opps";

    // Abfragen und XML öffnen
    $str = file_get_contents ( $url );
    $xml = new SimpleXMLElement ( $str );

    // Rückgabe
    $result ['Alte DWZ'] = $ro;
    $result ['Gewertete Partien'] = count ( $gegner );
    $result ['Punkte'] = $_pkt;
    $result ['Prozentual'] = count ( $gegner ) ? round ( $_pkt * 100 / count ( $gegner ) ) . "%" : "";
    $result ['Gegnerschnitt'] = $xml->Auswertung->Niveau;
    $result ['Erwartete Punkte'] = $xml->Auswertung->Gewinnerwartung;
    if (count($gegner) >= 5) {
      $result ['Leistung'] = $xml->Auswertung->Leistung;
    }
    $result ['Neue DWZ'] = $xml->Auswertung->DWZneu;
    $result ['Differenz'] = (int) $xml->Auswertung->DWZneu - (int) $ro;
    return $result;
  }

