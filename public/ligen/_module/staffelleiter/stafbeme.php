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
    $result = SED_Query('SELECT id FROM bemerkungen WHERE staffel=? AND runde=?', [$admin['staffel'], $_POST['r']]);
    if ($result->rowCount() > 0)
    {
      SED_TryQuery('UPDATE bemerkungen SET text=? WHERE staffel=? AND runde=? AND text<>? LIMIT 1', 
        [$_POST['text'], $admin['staffel'], $_POST['r'], $_POST['text']]);
      SED_Cache::clearSpieltag($admin['staffel'], $_POST["r"]);
	  }
    else
    {
      if (!SED_TryQuery('INSERT INTO bemerkungen SET staffel=?, runde=?, text=?', [$admin['staffel'], $_POST['r'], $_POST['text']]))
        SED_Error("Fehler beim einf&uuml;gen der Bemerkung!");
      SED_Cache::clearSpieltag($admin['staffel'], $_POST["r"]);
    }

    // Erfolgsmeldung
    echo "<b>Die neuen Daten wurden erfolgreich gespeichert!</b><br /><br />";
  }

    // Vobereitungen
    $rundenzahl = SED_GetRundenzahl ( $admin ['staffel'] );
    require ( "runde.inc.php" );

    // Gespeicherte Bemerkungen herausfinden
    $remarks = SED_Query('SELECT runde, text FROM bemerkungen WHERE staffel=?', [$admin['staffel']])->fetchAllAssociative();
    $bemerkungen = array();
    foreach ($remarks as $row)
        $bemerkungen[$row['runde']] = $row['text'];

    // Alle Spiele finden
    $matches = SED_Query('SELECT * FROM paarungen WHERE staffel=?', [$admin['staffel']])->fetchAllAssociative();
    $spiele = array();
    foreach ($matches as $spiel)
        $spiele[$spiel["runde"]][] = $spiel;

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
                echo "</b> <a style='text-decoration:none' href='?admin=alleeing-$admin[userid]-$admin[session]&pid=$spiel[id]#zusatz'><img src='$globals[systemicons]desk_bearbeiten.png' alt='Bemerkung bearbeiten' class='sed_admin_icon' />Bearbeiten</a><br />";
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
