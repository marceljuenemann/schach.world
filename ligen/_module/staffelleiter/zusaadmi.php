<?
/* SL-Bereich: Administrator-Bereich
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





	/*
	sooo dirty...
	
$staffel = 610;		
		$data = array (

array(
"zps" => "70604",
"mnr" => "1",
"mf_name" => "Jascha Bonacic",
"mf_telefon" => "05476 8019147",
"mf_telefon2" => "0163 6316792",
"mf_email" => "jascha.bonacic@osnanet.de",
"so_name" => "Altes Pfarrhaus und Martinusheim",
"so_strasse" => "Martinistraße 2 – 4",
"so_plz" => "49170",
"so_stadt" => "Hagen a.T.W",
array(
"Lubbe, Nikolas",
"Wöllermann, Jan",
"Grafe, Frank",
"Wierum, Colin",
"Bonacic, Nenad",
"Runde, Marco",
"Kemper, Christoph",
"Runde, Sven",
"Karsten Bertram",
"Antonius Schwefer",
"Heiner Rieping",
"Nils Orschulik",
"Jascha Bonacic",
"Daniel Tietje",
"Kilian Böhning",
"Kristin Rethmann",
"Lukas Beinke",
"Balint Balazs",


// end of dirtyness
)
),
);



		foreach ( $data as $team ){
			$teamdata = array ("staffel" =>$staffel, "turnier"=>$globals['tid']);
			
			
			foreach ( $team as $k => $v ){
				if ( is_array ( $v ) ){
					$name = "noname";
					$rsrc = mysql_query ( "SELECT name FROM mannschaften WHERE zps='$teamdata[zps]' ORDER BY id DESC", $globals ['db'] );

					// Hat das nicht geklappt?
					if ( !$rsrc || !mysql_num_rows ( $rsrc ) )
						;
					
					// Sonst Ausgabe
					$name = ( reset ( mysql_fetch_array ( $rsrc ) ) );
					$teamdata['name'] = $name;
				
					
					$sql =  "INSERT INTO mannschaften SET ";
					foreach ( $teamdata as $tk => $tv ){
						$sql .= "$tk='$tv', ";
					}
					$sql = substr ( $sql, 0, strlen($sql)-2 );
					mysql_query ( $sql, $globals ['db'] );
					$id = mysql_insert_id();

					foreach ( $v as $s ){
						require_once("spieler.class.php");
						$spieler = new SED_Spieler ();
						$spieler->setName ( $s );
						$spieler->set ( "mannschaft", $id );
						$spieler->autofill($teamdata['zps']);
						echo $spieler->getName ()."<br>";
						$spieler->saveToDB ();
					}
		
		
				} else {
					$teamdata [$k] = $v;
				}
			}
		
		}
		
		
	
	
  
  
  	
	
	
	*/
	
	
	
	
	
	
	
  // Zugriffskontrolle
  if ( isset ( $_POST ['passwort'] ) == false || md5 ( $_POST ['passwort'] ) != $globals ['masterpasswort'] )
  {
    SED_Error ( "Diese Funktionen stehen nur dem Webmaster zur Verfügung!", false );
    echo "<form action='".SED_GenerateFormAction()."' method='post'><div>";
    echo "<input type='password' style='margin-bottom: 15px; margin-top: 5px;' name='passwort' size='10' /><br />";
    echo "<input type='submit' class='sed_submit' value='Absenden' /></div></form>";
    exit;
  }

  
  ////////////////////////
  // Turniereinstellungen
  ////////////////////////

    $frmMF = array
    (
        // ID, Name, Beschreibung, Textfeldbreite, Max. Länge, Pflichtfeld
        array ( "directory", "Verzeichnisname", "", 20, 50, true ),
        array ( "template", "Template", "", 20, 50, true )
    );

      // Speichern
      if ( isset ( $_POST ['savebutton1'] ) )
      {
        // Datenprüfung
        $errors = array ();
        for ( $i = 0; $i < count ( $frmMF ); ++$i )
        {
          // Ist zu lang?
          if ( $frmMF [$i][4] < strlen ( $_POST ['frmManager_' . $frmMF [$i][0]] ) )
            $errors [] = "Der Text in Feld " . $frmMF [$i][1] . " ist zu lang!";

          // Ist Pflicht und nicht gesetzt?
          if ( $frmMF [$i][5] && strlen ( $_POST ['frmManager_' . $frmMF [$i][0]] ) == 0 )
            $errors [] = "Das Feld " . $frmMF [$i][1] . " ist ein Pflichtfeld!";
        }

        // Fehlerausgabe
        if ( count ( $errors ) > 0 )
        {
          foreach ( $errors as $error )
            echo "<span style='color: red; font-weight: bold'>$error</span><br />";
          echo "<br />";
        }

        if ( !count ( $errors ) )
        {
          // Query generieren
          $query = "";
          for ( $i = 0; $i < count ( $frmMF ); ++$i )
          {
            $tmp = ( is_numeric ( $_POST ["frmManager_" . $frmMF [$i][0]] ) ? "" : "'" );
            $query .= $frmMF [$i][0] . "=" . $tmp . $_POST ["frmManager_" . $frmMF [$i][0]] . $tmp . ", ";
          }

          // In MySQL Speichern
          mysql_query ( $x = "UPDATE turniere SET $query anmAktiv=$prefs[anmAktiv] WHERE id=$globals[tid] LIMIT 1", $globals ['db'] );

          // Cache leeren
          // ... nicht nötig

          // Erfolgsmeldung
          echo "<b>Die Daten wurden erfolgreich gespeichert</b>";
          exit;
        }
      }

      // Felder ausgeben
      // ID, Name, Beschreibung, Textfeldbreite, Max. Länge, Pflichtfeld
      echo "<span class='sed_hl2'>Turniereinstellungen</span><br /><br />";
      echo "<form action='".SED_GenerateFormAction()."' method='post'><div>";
      for ( $i = 0; $i < count ( $frmMF ); ++$i )
      {
        echo "<span style='font-weight: bold'>" . $frmMF [$i][1] . " " . ( $frmMF [$i][5] ? "*" : "" ) . "</span><br />";
        $value = $prefs [ $frmMF [$i][0] ];
        echo "<input type='text' style='margin-bottom: 15px; margin-top: 5px;' name='frmManager_" . $frmMF [$i][0] . "' value='$value' size='" . $frmMF [$i][3] . "' maxlength='" . $frmMF [$i][4] . "' /><br />";
      }

      echo "
      <input type='hidden' name='passwort' value='$_POST[passwort]' />
      <input type='submit' name='savebutton1' class='sed_submit' value='Speichern' />
      </div></form>";


  ////////////////////////
  // Passwörter versenden
  ////////////////////////

      // Passwörter
      if (isset ($_POST['buttonsave2']) && isset ($_POST['sure']) && $_POST['sure'])
      {
        // Parsen
        $id = substr ( $_POST ['person'], 1 );
        $type = substr ( $_POST ['person'], 0, 1 );

        // Abfragen
        $queries = array (
          "b" => "SELECT b.id, b.email, s.id as id2 FROM staffeln as s INNER JOIN benutzer as b ON b.id=s.leiter WHERE s.turnier=$globals[tid]"
        );

        // Abfrage
        if ( !isset ( $queries [$type] ) )
          SED_Error ( "Ungültiges Format!", true );
        $rsrc = mysql_query ( $queries [$type], $globals ['db'] );

        // Durchgehen
        if ( $rsrc ) while ( $user = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) )
        {
          // Namen generieren
          if ( $type == "a" || $type == "m" )
            $name = $globals ['teams'][$user ['id2']];
          else
            $name = $globals ['staffeln'][$user ['id2']];

          // Passwort ändern
          $password = "";
          for ( $i = 0; $i < 10; ++$i )
              $password .= base_convert ( rand ( 1, 30 ), 10, 32 );
          if ( !mysql_query ( "UPDATE benutzer SET passwort=MD5('$password') WHERE id=$user[id] LIMIT 1", $globals ['db'] ) )
            SED_Error ( "Beim Setzen des neuen Passworts ($user[id]) ist etwas schief gelaufen!", true );

          // Mail versenden
          if ( !mail ( $user ['email'], "$prefs[name] / Zugangsdaten", "Lieber Schachfreund,\n\nmit dieser Email erhalten Sie Ihre Zugangsdaten zur Turnierhomepage der $prefs[name]:\n\nBenutzer: $name\nPasswort: $password\n\nMit diesen Daten können Sie sich auf $globals[httppath]$prefs[directory]/ einloggen. Ihr Passwort können Sie in den Einstellungen ändern.\n\n", "From: $globals[absender_mail]\nReply-To: $globals[absender_mail]", "-f$globals[absender_mail]" ) )
            SED_Error ( "Beim Verschicken der Email ist ein Fehler aufgetreten ($user[id])" );
        }

        // Erfolgsmeldung
        echo "<b>Die Daten wurden erfolgreich versendet!</b>";
        echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
      }
?>


<br /><br />
<span class='sed_hl2'>Passwörter versenden</span><br /><br />
<form action='<? echo SED_GenerateFormAction(); ?>' method='post'><div>

      Empfänger: <select name='person'>
      <option value='b'>Alle Staffelleiter</option>
      </select><br /><br />
      <input type='hidden' name='passwort' value='<? echo $_POST ['passwort']; ?>' />
      <input type='checkbox' name='sure' value='1' /> Ich bin mir bewusst, dass durch Absenden des Formulars ggf. viele Emails an verschiedene Personen versendet werden. <br /><br />
      <input type="submit" class="sed_submit" name="buttonsave2" value="Absenden" /> <input type='button' class='sed_submit' value='Abbrechen' onclick="<? echo "location='?admin=desktop-$admin[userid]-$admin[session]';"; ?>" />

</div></form>

