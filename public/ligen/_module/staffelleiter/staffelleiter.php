<?php
/* Staffelleiter-Bereich-Modul
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */

	require_once ( "turnier.inc.php" );
	require_once ( "login.inc.php" );

	// AJAX innerhalb des Staffelleiter-Bereiches
	if ( isset ( $_GET ['type'] ) )
	{
		require_once ( "$globals[basedir]/_module/ajax/ajax.php");
		exit;
	}
		
	require_once ( "gui.inc.php" );

	echo $admin ['toptxt'];
	require_once ( $globals ['basedir'] . "/_module/staffelleiter/" . $admin ['pageid'] . ".php" );
?>
