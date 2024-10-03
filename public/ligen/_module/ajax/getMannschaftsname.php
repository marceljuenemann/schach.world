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
        // Nun den letzten Namen abfragen, den eine Mannschaft mit der gleichen ZPS hatte
        $name = SED_Query("SELECT name FROM mannschaften WHERE zps=? ORDER BY id DESC", [$zps])->fetchOne();
        if (!$name) return false;

        // Sonst Ausgabe
        echo utf8_encode ( $name );
        return true;
    }

    // Erstmal normaler Versuch
    if ( getMannschaftsname ( $_GET ['zps'] ) ) exit;
    
    // Bei Spielgemeinschaften nur die erste ZPS
    if ( getMannschaftsname ( substr ( $_GET ['zps'], 0, 5 ) ) ) exit;

	// Ansonsten einfach den Vereinsnamen nehmen und abschneiden
    $name = SED_Value("SELECT Vereinname FROM dwz_vereine WHERE ZPS=?", [substr($_GET['zps'],0,5)]);
    echo utf8_encode ( substr($name, 0, 15) );
	exit;
?>
