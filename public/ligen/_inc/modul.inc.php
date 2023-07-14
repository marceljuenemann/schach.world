<?
/* URL parsen
 * 
 * Dieses Skript entscheidet an Hand der übergebenen Parameter ($_GET)
 * welches Modul angezeigt werden soll.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage main
 */
	if ( isset ( $_GET ['m'] ) )
		$globals ['mod'] = $_GET ['m'];
	else if ( isset ( $_GET ["anmeldung"] ) )
		$globals ['mod'] = 'anmeldung';
	else if ( isset ( $_GET ["admin"] ) )
		$globals ['mod'] = 'staffelleiter';
	else if ( isset ( $_GET ['staffel'] ) && isset ( $_GET ['r'] ) )
	{
		if ( $_GET ['r'] == "spielplan" )
			$globals ['mod'] = "spielplan";
		elseif ( $_GET ['r'] == "statistik" )
			$globals ['mod'] = "statistik";
		else
			$globals ['mod'] = "spieltag";
	}
	elseif ( isset ( $_GET ['staffel'] ) )
		$globals ['mod'] = "spielplan";
	elseif ( isset ( $_GET ["mannschaft"] ) && is_numeric ( $_GET ['mannschaft'] ) )
		$globals ['mod'] = "mannschaft";
	elseif ( isset ( $_GET ["spieler"] ) && is_numeric ( $_GET ['spieler'] ) )
		$globals ['mod'] = "spieler";
    elseif ( isset ( $_GET ['esw'] ) ){ // Einzel-Staffel-Weiterleitung
        require_once ( "turnier.inc.php" );
        if ( count ( $globals ['staffeln'] ) == 1 ){
            $globals ['mod'] = "spieltag";
            $_GET ['staffel'] = reset (array_keys($globals ['staffeln']));
            $_GET ['r'] = "";
        } else
            $globals ['mod'] = "startseite";
    }
	else
		$globals ['mod'] = "startseite";

	$globals ['mod'] = str_replace ( "..", "_", $globals ['mod'] );
	
	// TINYURL
	if ( $globals ['mod'] == "tinyurl" )
	{
		require_once ( "tinyurl.inc.php" );
		SED_TINYURL_parse ();
	}
