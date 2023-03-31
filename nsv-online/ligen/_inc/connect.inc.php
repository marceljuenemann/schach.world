<?
/* MySQL Connector
 * 
 * Dieses Skript verbindet das System zur Datenbank.
 * @global global[db]
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage main
 */
    // Sollte eigentlich schon erledigt sein...
    require_once ( "../config.inc.php" );

    // Direct dependency on NSV site :/
    require_once ( "../../libs/mysql-shim.php" );
    
	// Debugging
	if ( $globals ['debug'] )
	{
		ini_set ( "display_errors", true );
		error_reporting ( $globals ['debugmode'] );
	}

	// Zur Datenbank verbinden
	$globals ['db'] = mysql_connect ( $globals ['dbhost'], $globals ['dbuser'], $globals ['dbpw'] );
	if ( !mysql_select_db ( $globals ['dbname'], $globals ['db'] ) )
		SED_Error ( "Fehler: Datenbank konnte nicht geöffnet werden!", true );
  mysql_set_charset('latin1');
	$globals ['dbpw'] = "******";
?>
