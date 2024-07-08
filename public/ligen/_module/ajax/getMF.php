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
	$fields = SED_Query ( "SELECT mf_telefon as telefon, mf_telefon2 as telefon2, mf_email as mf_email FROM mannschaften WHERE mf_name LIKE ? ORDER BY id DESC", [utf8_decode($_GET["name"]).'%'] )->fetchAssociative();
	
	// Wenn er existierte, dann diesen zurückgeben
	if ( $fields )
		echo utf8_encode ( implode ( ";", $fields ) );
	
	// Ansonsten keine Daten ausgeben
	else	
		echo ";;";
	
	exit;
?>
