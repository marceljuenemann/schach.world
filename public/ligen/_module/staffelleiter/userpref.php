<?php
/* SL-Bereich: Turnierleiter/Staffelleiter bearbeiten
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

    /////////////////////////////
    // Kontakt und Passwort bearbeiten - Vorbereitungen

    // Der Turnierleiter bearbeitet den Staffelleiter?
    $up_userid = ( isset ( $_GET ['staffel'] ) && $admin ['usertype'] == "t" ) 
        ? SED_Value ( 'SELECT leiter FROM staffeln WHERE id=? AND turnier=?', [$_GET['staffel'], $globals['tid']] ) 
        : $admin ['userid'];

    // Benutzername
    if ( isset ( $admin ['staffel'] ) && $admin ['staffel'] != 0 )
        echo "<span class='sed_hl3'>Staffelleiter " . $globals ['staffeln'][$admin['staffel']] . "</span><br /><br />";
    elseif ( isset ( $_GET ['staffel'] ) && $_GET ['staffel'] != 0 )
        echo "<span class='sed_hl3'>Staffelleiter " . $globals ['staffeln'][$_GET['staffel']] . "</span><br /><br />";
    else
        echo "<span class='sed_hl3'>Turnierleiter $prefs[name]</span><br /><br />";

    /////////////////////////////
    // Neues Passwort speichern
    if ( isset ( $_POST ['pw_change'] ) )
    {
        if ( $_POST ['pw_new1'] != $_POST ['pw_new2'] )
            SED_Error ( "Die Passworteingaben sind nicht identisch!" );
        elseif ( strlen ( $_POST ['pw_new1'] ) < 6 )
            SED_Error ( "Ihr Passwort muss mindesten sechs Zeichen lang sein!" );
        elseif ( $_POST ['pw_new1'] == "" )
            SED_Error ( "Bitte geben Sie ein neues Passwort an!" );
        else
        {
            if ( !SED_TryQuery ( 'UPDATE benutzer SET passwort=MD5(?) WHERE id=?', [$_POST['pw_new1'], $up_userid] ) )
                SED_Error ( "Fehler beim Setzen des neuen Passworts!" );
            else
            {
                echo "<b>Das Passwort wurde erfolgreich ge&auml;ndert!</b>";
                echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
            }
        }
    }

    /////////////////////////////
    // Neues Passwort setzen
    ?>
    <form action="<?php echo SED_GenerateFormAction(); ?>" method="post" style="text-align: left;"><fieldset>
        <legend>Passwort bearbeiten</legend>
        <table cellspacing="0" cellpadding="2">
        <tr><td>Neues Passwort: </td><td><input type="password" name="pw_new1" /></td></tr>
        <tr><td>Passwort wiederholen: </td><td><input type="password" name="pw_new2" /></td></tr>
        <tr><td></td><td><input type="submit" class="sed_submit" name="pw_change" value="&Auml;ndern" /> <input type='button' class='sed_submit' value='Abbrechen' onclick="<?php echo "location='?admin=desktop-$admin[userid]-$admin[session]';"; ?>" /></td></tr>
        </table>
        </fieldset></form><br />
    <?php

    /////////////////////////////
    // Neues Kontaktdaten setzen
    if ( isset ( $_POST ['kontakt_change'] ) )
    {
        // Name gesetzt?
        if ( strlen ( $_POST ['name'] ) < 3 )
            SED_Error ( "Bitte setzen Sie einen Namen!" );

        // Email richtig?
        elseif ( !SED_IsValidEmail ( $_POST ['email'] ) )
            SED_Error ( "Geben Sie eine g&uuml;ltige Emailadresse an!" );

        // Daten ändern
        else
        {
            if ( !SED_TryQuery ( 'UPDATE benutzer SET name=?, email=?, telefon=?, telefon2=? WHERE id=? LIMIT 1', [$_POST['name'], $_POST['email'], $_POST['telefon'], $_POST['telefon2'], $up_userid] ) )
                SED_Error ( "Fehler beim Setzen der neuen Daten!" );
            else
            {
                // Cache leeren
                if ( $admin ['usertype'] == 's' )
                    SED_Cache::clearSpieltag ( $admin ['staffel'] );
                elseif ( $admin ['usertype'] == 't' && isset ( $_GET ['staffel'] ) )
                    SED_Cache::clearSpieltag ( $_GET ['staffel'] );

                // Fertig
                echo "<b>Die Daten wurden erfolgreich ge&auml;ndert!</b>";
                echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
            }
        }
    }
    
    /////////////////////////////
    // Kontaktdaten bearbeiten
    $daten = SED_Row ( 'SELECT name, telefon, telefon2, email FROM benutzer WHERE id=?', [$up_userid]);
    ?>
        <form action="<?php echo SED_GenerateFormAction(); ?>" method="post" style="text-align: left;"><fieldset>
        <legend>Kontaktdaten</legend>
        <table cellspacing="0" cellpadding="2">
        <tr><td>Name: </td><td><input value="<?php echo $daten ['name']; ?>" type="text" name="name" /></td></tr>
        <tr><td>Email: </td><td><input value="<?php echo $daten ['email']; ?>" type="text" name="email" /></td></tr>
        <tr><td>Telefon: </td><td><input value="<?php echo $daten ['telefon']; ?>" type="text" name="telefon" /></td></tr>
        <tr><td>Telefon 2: </td><td><input value="<?php echo $daten ['telefon2']; ?>" type="text" name="telefon2" /></td></tr>
        <tr><td></td><td><input type="submit" class="sed_submit" name="kontakt_change" value="&Auml;ndern" /> <input type='button' class='sed_submit' value='Abbrechen' onclick="<?php echo "location='?admin=desktop-$admin[userid]-$admin[session]';"; ?>" /></td></tr>
        </table>
        </fieldset></form><br />
