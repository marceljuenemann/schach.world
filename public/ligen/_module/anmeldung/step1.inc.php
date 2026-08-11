<?php
/* Mannschaftsmeldung: 1. Verein und Mannschaftsname
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage anmeldung
 */
    require_once ( "ajax.inc.php" );

    // Vereinsliste abfragen
    $vereine = SED_Query('SELECT * FROM dwz_vereine WHERE ZPS LIKE ? AND ZPS NOT LIKE \'%00\' ORDER BY Vereinname', [$prefs['anmVerband'].'%'])->fetchAllAssociative();

    // Mannschaften ohne Spieler vorschlagen
    $playerless = SED_Anmeldung::getPlayerlessTeams ();
    if ( $playerless && count ( $playerless ) )
    {
        echo "<b>M&ouml;chten Sie eine der folgenden Mannschaften anmelden?</b><ul>";
        foreach ( $playerless as $team )
            echo "<li><a href='".SED_GenerateFormAction()."&changeteam=$team[id]'>".SED_escape($team['name'])." $team[mnr]</a></li>";
        echo "</ul><b>Wenn die Mannschaft nicht dabei war, k&ouml;nnen Sie die Daten im folgenden selbst&auml;ndig eingeben:</b><br /><br />";
    } 

    // Text zur Vereinsliste
    ?>
    <b>Verein</b> Bitte w&auml;hlen Sie Ihren Verein aus der folgenden Liste aus.</b> 
    Sollte Ihr Verein 
    nicht aufgelistet sein, so w&auml;hlen Sie bitte den Eintrag 'Anderer...' ganz
    am Ende der Liste.<br />
    <select name='zps' id='Verein' onchange='OnVereinChange();'>
    <option value=''>- Verein w&auml;hlen -</option>
    <?php
        // Vereinsliste ausgeben
        foreach ($vereine as $verein)
            echo "<option value='$verein[ZPS]'>$verein[Vereinname]</option>";
        echo "<option value=''>- Anderer... -</option></select><br /><br />";
    
    // Spielgemeinschaft
    ?>
    <b>Spielgemeinschaft</b> Wenn Ihre Mannschaft eine Spielgemeinschaft mit einem anderen
    Verein ist, dann w&auml;hlen Sie hier bitte den zweiten Verein aus.
    Ansonsten lassen Sie das Feld unver&auml;ndert.<br />
    <select name='spielgemeinschaft' id='Spielgemeinschaft' onchange='OnVereinChange();'>
    <option value=''>- Keine Spielgemeinschaft -</option>
    <?php
        // Vereinsliste ausgeben
        foreach ($vereine as $verein)
            echo "<option value='$verein[ZPS]'>$verein[Vereinname]</option>";
        echo "<option value=''>- Anderer... -</option></select><br /><br />";
    
    // Angezeigter Name
    echo "<span style='font-weight: bold'>Mannschaftsname</span> ";
    echo "Unter welchem Namen soll Ihre Mannschaft im System angezeigt werden? Der angezeigte Name darf maximal 20 Zeichen lang sein.<br />";
    echo "<input type='text' style='margin-bottom: 15px; margin-top: 5px;' name='name' value='' size='20' maxlength='20' /><br />";

    // Mannschaftsnummer
    echo "<span style='font-weight: bold'>Mannschaftsnummer</span> ";
    echo "Geben Sie in dieses Feld die Nummer der Mannschaft ein, die sie anmelden. Also f&uuml;r die erste Mannschaft Ihres Vereins die 1, f&uuml;r die zweite die 2 usw.<br />";
    echo "<input type='text' style='margin-bottom: 15px; margin-top: 5px;' name='mnr' value='1' size='5' maxlength='2' /><br />";

    // Staffel
    if ( count ( $globals ['staffeln'] ) > 1 ){
        echo "<span style='font-weight: bold'>Staffel</span> ";
        echo "Geben Sie bitte ein, in welcher Staffel die Mannschaft spielt. Wenn Sie das nicht wissen, lassen Sie das Feld einfach frei.<br />";
        echo "<select name='staffel'><option value='0'></option>";
        foreach ( $globals ['staffeln'] as $id=>$name )
            echo "<option value='$id'>$name</option>";
        echo "</select><br /><br />";
    }

    // Javascript
    ?>
    <script type='text/javascript'><!--

        function OnVereinChange ()
        {
            // ZPS auslesen
            zps = document.getElementById ( "Verein" ).value;
            zps = zps + document.getElementById ( "Spielgemeinschaft" ).value;
            
            // Guten Mannschaftsnamen abfragen, wenn es nicht 'Anderer...' ist
            if ( zps != "" )
            <?php
                $ajax = new SED_AjaxRequest ( "getMannschaftsname" );
                $ajax->setOption ( "zps", "zps" );
                $ajax->onResult ( "document.getElementsByName ( 'name' ) [0].value = @RESULT@;" );
                echo $ajax->getJavascript ();
            ?>
        }

    --></script>
    <?php
?>
