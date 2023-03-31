<?
/* Newsletter Funktion
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage frontend
 */

	require_once ( "turnier.inc.php" );
	require_once ( "mail.inc.php" );
	require_once ( "auth.inc.php" );
	require_once ( "gui.inc.php" );
	

	//////////
	// VALIDATE
	if ( isset ( $_GET ['validate'] ) )
	{
	  $defval = isset ( $_GET ['remove'] ) ? 0 : 1;
	  $prefix = isset ( $_GET ['remove'] ) ? "de" : "";

	  if ( isset ( $_GET ['validate'] ) && is_numeric ( $_GET ['validate'] ) && isset ( $_GET ['rnd'] ) )
		if ( mysql_query ( "UPDATE rundmail SET aktiv=IF(random='$_GET[rnd]',$defval,aktiv) WHERE id=$_GET[validate] LIMIT 1", $globals ['db'] ) )
		  echo "Newsletter ".$prefix."aktiviert";
		else
		  echo "Fehler beim &Auml;ndern des Datenbank-Eintrages";
	  else
		echo "Der Aktivierungslink war ung&uuml;ltig";
	}

	//////////
	// REGISTER

	else
	{
    // Disable due to spam bots.
    // TODO: Add captcha and reactivate.
    exit;
    
		// Datenüberprüfung
		if ( !SED_IsValidEmail ( $_POST ['email'] ) || !isset ( $globals ['staffeln'][$_POST ['staffel']] ) )
			echo ( "Die eingegebene eMail-Adresse ist fehlerhaft!" );
		else
		{
			// Erhält der Empfänger bereits die Rundmail?
			$rsrc = mysql_query ( "SELECT * FROM zusatzempfaenger z INNER JOIN mannschaften m ON m.id=z.mannschaft WHERE m.staffel='$_POST[staffel]' AND z.email='$_POST[email]' AND rundmail=1 LIMIT 1", $globals ['db'] );
			if ( $rsrc && mysql_num_rows ( $rsrc ) )
				echo ( "Die Rundmails werden bereits an die angegebene Adresse gesendet!" );
			else
			{
				// "Passwort" generieren
				$rnd = SED_GeneratePassword ();

				// Datensatz einfügen
				if ( mysql_query ( "INSERT INTO rundmail SET email='$_POST[email]', random='$rnd', staffel=$_POST[staffel]", $globals ['db'] ) )
				{
					// Mail versenden
					if ( mail ( "$_POST[email]", "Ergebnisdienst-Newsletter", "Lieber Schachfreund,\n\nSie haben sich über das Online-Formular auf $globals[httppath]$prefs[directory]/ für den Ergebnisdienst-Newsletter angemeldet. Um Ihre Anmeldung abzuschließen, klicken Sie bitte auf den untenstehenden Link.\n\nSollte diese Anfrage nicht von Ihnen ausgegangen sein, klicken Sie bitte nicht auf diesen Link und wenden Sie sich bitte umgehend an den Webmaster $globals[webmaster] (Email: $globals[webmaster_mail])\n\n$globals[httppath]$prefs[directory]?m=newsletter&validate=" . mysql_insert_id ( $globals ['db'] ) . "&rnd=$rnd\n\n", "From: $globals[absender_mail]\nReply-To: $globals[absender_mail]", "-f$globals[absender_mail]" ) )
						echo "An Ihre Emailadresse wurde eine Aktivierung-Email gesendet";
					else
						echo "Fehler beim Versenden der Email";
				}
				else
					echo "Der Benutzer scheint die Rundmails bereits zu erhalten. Bei Fragen wenden Sie sich an den Webmaster ($globals[webmaster_mail])!";
			}
		}
	}
	
	echo "<br /><br /><a href='?'>Zur Startseite</a>";
?>
