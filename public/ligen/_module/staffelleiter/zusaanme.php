<?php
/* SL-Bereich: Anmeldungs-Optionen
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
    require_once ( "auth.inc.php" );
?>

<form action='<?php echo SED_GenerateFormAction(); ?>' method='post'><div><fieldset class='sed_admin_desk'><legend>Einstellungen zur Mannschaftsmeldung</legend>

  <?php
    // Ändern?
    if ( isset ( $_POST ['anme_change'] ) )
    {
      // Globale Variablen ändern
      $prefs ['anmAktiv'] = ( isset ( $_POST ['aktiv'] ) && $_POST ['aktiv'] ) ? 1 : 0;
      $prefs ['anmVerband'] = $_POST ['verband'];
      $prefs ['anmGeburt'] = (int) $_POST ['geburt'];
      $prefs ['anmGeschlecht'] = $_POST ['geschlecht'];
      $prefs ['anmTLMail'] = @$_POST ['TLMail'] ?: 0;

      // In Datenbank speichern
    if (SED_TryQuery('UPDATE turniere SET anmAktiv=?, anmVerband=?, anmGeburt=?, anmGeschlecht=?, anmTLMail=? WHERE id=? LIMIT 1', [
          $prefs['anmAktiv'], 
          $prefs['anmVerband'], 
          $prefs['anmGeburt'], 
          $prefs['anmGeschlecht'], 
          $prefs['anmTLMail'], 
          $globals['tid']
      ]))
      {
        echo "<b>&Auml;nderungen erfolgreich gespeichert!</b><br /><br />";

        // Metatag Refresh
        echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
      }
      else
        SED_Error("Ausnahmefehler #935");

    }

    // Formulardaten
    $paddingStyle = "style='margin-top: 5px'";
    $attribAktiv = $prefs ['anmAktiv'] ? "checked='checked'" : "";
    $attribTLMail = $prefs ['anmTLMail'] ? "checked='checked'" : "";

    // Verband
    $verbaende = SED_Query('SELECT * FROM verbaende')->fetchAllAssociative();
    $optionsVerband = "";
    foreach ($verbaende as $verband)
      $optionsVerband .= "<option value='" . $verband['zps'] . "'>" . str_replace(" ", "&nbsp;", $verband['name']) . "</option>";
    $optionsVerband = SED_SelectOption ( $optionsVerband, $prefs ['anmVerband'] );

    // Geschlechtsbegrenzung
    $optionsMW = "<option value=''>Alle</option><option value='W'>Nur weiblich</option><option value='M'>Nur m&auml;nnlich</option>";
    $optionsMW = SED_SelectOption ( $optionsMW, $prefs ['anmGeschlecht'] );
  ?>

  <input <?php echo $paddingStyle; ?> type='checkbox' id='aktiv' name='aktiv' value='1' <?php echo $attribAktiv; ?> /> <label for='aktiv'><b>Mannschaftsmeldung durch Vereine aktivieren</b></label><br />
  Wenn Sie diese Option aktivieren, k&ouml;nnen Vereine ihre Mannschaften
  selbst&auml;ndig &uuml;ber folgenden Link eingeben:<br />
  <?php $url = "$globals[httppath]$prefs[directory]/anmeldung.html"; echo "<a href='$url' target='_blank'>$url</a><br />"; ?>
  Andernfalls ist die Eingabe nur &uuml;ber folgenden Link m&ouml;glich:<br />
  <?php $url = "$globals[httppath]$prefs[directory]/?m=anmeldung&auth=".SED_MD5_TL(); echo "<a href='$url' target='_blank'>$url</a>"; ?><br />
  <br />

  <b>Verband:</b><br />
  In welchem Verband m&uuml;ssen Mannschaften gemeldet sein, die sich zu Ihrem
  Turnier anmelden d&uuml;rfen?<br />
  <select <?php echo $paddingStyle; ?> name='verband'><?php echo $optionsVerband; ?></select><br />
  <br />

  <b>Altersbegrenzung:</b><br />
  Geben Sie hier den &auml;ltesten Jahrgang an, aus dem Spieler teilnehmen d&uuml;rfen, oder
  setzen Sie das Feld auf 0, um die Altersbegrenzung zu deaktivieren.<br />
  <input <?php echo $paddingStyle; ?> type='text' name='geburt' value='<?php echo $prefs ['anmGeburt']; ?>' size='4' maxlength='4' /><br />
  <br />

  <b>Geschlechts-Begrenzung:</b><br />
  Benutzen Sie das folgende Feld, um nur M&auml;dchen- oder Frauenmannschaften zu erlauben<br />
  <select <?php echo $paddingStyle; ?> name='geschlecht'><?php echo $optionsMW; ?></select><br />
  <br />

  <b>Info-Mail:</b><br />
  M&ouml;chten Sie eine eMail erhalten, wenn eine Mannschaft gemeldet wird? Nur sinnvoll, wenn die Vereine ihre Mannschaften selbst&auml;ndig melden.<br />
  <input <?php echo $paddingStyle; ?> type='checkbox' id='TLMail' name='TLMail' value='1' <?php echo $attribTLMail; ?> /> <label for='TLMail'>Info-Mail aktivieren</label><br />
  <br />

  <input type='submit' class='sed_submit' name='anme_change' value='Absenden' />
  <input type='button' class='sed_submit' value='Abbrechen' onclick="<?php echo "location='?admin=desktop-$admin[userid]-$admin[session]';"; ?>" />

</fieldset></div></form><br />




<form action='<?php echo SED_GenerateFormAction(); ?>' method='post'><div><fieldset class='sed_admin_desk'><legend>Zusatzfelder</legend>
    An dieser Stelle k&ouml;nnen Sie zus&auml;tzliche Felder festlegen, die bei der Anmeldung abgefragt werden. Ein Beispiel hierf&uuml;r ist ein Anmerkungsfeld oder der Name des 1. Vorsitzenden. Geben Sie pro Zeile bitte die Bezeichnung eines Feldes an. Wenn das Feld mehrzeilig sein soll, dann h&auml;ngen Sie an die Bezeichnung bitte #0 an.<br /><br />

    <?php
        // Ändern?
        if ( isset ( $_POST ['anme_felder'] ) )
        {
            // In Datenbank speichern
            if (SED_TryQuery('UPDATE turniere SET anmZusatzfelder=? WHERE id=? LIMIT 1', [$_POST['anme_textarea'], $globals['tid']]))
            {
                echo "<b>&Auml;nderungen erfolgreich gespeichert!</b><br /><br />";
                $prefs['anmZusatzfelder'] = $_POST['anme_textarea'];
            }
        }
    ?>        

    <textarea name='anme_textarea' cols='60' rows='8'><?php echo $prefs ['anmZusatzfelder']; ?></textarea><br /><br />
    <input type='submit' class='sed_submit' name='anme_felder' value='Absenden' />
    <input type='button' class='sed_submit' value='Abbrechen' onclick="<?php echo "location='?admin=desktop-$admin[userid]-$admin[session]';"; ?>" />

</fieldset></div></form><br />




<fieldset class='sed_admin_desk'><legend>Eingaben in die Zusatzfelder</legend>
    An dieser Stelle k&ouml;nnen Sie nachlesen, was die Mannschaften in die Zusatzfelder, wie z.B. Anmerkungen, eingegeben haben.<br /><br />
    <?php
        if ( isset ( $_GET ['showzusatzfelder'] ) )
        {
    ?>
            <table class='sed_tabelle'><tr><th>Mannschaft</th><th>Feld</th><th>Inhalt</th></tr>
                <?php
                    $zusatzfelder = SED_Query('SELECT * FROM anmeldungZusatzfelder a INNER JOIN mannschaften m ON m.id=a.mannschaft WHERE m.turnier=? AND inhalt<>\'\' ORDER BY m.name, a.feldname', [$globals['tid']])->fetchAllAssociative();
                    foreach ($zusatzfelder as $tmp)
                        echo "<tr><td>".$globals['teams'][$tmp['mannschaft']]."</td><td>".$tmp['feldname']."</td><td>".nl2br($tmp['inhalt'])."</td></tr>";
                ?>
            </table>
    <?php
        }
        else
            echo "<a href='?admin=zusaanme-$admin[userid]-$admin[session]&showzusatzfelder=1'>Daten anzeigen</a><br /><br />";
    ?>
</fieldset>

