<?
/* AJAX: Liste mit Paarungen
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage ajax
 */

	require_once ( "turnier.inc.php" );
	require_once ( "login.inc.php" ); // Für usertype etc.
	require_once ( "paarungsauflistung.inc.php" );
	
	// Paarungsauflistung ausgeben
    ob_start ();
	SED_Paarungsauflistung ();
	echo SED_utf8_encode ( ob_get_clean () );
	
	exit;
?>
