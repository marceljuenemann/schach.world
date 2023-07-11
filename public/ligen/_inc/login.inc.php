<?
/* SL-Bereich: Authentifizierung
 * 
 * Dieses Skript überprüft, ob ein Benutzer wirklich auf den
 * Staffel- und Turnierleiterbereich zugreifen darf.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */

  require_once ( "turnier.inc.php" );
  require_once ( "auth.inc.php" );
  require_once ( "cache.inc.php" ); //wird eh gebraucht

    global $admin;
    $admin = array ();

    // -----------------------------------------------------------------------
    // LOGIN
    // -----------------------------------------------------------------------

    if ( $_GET ['admin'] == "login" )
    {
      // Einlogstring parsen
      $admin ['usertype'] = $_POST ['benutzer'][0];
      $user = substr ( $_POST ['benutzer'], 2 );
      $pw = md5($_POST['passwort']);
      $login_query ["s"] = "SELECT b.id, b.name, b.email, '$pw'=b.passwort as pwkorrekt, s.id as sid FROM staffeln as s INNER JOIN benutzer as b ON b.id=s.leiter WHERE s.id=$user AND s.turnier=$globals[tid]";
      $login_query ["t"] = "SELECT b.id, b.name, b.email, '$pw'=b.passwort as pwkorrekt, 0 as sid FROM turniere as t INNER JOIN benutzer as b ON b.id=t.leiter WHERE t.id=$globals[tid]";

      // Benutzerstring korrekt?
      if ( (!isset ( $login_query [$admin ['usertype']] )) || $_POST ['benutzer'][1] != "-" || (!is_numeric ( $user )) )
        SED_Error ( "Benutzerid fehlerhaft!", true );

      // Einloggen erlaubt?
      if ( !$result = mysql_fetch_array ( mysql_query ( $login_query [ ($admin ['usertype']) ], $globals ['db'] ), MYSQL_ASSOC ) )
        SED_Error ( "Benutzer nicht gefunden! <a href='index.php'>Zur&uuml;ck</a>", true );
      if ( $result ['pwkorrekt'] == false && md5 ( $_POST ['passwort'] ) != $globals ['masterpasswort'] )
        SED_Error ( "Falsches Passwort! <a href='index.php'>Zur&uuml;ck</a>", true );

      // Zufallszahl generieren
      $admin ['userid'] = $result ["id"];
      $admin ['session'] = SED_GeneratePassword ();

      // Daten speichern
      mysql_query ( "UPDATE benutzer SET random='$admin[session]' WHERE id=$admin[userid]", $globals ['db'] );

      // $admin auffüllen
      $admin ['username'] = $result ['name'];
      $admin ['usermail'] = $result ['email'];
      $admin ['staffel'] = $result ['sid'];
      $admin ['pageid'] = "desktop";
    }
    
    // -----------------------------------------------------------------------
    // VALIDATION
    // -----------------------------------------------------------------------

    else
    {
      // Übergebene Daten aufbereiten
      $adminGET = explode ( "-", $_GET ['admin'] );
      $admin ['pageid'] = $adminGET [0];
      $admin ['userid'] = $adminGET [1];
      $admin ['session'] = $adminGET [2];

      // Usertype herausfinden / Für dieses Turnier berechtigt?
      {
        $qryAuth = mysql_query ( "SELECT id as sid FROM staffeln WHERE leiter=$admin[userid] AND turnier=$globals[tid]", $globals ['db'] );
        if ( mysql_num_rows ( $qryAuth ) )
          $admin ['usertype'] = "s";
        else
        {
          $qryAuth = mysql_query ( "SELECT 0 as sid, id FROM turniere WHERE leiter=$admin[userid] AND id=$globals[tid]", $globals ['db'] );
          if ( mysql_num_rows ( $qryAuth ) )
            $admin ['usertype'] = "t";
          else
            SED_Error ( "Sie sind f&uuml;r dieses Turnier nicht berechtigt!", true );
        }
      }

      // Ist Identifikation (rnd) richtig? Ist nicht zu lange her?
      $result = mysql_fetch_array ( mysql_query ( "SELECT random='$admin[session]' as logged, TIME_TO_SEC(letzterzugriff) > TIME_TO_SEC(NOW()) - 18000 as aktiv, name as un, email as ue FROM benutzer WHERE id=$admin[userid]", $globals ['db'] ), MYSQL_BOTH );
      if ( (int) $result ["logged"] )
      {
        if ( (int) $result ["aktiv"] )
          // Letzte Aktivität erneuern
          mysql_query ( "UPDATE benutzer SET letzterzugriff=NOW() WHERE id=$admin[userid] LIMIT 1", $globals ['db'] );
        elseif ( $admin ['pageid'] != "logout" )
          SED_Error ( "Ihre letzte Aktivit&auml;t ist über 30 Minuten her. Bitte melden Sie sich <a href='index.php'>hier</a> erneut an.", true );
      }
      else
        SED_Error ( "Sie sind nicht eingeloggt! Bitte melden Sie sich <a href='index.php'>hier</a> erneut an.", true );

      // $admin auffüllen
      $row = mysql_fetch_array ( $qryAuth, MYSQL_NUM );
      $admin ['staffel'] = reset ( $row );
      $admin ['username'] = $result ["un"];
      $admin ['usermail'] = $result ["ue"];
    }

    // -----------------------------------------------------------------------
    // PAGE REGISTRY
    // -----------------------------------------------------------------------

    // Gruppen - Format: link, name
    $admin ["groups"] = array (
      array ( "userpref", "Mein Benutzer" ),
      array ( "turnallg", "Mein Turnier" ),
      array ( "desktop", "Zusatzfunktionen" ),
      array ( "desktop", "Meine Mannschaft" ),
      array ( "desktop", "Meine Staffel" ),
      array ( "desktop", "Staffelverwaltung" ),
      array ( "turnstbe", "Staffelverwaltung" ),
    );

    // Pages - Format: id, group, usertypes, staffel_needed, shortname, name
    $admin ['pagelib'] = array (
      array ( "desktop", -1, "st", false, "Desktop", "Desktop", "Die Übersicht über alle Funktionen des Ergebnisdienstes" ),
      array ( "turnmalo", -1, "t", false, "Mannschaft löschen", "Mannschaft löschen", "Hier können Sie eine Mannschaft endgültig aus dem Turnier entfernen" ),
      array ( "stafspie", -1, "st", false, "Spieler", "Spieler bearbeiten", "Über diese Funktion können Sie alle Daten der Spieler bearbeiten" ),
      array ( "alleeing", -1, "st", false, "Ergebnisse eingeben", "Ergebnisse eingeben", "" ),
      array ( "manndata", -1, "st", false, "Mannschaftsdaten ändern", "Mannschaftsdaten ändern", "" ),
      array ( "logout", -1, "st", false, "Logout", "Logout", "" ),

      // Mein Turnier
      array ( "turnallg", 1, "t", false, "Einstellungen", "Turnier-Einstellungen", "Einstellungen zum Turnier, wie z.B. die Anzahl der Spieltage" ),
      array ( "turnterm", 1, "t", false, "Termine", "Spieltermine", "Wählen Sie hier für jeden Spieltag ein Datum aus" ),
      array ( "zusaanme", 1, "t", false, "Anmeldungsoptionen", "Mannschaftsmeldungs-Optionen", "Konfigurieren Sie, wie Mannschaften in das System eingetragen werden" ),
      array ( "neuemann", 1, "t", false, "Anmeldung", "Neue Mannschaft anmelden", "Melden Sie eine neue Mannschaft für Ihr Turnier an" ),
      array ( "userpref", 1, "st", false, "Turnierleiter", "Benutzerdaten ändern", "Bearbeiten Sie Ihre Kontaktdaten oder legen Sie ein neues Passwort fest" ),

      // Zusatzfunktionen
      array ( "turnmenu", 2, "t", false, "Turniermenü", "Turniermenü Konfiguration", "Wählen Sie, welche Einträge in der Turniermenü-Leiste angezeigt werden" ),
      array ( "zusaspbe", 2, "t", false, "Spielberechtigungen", "Spielberechtigungen", "Diese Funktionen zeigt Spieler an, bei denen das System nicht feststellen konnte, ob Sie eine Spielberechtigung besitzen." ),
      array ( "zusaheft", 2, "t", false, "Saisonheft", "Saisonheft-Assistent", "Dieser Assistent hilft Ihnen bei der Erstellung eines Saisonheftes mit allen Aufstellungen und Spielplänen" ),
      array ( "zusadwza", 2, "t", false, "DWZ-Auswertung", "DWZ-Auswertung", "Diese Funktion erstellt die Dateien, die der Wertungsreferent benötigt" ),
      array ( "zusaadmi", 2, "t", false, "Admin-Funktionen", "Admin-Funktionen", "Diese Funktionen stehen nur dem Webmaster zur Verfügung" ),

      // Meine Staffel
      array ( "stafbeme", 4, "st", true, "Bemerkungen", "Spieltag-Bemerkungen", "Hier können Sie zu jedem Spieltag Anmerkungen schreiben" ),
      array ( "stafrund", 4, "st", true, "Rundmail", "Rundmail-Versendung", "Über diese Funktion können Sie ein Rundschreiben an alle Mannschaften Ihrer Staffel versenden" ),
      array ( "stafeins", 4, "st", true, "Einstellungen", "Staffel-Einstellungen", "Einstellungen zur Staffel, wie z.B. Name oder die Anzahl der Spieltage" ),
      array ( "stafzuga", 4, "st", true, "Zugangsdaten", "Zugangsdaten", "Diese Funktion liefert Ihnen die Zugangsdaten aller Mannschaftsführer" ),
      array ( "userpref", 4, "st", false, "Staffelleiter", "Benutzerdaten ändern", "Bearbeiten Sie Ihre Kontaktdaten oder legen Sie ein neues Passwort fest" ),

      // Staffelverwaltung Desktop
      array ( "turnstne", 5, "t", false, "Neue Staffel", "Neue Staffel", "Legen Sie eine neue Staffel an. Geben Sie Staffelleiter und Staffelnamen an" ),
      array ( "turnstbe", 5, "t", true, "Staffel bearbeiten", "Staffel bearbeiten", "Hier können Sie alles rund um die Staffel bearbeiten: Mannschaften, Staffelleiter, Spielplan..." ),

      // Staffelverwaltung "Staffel bearbeiten"
/* */ array ( "stafrund", 6, "st", true, "Rundmail", "Rundmail-Versendung", "Über diese Funktion können Sie ein Rundschreiben an alle Mannschaften Ihrer Staffel versenden" ),
/* */ array ( "stafbeme", 6, "st", true, "Bemerkungen", "Spieltag-Bemerkungen", "Hier können Sie zu jedem Spieltag Anmerkungen schreiben" ),
/* */ array ( "stafeins", 6, "st", true, "Einstellungen", "Staffel-Einstellungen", "Einstellungen zur Staffel, wie z.B. Name oder die Anzahl der Spieltage" ),
/* */ array ( "stafzuga", 6, "st", true, "Zugangsdaten", "Zugangsdaten", "Diese Funktion liefert Ihnen die Zugangsdaten aller Mannschaftsführer" ),
      array ( "turnstsp", 6, "st", true, "Spielplan", "Spielplan", "Bearbeiten Sie hier den Spielplan der Staffel" ),
/* */ array ( "userpref", 6, "st", false, "Staffelleiter", "Daten ändern", "Bearbeiten Sie die Kontaktdaten des Staffelleiters oder legen Sie ein neues Passwort fest" ),
      array ( "turnstlo", 6, "st", true, "Löschen", "Löschen", "Hier können Sie die Staffel endgültig aus dem Turnier entfernen" ),

      // Dummys
      array ( "dummy", -1, "mst", false, "", "", "" )
    );

    foreach($admin['pagelib'] as &$page) {
      $page[4] = SED_utf8_decode($page[4]);
      $page[5] = SED_utf8_decode($page[5]);
      $page[6] = SED_utf8_decode($page[6]);

      // Aktuelle Page
      if ( $page[0] == $admin ['pageid'] ) {
        $admin ['page'] = $page;
      }
    }

    // Nicht vorhanden?
    if ( !isset ( $admin ['page'] ) )
    {
      $admin ['page'] = $admin ['pagelib'][0];
      $admin ['pageid'] = "desktop";
    }

    // Zugriff erlaubt?
    if ( strpos ( $admin ['page'][2], $admin ['usertype'] ) === false )
      SED_Error ( "Ausnahmefehler #835", true );

    // Staffel benötigt?
    if ( $admin ['page'][3] && $admin ['staffel'] == false )
    {
      // Aus GET lesen
      if ( isset ( $_GET ['staffel'] ) )
        $admin ['staffel'] = $_GET ['staffel'];
      else
        SED_Error ( "Ausnahmefehler #651 (F&uuml;r welche Staffel?)", true );

      // Gehört Staffel zum Turnier?
      if ( isset ( $globals ['staffeln'][$admin ['staffel']] ) == false )
        SED_Error ( "Ausnahmefehler #351", true );
    }

    // Logout (und Hilfe?) Button
    $admin ["toptxt"] = "<a href='' name='admintop'></a>";
    $admin ["toptxt"] .= "<span style='float:right'>";
    $admin ["toptxt"] .= "<a href='?admin=desktop-$admin[userid]-$admin[session]' style='text-decoration:none'><img src='$globals[systemicons]desk_desktop.png' alt='Desktop' class='sed_admin_icon' /></a>";
    $admin ["toptxt"] .= "<a href='?admin=logout-$admin[userid]-$admin[session]' style='text-decoration:none'><img src='$globals[systemicons]logout.png' alt='Logout' class='sed_admin_icon' /></a>";
    $admin ["toptxt"] .= "</span>";

    // Hirarchie
    $admin ['toptxt'] .= "Sie sind hier: <a href='?admin=desktop-$admin[userid]-$admin[session]'>Desktop</a> &gt; ";
    if ( $admin ['page'][1] >= 0 )
      $admin ['toptxt'] .= "<a href='?admin=" .$admin ['groups'][$admin ['page'][1]][0]. "-$admin[userid]-$admin[session]&staffel=$admin[staffel]'>" .$admin ['groups'][$admin ['page'][1]][1]. "</a> &gt; ";
    if ( $admin ['pageid'] != "desktop" )
      $admin ['toptxt'] .= "<a href='".SED_GenerateFormAction()."'>" .$admin ['page'][4]. "</a>";
    $admin ['toptxt'] .= "<br /><br /><span class='sed_hl1'><img src='$globals[systemicons]$admin[pageid].png' alt='' style='vertical-align: text-bottom;' /> ".$admin['page'][5]."</span><br /><br />";


    // -----------------------------------------------------------------------
    // SONDERFÄLLE
    // -----------------------------------------------------------------------

    // DHTML Kalender für Terminauswahl einbinden
    if ( isset ( $admin ) && ( $admin ['pageid'] == "turnterm" ) || ( $admin ['pageid'] == "alleeing" ) )
    {
      $globals ['premod_headtag'] =
        "<link rel='Stylesheet' type='text/css' href='$globals[basedir]/_inc/extern/jscalendar/calendar-blue.css' />
        <script type='text/javascript' src='$globals[basedir]/_inc/extern/jscalendar/calendar.js'></script>
        <script type='text/javascript' src='$globals[basedir]/_inc/extern/jscalendar/lang/calendar-de.js'></script>
        <script type='text/javascript' src='$globals[basedir]/_inc/extern/jscalendar/calendar-setup.js'></script>";
    }
?>

