<?
/* SL-Bereich: Rundmail
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */

    require_once ( "login.inc.php" );
    require_once ( "mail.rundmail.inc.php" );
    require_once ( "runde.inc.php" );

    // Rundmail-Objekt anlegen
    $staffel = $admin ['staffel'] ? $admin ['staffel'] : $_GET ['staffel'];
    if ( !$staffel ) SED_Error ( "Keine Staffel angegeben!", true );
    $runde = isset ( $_POST ['runde'] ) ? $_POST ['runde'] : $_GET ['r'];
    $rundmail = new SED_Rundmail ( $staffel, $runde );

    // Absenden?
    if ( isset ( $_POST ['savebutton'] ) )
    {
        // Spielfestsetzung
        if ( $_POST ['runde'] )
            mysql_query ( "UPDATE paarungen SET festgelegt=1 WHERE staffel=$staffel AND runde=$_POST[runde] AND erg1 IS NOT NULL", $globals ['db'] );

        // Versenden
        $rundmail->Send ( $_POST ["subject"], $_POST ["text"] );

        // Erfolgsmeldung
        echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
        exit;
    }

    // Rundenauswahl generieren
    $select = "";
    for ( $r = 1; $r <= $rundmail->getRundenzahl (); ++$r )
        $select .= "<option value='$r'>$r. Spieltag</option>";
    $select .= "<option value='0'>Ohne Anhang</option>";
    $select = SED_SelectOption ( $select, $runde );

  // Ausgabe
  echo "<form action='".SED_GenerateFormAction()."' method='post'><div>

          <b>Anhang:</b> Ergebnisse vom <select name='runde'>$select</select> als PDF<br />
          <br />
          <b>Betreff der eMail:</b> <input name='subject' size='50' value='".$rundmail->getDefaultSubject()."'  /><br />
          <br />
          <textarea name='text' cols='75' rows='15'>".$rundmail->getDefaultText()."</textarea><br />
          <br />
          <input type='submit' name='savebutton' class='sed_submit' value='Abschicken' />
          <input type='button' class='sed_submit' value='Abbrechen' onclick=\"location='?admin=desktop-$admin[userid]-$admin[session]';\" />

        </div></form><br /><br />";
     
        
    // Liste der Empfänger holen (ist schon alphabetisch sortiert)
    $tos = $rundmail->getTo ();
    echo "<span class='sed_hl2'>Liste der Empfänger</span><br /><br />";
    
    foreach ( $tos as $team => $empfaenger ){
        // Mannschaftsführer
        if ( $team != "newsletter" ){
            echo "<b>".$globals['teams'][$team].":</b> ";
            echo implode ( ", ", $empfaenger );
            echo "<br />";
        
        // Newsletter
        } else {
            if ( count ( $empfaenger ) )
                echo "<br /><b>Newsletter-Abbonomenten:</b><br />";
            foreach ( $empfaenger as $nl )
                echo "$nl[email]<br />";
        }
    }
?>
