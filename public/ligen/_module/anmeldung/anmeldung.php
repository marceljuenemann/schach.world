<?php
/* Mannschaftsmeldung: Frontend
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage anmeldung
 */

    require_once ( "turnier.inc.php" );
    require_once ( "anmeldung.class.php" );
    require_once ( "spieler.class.php" );
    require_once ( "gui.inc.php" );

    // Zugangsberechtigt?
    $isTurnierleiter = (isset($_GET["auth"]) && $_GET ["auth"] == SED_MD5_TL ());
    if ( !$prefs ['anmAktiv'] && !$isTurnierleiter )
        SED_Error ( "Sie sind nicht berechtigt, eine Mannschaft zu melden!", true );

    // Überschriften
    if ( !isset ( $admin ) )
        echo "<span class='sed_hl1'>Mannschaftsmeldung</span><br /><br />";

    // Javascript Schutz
    echo "<div id='AnmeldungInhalt' style='display: none; text-align: justify'>";

    /////////////////////////////////////////////
    // OBJEKT UND NEUE DATEN LADEN
    /////////////////////////////////////////////

    // Neues Anmeldungsobjekt erstellen
    $anmeldung = new SED_Anmeldung ();

    // Anmeldungs-Objekt aus POST lesen
    if ( isset ( $_POST ["class"] ) ){
        $anmeldung->parseJSON ( base64_decode ( $_POST ["class"] ) );
    }

    // Soll eine "leere" Mannschaft gefüllt werden?
    elseif ( isset ( $_GET ['changeteam'] ) ){
        $anmeldung = new SED_Anmeldung ( $_GET ['changeteam'] );
        $_POST ['step'] = 2;
    }

    // Neue Daten aus POST auslesen
    $anmeldung->setFields ( $_POST );


    /////////////////////////////////////////////
    // STEP-CONTROLLER
    /////////////////////////////////////////////

    // Step berechnen
    $step = isset ( $_POST ["step"] ) ? $_POST ["step"] : 1;
    if ( isset ( $_POST ['ohne_aufstellung'] ) && $_POST ['ohne_aufstellung'] ) $step = 6;

    // Inhaltsverzeichnis
    $arrIHV = array (
        "Vereinsdaten", "Spiellokal",
        "Mannschaftsf&uuml;hrer und Zusatzinfos",
        "Spielerauswahl", "Aufstellung"
    );
    for ( $i = 1; $i <= count ( $arrIHV ); ++$i ){
        $text = "Schritt $i: " . $arrIHV [$i-1] . "<br />";
        echo $i==$step ? "<b>$text</b>" : $text;
    }
    echo "<br />";

    // Form begin
    if ( $step <= count ( $arrIHV ) )
        echo "<span class='sed_hl2'>" . $arrIHV [$step-1] . "</span><br /><br />";
    echo "<form accept-charset='ISO-8859-1' action='".SED_GenerateFormAction()."' method='post' id='anmeldungsform'><div>";

    // Inhalt
    include_once ( "$globals[basedir]/_module/anmeldung/step$step.inc.php" );

    // Weiter-Button
    if ( $step <= count ( $arrIHV ) ){
        echo "<input type='hidden' name='step' value='" . ( $step + 1 ) . "' />";
        echo "<input type='hidden' name='class' value='" . base64_encode($anmeldung->getJSON ()) . "' />";
        echo "<input type='submit' class='sed_submit' value='Weiter' />";
    }

    // Formular beenden
    echo "</div></form>";


    /////////////////////////////////////////////
    // JAVASCRIPT SCHUTZ
    /////////////////////////////////////////////

  ?>
  </div><div id='AnmeldungScriptSchutz'>
    <span class='sed_hl2'>Seite wird geladen...</span><br />
    <br /><br />
    <br /><br />
    Sollte die Seite nach f&uuml;nf Sekunden noch nicht angezeigt werden,
    dann haben Sie Javascript nicht aktiviert. Informieren Sie
    sich, wie Sie <a href='http://code.google.com/p/schach-ergebnisdienst/wiki/JavascriptAktivieren' target='_blank'>Javascript in Ihrem Browser aktivieren</a>, und laden
    Sie diese Seite neu.<br /><br />
    Javascript wird ben&ouml;tigt, um den Anmeldeprouzess benutzerfreundlicher zu gestalten.
  </div>

  <script type="text/javascript"><!--

    document.getElementById ( 'AnmeldungScriptSchutz' ).style.display = "none";
    document.getElementById ( 'AnmeldungInhalt' ).style.display = "block";

  --></script>
  <?php
?>
