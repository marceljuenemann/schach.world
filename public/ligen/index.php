<?
/* Schach-Ergebnisdienst
 *
 * Die index.php des Systems. Das Skript macht einige Vorbereitungen
 * und berechnet danach, welches Modul anzuzeigen ist.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage main
 */


    chdir ( "_inc" );
    global $globals;
    $globals ['basedir'] = "..";

    // Einige wichtige Funktionen zugänglich machen
    require_once ( "main.inc.php" );


    ///////////////////////////////////////////////////////
    // KONFIGURATION
    ///////////////////////////////////////////////////////

    // Keinen Content-Type header senden.
    // https://www.saotn.org/php-56-default_charset-change-may-break-html-output/
    ini_set( 'default_charset', "" );

    // Einlesen
    require_once ( "config.inc.php" );

    // Debugging
    if ( $globals ['debug'] )
    {
        ini_set ( "display_errors", true );
        error_reporting ( $globals ['debugmode'] );
    }

    // Zur Datenbank verbinden
    $globals ['db'] = mysql_connect ( $globals ['dbhost'], $globals ['dbuser'], $globals ['dbpw'] );
    if ( !mysql_select_db ( $globals ['dbname'], $globals ['db'] ) )
        SED_Error ( "Fehler: Datenbank konnte nicht geöffnet werden!", true );
    mysql_set_charset('latin1');
    $globals ['dbpw'] = "******";


    ///////////////////////////////////////////////////////
    // MODUL AUFRUFEN
    ///////////////////////////////////////////////////////

    // Aufzurufendes Modul in $globals [mod] schreiben
    require_once ( "modul.inc.php" );

    // Existiert es überhaupt?
    $modulpfad = "$globals[basedir]/_module/$globals[mod]/$globals[mod].php";
    if ( !file_exists ( $modulpfad ) )
        SED_Error ( "Fehler: Das angeforderte Modul existiert nicht!", true );

    // An Modul übergeben
    require_once ( $modulpfad );

    // Rest der Seite ausgeben
    if ( function_exists ( "SED_GUIclose" ) )
        SED_GUIclose ();
?>
