<?
/* SL-Bereich: Zugangsdaten für Mannschaftsführer
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */

  require_once ( "login.inc.php" );
  require_once ( "tinyurl.inc.php" );

	// Anfang
	echo "Mit den folgenden Links k&ouml;nnen die jeweiligen Paarungen in das System eingegeben werden. Sollte die Eingabe durch Mannschaftsf&uuml;hrer bei Ihrem Turnier aktiviert sein, so erhalten die Mannschaftsf&uuml;hrer diese Links automatisch drei Tage vor einem Spieltag. Sollte jemand Probleme haben, Mails vom System zu empfangen, so k&ouml;nnen Sie ihm seinen Link direkt aush&auml;ndigen.<br />";

	// Iteration über alle Paarungen
	$rsrc = SED_Query ( "SELECT id, runde, mannschaft1, mannschaft2 FROM paarungen WHERE staffel=? ORDER BY runde", [$admin['staffel']])->fetchAllAssociative();
  $runde = 0;
	foreach ( $rsrc as $paarung )
	{
    // Neue Runde?
    if ( $paarung ['runde'] != $runde ) {
      $runde = $paarung ['runde'];
      echo "<br /><b>Spieltag $runde:</b><br />";
    }
        
		// Sicherheitsstring holen, evtl. neuen
    $link = SED_TINYURL_Paarung ( $paarung ['id'] );
		
		// Ausgeben
		echo $globals['teams'][$paarung ['mannschaft1']]." - ";
    echo $globals['teams'][$paarung ['mannschaft2']].": ";
    echo "<a href='$link'>$link</a><br />";
	}	  
	echo "<br />";
