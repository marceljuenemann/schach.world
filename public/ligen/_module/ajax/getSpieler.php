<?
/* AJAX: Spielerdaten nach Namen
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage ajax
 */
    /* Liefert eine Spielerauflistung. 
     * Erwartet Javascript-Callback OnSpielerClick ( dwz, spieler )
     * wobei dwz = implode ( ";", <DWZ-DB-Entry> )
     * und spieler = $spieler->serialize ()
     */

    require_once ( "dwzdb.inc.php" );
    
    // Anfrage vorbereiten
    $db = new SED_DWZ_Request ();
    $db->setName ( utf8_decode ( $_GET['name'] ) );
    $db->setVerband ( $_GET['verband'] );

    // Anfrage durchführen
    foreach ( $db->getPlayerObjectList ( 10, SED_DWZ_Request::SORT_NAME ) as $player ){
        echo "<a href='javascript:dummy()' onclick='OnSpielerClick("
        .$player->getJSON() // Liefert UTF-8 codierten String
        .")'>"
        .utf8_encode($player->getName ())."</a><br />";
    }
	exit;
?>
