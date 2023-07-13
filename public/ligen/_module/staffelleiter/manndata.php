<?
/* SL-Bereich: Mannschaftsdaten
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
	require_once ( "auth.inc.php" );

	// Berechtigt, die Mannschaftsdaten zu bearbeiten?
	if ( isset ( $_GET ['mid'] ) && isset ( $globals['teams'][$_GET['mid']] ) )
	{
		$_GET ['auth'] = SED_MD5_MID ( $_GET ['mid'] );
		require_once ( "$globals[basedir]/_module/mannschaftsdaten/mannschaftsdaten.php" );
	}
?>
