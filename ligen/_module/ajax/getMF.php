<?
/* AJAX: Mannschaftsführerdaten nach Namen
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage ajax
 */

	// Nun die aktuellsten Daten abfragen
	$rsrc = mysql_query ( "SELECT mf_telefon as telefon, mf_telefon2 as telefon2, mf_email as mf_email FROM mannschaften WHERE mf_name LIKE '".utf8_decode($_GET["name"])."%' ORDER BY id DESC", $globals ['db'] );
	
	// Wenn er existierte, dann diesen zurückgeben
	if ( $rsrc && mysql_num_rows ( $rsrc ) )
		echo utf8_encode ( implode ( ";", mysql_fetch_array ( $rsrc, MYSQL_NUM ) ) );
	
	// Ansonsten keine Daten ausgeben
	else	
		echo ";;";
	
	exit;
?>
