<?
/* Verschiedene Funktionen
 * 
 * Ein paar bunt gemischte Funktionen, die immer verfügbar sind.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage main
 */

  require_once ( "../../libs/mysql-shim.php" );
  require_once ( "../../../vendor/autoload.php" );

  define('SED_REMIS', '½');

  // Gibt eine rote Fehlermeldung aus
  function SED_Error ( $msg, $exit = false, $back = false, $mail = false )
  {
    $backlink = $back ? " <a href='javascript:history.back();'>Zur&uuml;ck</a>" : "";
    echo "<b style='color: red'>$msg$backlink</b><br /><br />";
    if ( $mail )
    {
        // Fehlermeldung per Mail an Webmaster
        global $globals;
        error_log ( "Fehler im Ergebnisdienst: $msg", 1, $globals ["webmaster_mail"] );
    }
    if ( $exit )
    {
        // Damit die Seite etwas schöner aussieht
		if ( function_exists ( "SED_GUIclose" ) )
			SED_GUIclose ();
		exit;
	}
    return false; // macht Codeabkürzungen möglich
  }


  // Bringt Ergebnisse in eine anschauliche Form mit Brüchen
  function SED_Ergebnis ( $erg )
  {
    return str_replace ( ".5", SED_REMIS, $erg == "0.5" ? SED_REMIS : $erg );
  }
  
  // Setzt einen Spielernamen zusammen (Titel Nachname, Vorname)
  function SED_Spielername ( $info, $prefix = "" )
  {
	  static $femaleTitels = array(
			'WC' => 'WCM',
			'WF' => 'WFM',
			'WI' => 'WIM',
			'WG' => 'WGM'
	  );
	  
      // Ist es ein NULL-Spieler o.ä.?
      if ( strlen ( $info [$prefix."nachname"] ) < 2 )
		return "";
      
      // Weibliche FIDE-Titel verbessern
      $titel = isset($info[$prefix."titel"]) ? trim($info[$prefix."titel"]) : "";
      $titel = strtr($titel, $femaleTitels);
      
      // Hat der Spieler einen Titel?
      return trim ( $titel." ".$info[$prefix."nachname"].", ".$info[$prefix."vorname"] );
  }

  // Prüft, ob eine angegebene Email gültig ist
  function SED_IsValidEmail ( $email )
  {
    // eMail unbekannt?
    if ( strpos ( $email, "unbekannt@" ) !== false )
        return false;

    return filter_var ( $email, FILTER_VALIDATE_EMAIL );
  }

	
	// Liefert das erste Ergebnis einer Abfrage als Array
	function SED_MYSQL_Array ( $sql, $exit = false )
	{
		global $globals;
		$rsrc = mysql_query ( $sql, $globals ['db'] );
		if ( !$rsrc )
		{
			if ( $exit )
				SED_Error ( "Fehler in Abfrage! <!-- $sql -->", true );
			else 
				return false;
		}
		return mysql_fetch_array ( $rsrc, MYSQL_ASSOC );
	}
 
 
	// Liefert den erste Wert einer Abfrage, oder null.
	function SED_MYSQL_Value ( $sql, $exit = false )
	{
		global $globals;
    $row = SED_MYSQL_Array($sql, $exit);
    if (!$row || !count($row)) {
			if ( $exit )
				SED_Error ( "Fehler in Abfrage! <!-- $sql -->", true );
			else 
				return null;
		}
    return reset($row);
	}
 
  
  // Generiert den Pfad für Formulare, wenn die Zielseite gleich ist
  function SED_GenerateFormAction ( $without = false )
  {
    global $globals;
    if ( !$without ) $without = array ();
    $path = "?";
    foreach ( $_GET as $k => $v )
        if ( !in_array ( $k, $without ) )
            $path .= "$k=$v&";
    return $path;
  }


  function SED_IsNsv2020 ()
  {
      return true; // woo-hoo!
      /*
      global $prefs;
      if ( isset ( $prefs ['directory'] ) && $prefs ['directory'] == 'sjbh-1011' ) return true;
      if ( isset ( $prefs ['template'] ) && $prefs ['template'] == 'optimus' ) return true;
      if ( isset ( $prefs ['template'] ) && $prefs ['template'] != 'nsv' ) return false;
      return true;
      */
  }

