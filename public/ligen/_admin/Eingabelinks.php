<?php
/* Automatische Versendung von Eingabelink-eMails
 *
 * Versendet die Eingabelinks und sollte täglich per Cronjob aufgerufen
 * werden.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage admin
 */

    $globals['adminScript'] === 'Eingabelinks' or die('Invalid invocation');

    // Kann auch mal etwas länger dauern
    ini_set ( "max_execution_time", 30000 );

    require_once ( "tinyurl.inc.php" );
    require_once ( "mail.eingabelink.inc.php" );

    // Paarungen sammeln
    $paar = SED_Query ( '
        SELECT p.mannschaft1 as mid, p.mannschaft2 mid2, ausr.id ausrichter, ausr.mf_email, p.id as paarung, p.staffel, p.runde, vs.turnier, vs.staffelleiter slname, vs.email slmail
        FROM paarungen as p
        INNER JOIN viewTermine vt ON vt.paarung=p.id
        INNER JOIN mannschaften as ausr ON ausr.id=vt.ausrichter
        INNER JOIN viewStaffeln vs ON vs.id=p.staffel
        WHERE vs.sysEingabelinks=1 AND p.linkGesendet=0 AND p.erg1 IS NULL
            AND vt.termin=DATE_ADD(CURDATE(),INTERVAL 3 DAY)
        ORDER BY ausr.id')->fetchAllAssociative();
    echo count($paar) . " Paarungen gefunden\n";

    // eMails vorbereiten
    $mails = array (); // ausrichter => list of arrays (mid, mi2, paarung, ...)
    foreach($paar as $spiel) {
        // Speichern
        $mails [$spiel ["ausrichter"]] [] = $spiel;
    }

    // eMails senden
    foreach ( $mails as $ausrichter=>$paarungen ){
        @SED_Eingabelinkmail ( $ausrichter, $paarungen );
        foreach ( $paarungen as $spiel ) {
            // Damit die Mail nicht nochmal versendet wird...
            SED_Query( 'UPDATE paarungen SET linkGesendet=1 WHERE id=? LIMIT 1', [$spiel['paarung']] );
        }
    }
?>
