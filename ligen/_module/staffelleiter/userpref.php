<?
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
        ? reset ( SED_MYSQL_Array ( "SELECT leiter FROM staffeln WHERE id=$_GET[staffel] AND turnier=$globals[tid]", true ) ) 
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
            if ( !mysql_query ( "UPDATE benutzer SET passwort=MD5('$_POST[pw_new1]') WHERE id=$up_userid LIMIT 1", $globals ['db'] ) )
                SED_Error ( "Fehler beim Setzen des neuen Passworts!" );
            else
            {
                echo "<b>Das Passwort wurde erfolgreich geändert!</b>";
                echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
            }
        }
    }

    /////////////////////////////
    // Neues Passwort setzen
    ?>
    <form action="<? echo SED_GenerateFormAction(); ?>" method="post" style="text-align: left;"><fieldset>
        <legend>Passwort bearbeiten</legend>
        <table cellspacing="0" cellpadding="2">
        <tr><td>Neues Passwort: </td><td><input type="password" name="pw_new1" /></td></tr>
        <tr><td>Passwort wiederholen: </td><td><input type="password" name="pw_new2" /></td></tr>
        <tr><td></td><td><input type="submit" class="sed_submit" name="pw_change" value="Ändern" /> <input type='button' class='sed_submit' value='Abbrechen' onclick="<? echo "location='?admin=desktop-$admin[userid]-$admin[session]';"; ?>" /></td></tr>
        </table>
        </fieldset></form><br />
    <?

    /////////////////////////////
    // Neues Kontaktdaten setzen
    if ( isset ( $_POST ['kontakt_change'] ) )
    {
        // Name gesetzt?
        if ( strlen ( $_POST ['name'] ) < 3 )
            SED_Error ( "Bitte setzen Sie einen Namen!" );

        // Email richtig?
        elseif ( !SED_IsValidEmail ( $_POST ['email'] ) )
            SED_Error ( "Geben Sie eine gültige Emailadresse an!" );

        // Daten ändern
        else
        {
            if ( !mysql_query ( "UPDATE benutzer SET name='$_POST[name]', email='$_POST[email]', telefon='$_POST[telefon]', telefon2='$_POST[telefon2]' WHERE id=$up_userid LIMIT 1", $globals ['db'] ) )
                SED_Error ( "Fehler beim Setzen der neuen Daten!" );
            else
            {
                // Cache leeren
                if ( $admin ['usertype'] == 's' )
                    SED_Cache::clearSpieltag ( $admin ['staffel'] );
                elseif ( $admin ['usertype'] == 't' && isset ( $_GET ['staffel'] ) )
                    SED_Cache::clearSpieltag ( $_GET ['staffel'] );

                // Fertig
                echo "<b>Die Daten wurden erfolgreich geändert!</b>";
                echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
            }
        }
    }
    
    /////////////////////////////
    // Kontaktdaten bearbeiten
    $daten = mysql_fetch_array ( mysql_query ( "SELECT name, telefon, telefon2, email FROM benutzer WHERE id=$up_userid", $globals ['db'] ), MYSQL_ASSOC );
    ?>
        <form action="<? echo SED_GenerateFormAction(); ?>" method="post" style="text-align: left;"><fieldset>
        <legend>Kontaktdaten</legend>
        <table cellspacing="0" cellpadding="2">
        <tr><td>Name: </td><td><input value="<? echo $daten ['name']; ?>" type="text" name="name" /></td></tr>
        <tr><td>Email: </td><td><input value="<? echo $daten ['email']; ?>" type="text" name="email" /></td></tr>
        <tr><td>Telefon: </td><td><input value="<? echo $daten ['telefon']; ?>" type="text" name="telefon" /></td></tr>
        <tr><td>Telefon 2: </td><td><input value="<? echo $daten ['telefon2']; ?>" type="text" name="telefon2" /></td></tr>
        <tr><td></td><td><input type="submit" class="sed_submit" name="kontakt_change" value="Ändern" /> <input type='button' class='sed_submit' value='Abbrechen' onclick="<? echo "location='?admin=desktop-$admin[userid]-$admin[session]';"; ?>" /></td></tr>
        </table>
        </fieldset></form><br />
