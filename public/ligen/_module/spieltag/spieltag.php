<?
/* Spieltag-Anzeige
 *
 * Zeigt Paarungen und Tabelle eines bestimmten Spieltages einer
 * bestimmten Staffel an.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage spieltag
 */

    require_once ( "turnier.inc.php" );
    require_once ( "spieltag.inc.php" );
    require_once ( "tabelle.inc.php" );
    require_once ( "runde.inc.php" );

    // Welcher Ausgabetyp?
    if ( isset ( $_GET ['ausgabe'] ) )
    {
        $_GET ['ausgabe'] = strtolower ( $_GET ['ausgabe'] ); // wegen PDF im Spieltag-Auswahl-Formular
        if ( !in_array ( $_GET ['ausgabe'], array ( "pdf", "txt", "phparray" ) ) )
            SED_Error ( "Nicht unterstütztes Ausgabeformat!", true );
        $fn = "$globals[basedir]/_module/spieltag/".str_replace ( "..", "-", strtolower ( $_GET ['ausgabe'] ) ).".php";
        if ( file_exists ( $fn ) )
        {
            require_once ( $fn );
            exit;
        }
    }
