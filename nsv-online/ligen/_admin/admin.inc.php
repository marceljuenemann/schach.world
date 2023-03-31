<?
/* Admin Bereich
 * 
 * Mehrere Funktionen für den Administrator des Systems.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage admin
 */
    chdir ( "../_inc/" );
    $globals ['basedir'] = "..";
	require_once ( "main.inc.php" );
	require_once ( "../config.inc.php" );
    require_once ( "connect.inc.php" );
	
	ini_set ( "display_errors", true );
	error_reporting ( E_ALL );
?>
