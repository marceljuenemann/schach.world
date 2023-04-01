<?
/* Berechnung der aktuellen Runde
 *
 * Stellt sicher, dass $_GET[r] auf eine gültige Runde gesetzt ist.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage libs
 */

    // Aktuelle Runde in $_GET [r] speichern
    if ( !isset ( $_GET ['r'] ) || !is_numeric ( $_GET ['r'] ) ){
        $_GET ['r'] = SED_AktuelleRunde ( 0 );
   }


    // Berechnet die aktuelle Runde einer bestimmten Staffel
    function SED_AktuelleRunde ( $staffel ){
        // Keine Staffel gesetzt? => selber nachgucken
        global $admin; global $globals;
        if ( !$staffel )
            $staffel = isset ( $_GET ['staffel'] ) ? $_GET ['staffel']
            : (isset ( $admin ) && isset ( $admin ['staffel'] ) ? $admin ['staffel'] : 0);

        // Staffelabhängig
        if ( $staffel ){
            // Den (staffelabhängigen) Termin finden, der am nächsten an heute ist
            // Das Problem an dieser Version war, das einzelne Spiele, die verlegt wurden, zu schlechten Ergebnissen führen
            //$rsrc = mysql_query ( "SELECT runde FROM viewTermine WHERE staffel=$staffel ORDER BY ABS(UNIX_TIMESTAMP(termin)-UNIX_TIMESTAMP(CURDATE())) LIMIT 1", $globals ['db'] );
            // Diese Query funktioniert, allerdings wurde bei Doppelspieltagen immer der letzte angezeigt.
            //$rsrc = mysql_query ( "SELECT runde FROM viewStaffeltermine WHERE id=$staffel AND runde<='$maxRunde' ORDER BY ABS(UNIX_TIMESTAMP(datum)-UNIX_TIMESTAMP(CURDATE())), runde DESC LIMIT 1", $globals ['db'] );
            $maxRunde = SED_GetLetzteRunde ( $staffel );
            $rsrc = mysql_query ( "
                SELECT runde, IF(datum<=CURDATE(),-runde,runde) doppelspieltag
                FROM viewStaffeltermine
                WHERE id='$staffel' AND runde<='$maxRunde'
                ORDER BY
                  ABS(UNIX_TIMESTAMP(datum)-UNIX_TIMESTAMP(CURDATE())),
                  doppelspieltag
                LIMIT 1", $globals ['db'] );
            if ( $rsrc && mysql_num_rows ( $rsrc ) && $r = mysql_fetch_array ( $rsrc, MYSQL_NUM ) )
                return reset ( $r );
        }

        // Staffelunabhängig
        else {
            require_once ( "turnier.inc.php" );

            // Den (staffelunabhängigen) Termin finden, der am nächsten an heute ist
            $rsrc = mysql_query ( "SELECT runde FROM termine WHERE turnier=$globals[tid] and staffel is null ORDER BY ABS(UNIX_TIMESTAMP(datum)-UNIX_TIMESTAMP(CURDATE())) LIMIT 1", $globals ['db'] );
            if ( $rsrc && mysql_num_rows ( $rsrc ) )
                return reset ( mysql_fetch_array ( $rsrc, MYSQL_NUM ) );
        }

        return 1; // hat alles nichts geholfen...
    }
?>
