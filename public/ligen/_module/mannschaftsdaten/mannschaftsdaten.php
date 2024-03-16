<?
/* Ändern von Mannschaftsdaten (Spiellokal etc.)
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage mannschaft
 */

    require_once ( "turnier.inc.php" );
    require_once ( "gui.inc.php" );
    require_once ( "auth.inc.php" );
    require_once ( "cache.inc.php" );

    if ( $_GET ['auth'] != SED_MD5_MID ( $_GET ['mid'] ) )
        SED_Error ( "Sie sind nicht berechtigt, diese Seite zu betrachten!", true );

    // Aktuelle Daten abfragen
    $daten = getDaten ();
    echo "<span class='sed_hl2'>Mannschaftsdaten ".$globals['teams'][$_GET['mid']]."</span><br /><br />";

    function getDaten ()
    {
      return SED_Row("SELECT * FROM mannschaften WHERE id=?", [$_GET['mid']]);
    }

      ///////////////////////////////////////
      // MANNSCHAFTSNAME
      ///////////////////////////////////////

      if (isset($admin) && ($admin['usertype'] == 's' || $admin['usertype'] == 't')) {
        if ( isset ( $_POST ['name_change'] ) )
        {
              if ( !mysql_query ( "UPDATE mannschaften SET name='$_POST[new_name]' WHERE id='$_GET[mid]' LIMIT 1", $globals ['db'] ) )
                  SED_Error ( "Fehler beim Setzen der neuen Daten!" );
              else
                  echo "<b>Neue Daten erfolgreich gespeichert!</b>";
              $daten = getDaten ();
              SED_Cache::clearAll ();
        }
        ?>
          <form action="<? echo SED_GenerateFormAction(); ?>" method="post" style="text-align: left;">
            <fieldset><legend>Mannschaftsname</legend>
              <input value="<? echo $daten ['name']; ?>" type="text" maxlength='20' size='20' name="new_name" />
              <input type="submit" class="sed_submit" name="name_change" value="&Auml;ndern" />
          </fieldset></form><br />
        <?
      }

      ///////////////////////////////////////
      // SPIELLOKAL
      ///////////////////////////////////////

      if ( isset ( $_POST ['so_change'] ) )
      {
            if ( !mysql_query ( "UPDATE mannschaften SET so_name='$_POST[so_name]', so_hinweis='$_POST[so_hinweis]', so_strasse='$_POST[so_strasse]', so_plz='$_POST[so_plz]', so_stadt='$_POST[so_stadt]', so_telefon='$_POST[so_telefon]' WHERE id=$_GET[mid] LIMIT 1", $globals ['db'] ) )
                SED_Error ( "Fehler beim Setzen der neuen Daten!" );
            else
                echo "<b>Neue Daten erfolgreich gespeichert!</b>";
            $daten = getDaten ();
      }
      ?>
        <form action="<? echo SED_GenerateFormAction(); ?>" method="post" style="text-align: left;"><fieldset>
          <legend>Spiellokal</legend>
            <table cellspacing="0" cellpadding="2">
            <tr><td>Name: </td><td><input value="<? echo $daten ['so_name']; ?>" type="text" maxlength='40' name="so_name" /></td></tr>
            <tr><td>Hinweis: </td><td><input value="<? echo $daten ['so_hinweis']; ?>" type="text" maxlength='255' name="so_hinweis" /></td></tr>
            <tr><td>Sta&szlig;e: </td><td><input value="<? echo $daten ['so_strasse']; ?>" type="text" maxlength='30' name="so_strasse" /></td></tr>
            <tr><td>PLZ: </td><td><input value="<? echo $daten ['so_plz']; ?>" type="text" size='5' name="so_plz" /></td></tr>
            <tr><td>Stadt: </td><td><input value="<? echo $daten ['so_stadt']; ?>" type="text" maxlength='30' name="so_stadt" /></td></tr>
            <tr><td>Telefon: </td><td><input value="<? echo $daten ['so_telefon']; ?>" type="text" maxlength='15' name="so_telefon" /></td></tr>
            <tr><td></td><td><input type="submit" class="sed_submit" name="so_change" value="&Auml;ndern" /></td></tr>
          </table>
        </fieldset></form><br />
      <?


      ///////////////////////////////////////
      // KONTAKTDATEN BEARBEITEN
      ///////////////////////////////////////

      if ( isset ( $_POST ['kontakt_change'] ) )
      {
        // Name gesetzt?
        if ( strlen ( $_POST ['name'] ) < 3 )
          SED_Error ( "Bitte setzen Sie einen Namen!" );

        // Daten ändern
        else
        {
            if ( !mysql_query ( "UPDATE mannschaften SET mf_name='$_POST[name]', mf_email='$_POST[email]', mf_telefon='$_POST[telefon]', mf_telefon2='$_POST[telefon2]' WHERE id=$_GET[mid] LIMIT 1", $globals ['db'] ) )
                SED_Error ( "Fehler beim Setzen der neuen Daten!" );
            else
                echo "<b>Neue Daten erfolgreich gespeichert!</b>";
            $daten = getDaten ();
        }
      }


      ///////////////////////////////////////
      // KONTAKTDATEN ANZEIGEN
      ///////////////////////////////////////

      ?>
        <a name="mf" href=""></a>
        <form action="<? echo SED_GenerateFormAction(); ?>" method="post" style="text-align: left;"><fieldset>
          <legend>Mannschaftsf&uuml;hrer</legend>
            <table cellspacing="0" cellpadding="2">
            <tr><td>Name: </td><td><input value="<? echo $daten ['mf_name']; ?>" maxlength='30' type="text" name="name" /></td></tr>
            <tr><td>Email: </td><td><input value="<? echo $daten ['mf_email']; ?>" maxlength='50' type="text" name="email" /></td></tr>
            <tr><td>Telefon: </td><td><input value="<? echo $daten ['mf_telefon']; ?>" maxlength='40' type="text" name="telefon" /></td></tr>
            <tr><td>Telefon 2: </td><td><input value="<? echo $daten ['mf_telefon2']; ?>" maxlength='40' type="text" name="telefon2" /></td></tr>
            <tr><td></td><td><input type="submit" class="sed_submit" name="kontakt_change" value="&Auml;ndern" /></td></tr>
          </table>
        </fieldset></form><br />
      <?


      ///////////////////////////////////////
      // WEITERE EMPFÄNGER
      ///////////////////////////////////////

        // Einen Empfänger hinzufügen
        if ( isset ( $_POST ['zuemp_add'] ) )
        {
            if ( SED_IsValidEmail ( $_POST ['field0'] ) )
            {
                // Checkboxen verarbeiten
                $f1 = ( isset ( $_POST ['field1'] ) && $_POST ['field1'] == 1 ) ? 1 : 0;
                $f2 = ( isset ( $_POST ['field2'] ) && $_POST ['field2'] == 1 ) ? 1 : 0;
                $f3 = ( isset ( $_POST ['field3'] ) && $_POST ['field3'] == 1 ) ? 1 : 0;

                // Datensatz einfügen
                if ( ! mysql_query ( "INSERT INTO zusatzempfaenger SET mannschaft=$_GET[mid], email='$_POST[field0]', eingabelink=$f1, bestaetigung=$f2, rundmail=$f3", $globals ['db'] ) )
                    SED_Error ( "Fehler beim Einf&uuml;gen!" );
                else
                    echo "<b>Der Empf&auml;nger wurde erfolgreich hinzugef&uuml;gt!</b>";
            }
            else
                SED_Error ( "Ung&uuml;ltige Emailadresse!" );
        }

        // Einen Empfänger löschen
        if ( isset ( $_GET ['zuemp_del'] ) )
        {
          if ( ! mysql_query ( "DELETE FROM zusatzempfaenger WHERE id=$_GET[zuemp_del] AND mannschaft=$_GET[mid] LIMIT 1", $globals ['db'] ) )
            SED_Error ( "Fehler beim L&ouml;schen!" );
          else
          {
            echo "<b>Der Empf&auml;nger wurde erfolgreich gel&ouml;scht!</b>";
          }
          unset ( $_GET ['zuemp_del'] );
        }

        // Auflistung der Empfänger
        ?>
          <a name='zuemp'></a>
          <form action="<? echo SED_GenerateFormAction(); ?>" method="post" style="text-align: left;"><fieldset>
            <legend>Weitere Empf&auml;nger</legend>
            &Uuml;ber diese Funktion k&ouml;nnen Sie weitere Emailadressen hinterlegen, an die Eingabelinks und Ergebnisse gesendet werden sollen. In der folgenden Tabelle sind die vorhandenen Zusatzempf&auml;nger aufgelistet.<br /><br />

            <table class='sed_tabelle'>
              <tr><th>Adresse</th><th>Eingabelink</th><th>Best&auml;tigung</th><th>Rundmail</th><th></th></tr>
              <?
                // Zusatzempfänger abfragen
                $rsrc = mysql_query ( "SELECT email, IF(eingabelink=1,'Ja','Nein'), IF(bestaetigung=1,'Ja','Nein'), IF(rundmail=1,'Ja','Nein'), id FROM zusatzempfaenger WHERE mannschaft=$_GET[mid]", $globals ['db'] );
                if ( $rsrc )
                  while ( $tmp = mysql_fetch_array ( $rsrc, MYSQL_NUM ) )
                  {
                    if ( strlen ( $tmp [0] ) > 26 )  $tmp [0] = substr ( $tmp [0], 0, 23 ) . "...";
                    echo "<tr><td>$tmp[0]</td><td>$tmp[1]</td><td>$tmp[2]</td><td>$tmp[3]</td>";
                    echo "<td><a href='".SED_GenerateFormAction()."&zuemp_del=$tmp[4]'>L&ouml;schen</a></td></tr>";
                  }
              ?>
            </table>
          </fieldset></form><br />


          <form action="<? echo SED_GenerateFormAction(); ?>" method="post" style="text-align: left;"><fieldset>
            <legend>Empf&auml;nger hinzuf&uuml;gen</legend>

            Emailadresse: <input type="text" name="field0" /><br /><br />

            <input type='checkbox' name='field1' id='field1' value='1' checked='checked' />
                <label for='field1'><strong>Eingabelink erhalten</strong> - Wenn Sie diese Option aktivieren, dann wird an obige Emailadresse ebenfalls ein Link zur Eingabe der Ergebnisse verschickt. Nutzen Sie diese Funktion, wenn eine andere Person f&uuml;r Sie die Ergebnisse melden soll.</label><br /><br />

            <input type='checkbox' name='field2' id='field2' value='1' checked='checked' />
                <label for='field2'><strong>Eingabebest&auml;tigung erhalten</strong> - Sobald die Ergebnisse eingetragen wurden, wird eine Best&auml;tigung mit den Eingaben an die Mannschaftsf&uuml;hrer und den Staffelleiter gesendet. Aktivieren Sie diese Option, wenn diese Best&auml;tigung auch an obige Adresse gesendet werden soll. </label><br /><br />

            <input type='checkbox' name='field3' id='field3' value='1' checked='checked' />
                <label for='field3'><strong>Rundmail erhalten</strong> - Wenn der Staffelleiter alle Ergebnisse erhalten und gepr&uuml;ft hat, dann verschickt er eine Rundmail an alle Mannschaftsf&uuml;hrer. Aktivieren Sie diese Option, wenn diese Rundmail auch an obige Adresse gesendet werden soll.</label><br /><br />

            <input type="submit" class="sed_submit" name="zuemp_add" value="Hinzuf&uuml;gen" />
          </table>


            </fieldset></form><br />

