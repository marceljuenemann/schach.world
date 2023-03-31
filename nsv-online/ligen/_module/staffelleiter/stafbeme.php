<?
/* SL-Bereich: Spieltagsbemerkungen
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */

    require_once ( "login.inc.php");

  if ( isset ( $_POST ['admin_sl_bemerkungen'] ) )
  {
    // Bemerkung einfügen / bearbeiten
    $tmp = mysql_query ( "SELECT id FROM bemerkungen WHERE staffel=$admin[staffel] AND runde=$_POST[r]", $globals ['db'] );
    if ( mysql_num_rows ( $tmp ) )
    {
      mysql_query ( "UPDATE bemerkungen SET text='$_POST[text]' WHERE staffel=$admin[staffel] AND runde=$_POST[r] AND text<>'$_POST[text]' LIMIT 1", $globals ['db'] );
      SED_Cache::clearSpieltag ( $admin ['staffel'], $_POST["r"] );
	}
    else
    {
      if ( !mysql_query ( "INSERT INTO bemerkungen SET staffel=$admin[staffel], runde=$_POST[r], text='$_POST[text]'", $globals ['db'] ) )
        SED_Error ( "Fehler beim einfügen der Bemerkung!" );
      SED_Cache::clearSpieltag ( $admin ['staffel'], $_POST["r"] );
    }

    // Erfolgsmeldung
    echo "<b>Die neuen Daten wurden erfolgreich gespeichert!</b><br /><br />";
  }

    // Vobereitungen
    $rundenzahl = SED_GetRundenzahl ( $admin ['staffel'] );
    require ( "runde.inc.php" );

    // Gespeicherte Bemerkungen herausfinden
    $res = mysql_query ( "SELECT runde, text FROM bemerkungen WHERE staffel=$admin[staffel]", $globals ['db'] );
    $bemerkungen = array ();
    while ( $row = mysql_fetch_array ( $res, MYSQL_ASSOC ) )
        $bemerkungen [$row ['runde']] = $row ['text'];

    // Alle Spiele finden
    $res = mysql_query ( "SELECT * FROM paarungen WHERE staffel=$admin[staffel]", $globals['db'] );
    $spiele = array ();
    while ( $spiel = mysql_fetch_array ( $res, MYSQL_ASSOC ) )
        $spiele [$spiel["runde"]][] = $spiel;

    // Ausgabe
    for ( $r = 1; $r <= $rundenzahl; ++$r )
    {
        // Container
        echo "<div id='spieltag$r'>";
        echo "<span class='sed_hl2'>$r. Spieltag</span>";
        if ( $r > 1 )
            echo "<a href='javascript:showOne($r-1)'><img src='$globals[systemicons]pre.png' alt='Vorheriger' class='sed_admin_icon' /></a>";
        if ( $r < $rundenzahl )
            echo "<a href='javascript:showOne($r+1)'><img src='$globals[systemicons]next.png' alt='N&auml;chster' class='sed_admin_icon' /></a>";
        echo "<br /><br /> ";

        // Allgemeine Bemerkungen
        if ( !isset ( $bemerkungen [$r] ) ) $bemerkungen [$r] = "";
        echo "  <form style='margin: 0' action='".SED_GenerateFormAction()."&r=$r' method='post'>
                  <input type='hidden' name='r' value='$r' />
                  <textarea name='text' cols='60' rows='5'>".$bemerkungen[$r]."</textarea><br />
                  <input type='submit' class='sed_submit' name='admin_sl_bemerkungen' value='Speichern'  /><br /><br />
                </form>";

        // Bemerkungen zu einzelnen Spielen
        if ( isset ( $spiele [$r] ) ){
            echo "<br /><br /><span class='sed_hl2'>Bemerkungen zu einzelnen Spielen:</span><br /><br />";
            foreach ( $spiele [$r] as $spiel ){
                echo "<b>";
                echo $globals ['teams'][$spiel['mannschaft1']];
                echo " - ";
                echo $globals ['teams'][$spiel['mannschaft2']];
                echo "</b> <a style='text-decoration:none' href='?admin=alleeing-$admin[userid]-$admin[session]&p=$spiel[id]#zusatz'><img src='$globals[systemicons]desk_bearbeiten.png' alt='Bemerkung bearbeiten' class='sed_admin_icon' />Bearbeiten</a><br />";
                if ( $spiel ['bemerkung'] )
                    echo "<i>$spiel[bemerkung]</i><br /><br />";
            }
        }
        echo "<br /><br /></div>";
    }
    
    // Javascript zum Verstecken von Spieltagen
    ?><script type='text/javascript'><!--
        function show ( spieltag, type ){
            document.getElementById ( "spieltag"+spieltag ).style.display = type;
        }
        
        function showOne ( spieltag ){
            for ( r = 1; r <= <? echo $rundenzahl; ?>; ++r )
                show ( r, "none" );
            show ( spieltag, "block" );
        }
        
        // Anfangseinstellung
        showOne ( <?=$_GET['r']?> );
        
    --></script><?    
?>
