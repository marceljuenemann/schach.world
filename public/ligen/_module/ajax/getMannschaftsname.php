<?
/* AJAX: Mannschaftsname nach Verein
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage ajax
 */

    function getMannschaftsname ( $zps ){
        global $globals;
        
        // Nun den letzten Namen abfragen, den eine Mannschaft mit der gleichen ZPS hatte
        $rsrc = mysql_query ( "SELECT name FROM mannschaften WHERE zps='$zps' ORDER BY id DESC", $globals ['db'] );

        // Hat das nicht geklappt?
        if ( !$rsrc || !mysql_num_rows ( $rsrc ) )
            return false;
        
        // Sonst Ausgabe
        echo SED_utf8_encode ( reset ( mysql_fetch_array ( $rsrc ) ) );
        return true;
    }

    // Erstmal normaler Versuch
    if ( getMannschaftsname ( $_GET ['zps'] ) ) exit;
    
    // Bei Spielgemeinschaften nur die erste ZPS
    if ( getMannschaftsname ( substr ( $_GET ['zps'], 0, 5 ) ) ) exit;

	// Ansonsten einfach den Vereinsnamen nehmen und abschneiden
    echo SED_utf8_encode ( substr ( @reset ( mysql_fetch_array ( mysql_query ( "SELECT Vereinname FROM dwz_vereine WHERE ZPS='".substr($_GET['zps'],0,5)."'" ) ) ), 0, 15 ) );
	exit;
?>
