<?php
/* Spieltag als Text-Datei
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage spieltag
 */

  /////////////////////////////////
  // Vorbereitungen
  /////////////////////////////////

  header ( "Content-type: text/plain" );
  
  if ( !Spieltag ( $globals ['tid'], $_GET ['staffel'], $_GET ['r'], $_data, true, false ) )
    SED_Error ( "Der Spieltag scheint nicht zu existieren!", true );

  
  /////////////////////////////////
  // Überschrift & Spiele
  /////////////////////////////////  
  
  // Überschrift
  echo "$_data[staffelname]\r\n$_data[sl_name]\r\n$_GET[r]. Runde $_data[datum]\r\n";

  // Paarungen ausgeben
  for ( $i = 0; $i < count ( $_data ['paarungen'] ); ++$i )
  {
	// Paarungstitel
	echo $_data ['paarungen'][$i]['m1'] . "\t-\t" . $_data ['paarungen'][$i]['m2'] . "\r\n";	

    // Spielerpaarungen
    for ( $j = 0; $j < count ( $_data ['paarungen'][$i]['paarungen'] ); ++$j )
    {
	  echo $_data ['paarungen'][$i]['paarungen'][$j]['s1pass'] . "\t";
	  echo $_data ['paarungen'][$i]['paarungen'][$j]['s1fullname'] . "\t";
	  echo $_data ['paarungen'][$i]['paarungen'][$j]['erg1'] . "\t:\t";
	  echo $_data ['paarungen'][$i]['paarungen'][$j]['erg2'] . "\t";
	  echo $_data ['paarungen'][$i]['paarungen'][$j]['s2fullname'] . "\t";
	  echo $_data ['paarungen'][$i]['paarungen'][$j]['s2pass'] . "\r\n";
    }

    // Ergebnis
    echo $_data ['paarungen'][$i]['erg1'] . "\t:\t" . $_data ['paarungen'][$i]['erg2'] . "\r\n";
  }

  
  /////////////////////////////////
  // Tabelle
  /////////////////////////////////

  function __TabAusgeben ( $kreuztabelle )
  {
    // Vorbereitungen
    $_tab = Tabelle ( $_GET ['staffel'], $_GET ['r'], $kreuztabelle );
    if ( !count ( $_tab ) ) return;

    // Überschriften
    echo "Tabelle nach $_GET[r] Runden\r\n";

    // Daten
    for ( $i = 1; $i < count ( $_tab ); ++$i )
    {
      for ( $j = 0; $j < count ( $_tab[$i] ) -1; ++$j )
      {
          if ( is_array ( $_tab [$i][$j] ) )
            echo $_tab[$i][$j]["text"]."\t";
          else  
            echo $_tab[$i][$j]."\t";
	  }
      echo "\r\n";
    }
  }

  // Tabellen wirklich ausgeben
  $showTabelle = SED_Value('SELECT showTabelle FROM viewStaffeln WHERE id=?', [$_GET['staffel']]);
  if ( $showTabelle ) {
    __TabAusgeben ( false );
  }

  /////////////////////////////////
  // Infos
  /////////////////////////////////

  // Bemerkungen
  $content = "Bemerkungen\r\n";
  if ( isset ( $_data ['paarungen'] ) )
    foreach ( $_data ['paarungen'] as $paarung )
      if ( $paarung ['bemerkung'] )
        $content .= "$paarung[bemerkung]\r\n";
  if ( isset ( $_data ['bemerkung'] ) && $_data ['bemerkung'] )
    $content .= $_data ['bemerkung'] ."\r\n";

  // Nachmeldungen
  if ( isset ( $_data ['nachmeldungen'] ) )
    if ( count ( $_data ['nachmeldungen'] ) )
    {
      $content .= "Nachmeldungen\r\n";
      foreach ( $_data ['nachmeldungen'] as $nachmeldung )
        $content .= "$nachmeldung[nachname], $nachmeldung[vorname] ($nachmeldung[mannschaft])\r\n";
    }

  echo $content;
?>
