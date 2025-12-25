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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result as DBALResult;
use Nsv\League\Core\Encoding;
use Nsv\League\Core\LegacySystem;
use Nsv\League\Core\Result;

  define('SED_REMIS', Result::DRAW());

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
      if ( strlen ( $info [$prefix."nachname"] ?: '' ) < 2 )
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

  /**
   * Bridge into the new Symfony application.
   */
  function SED_Bridge(): LegacySystem {
    global $globals;
    return $globals['bridge'];
  }
  
  /**
   * Returns the PDO connection object.
   */
  function SED_Connection(): Connection {
    $connection = SED_Bridge()->leagueEntityManager->getConnection();
    return $connection;
  }

  /**
   * Prepares and executes the given query.
   * 
   * @param sql the query to prepare
   * @param params the parameters to fill in
   * @see Connection#executeQuery
   * @return DBALResult
   * @throws Exception
   */
  function SED_Query(string $sql, array $params = []): DBALResult {
    return SED_Connection()->executeQuery($sql, $params);
  }

  /**
   * Prepares and executes the given query and returns the result or false if an exception occurred.
   * 
   * This is intended to be an easy replacement from mysql_query().
   */
  function SED_TryQuery(string $sql, array $params = []): DBALResult|false {
    try {
      return SED_Query($sql, $params);
    } catch (\Exception $e) {
      SED_Bridge()->leagueLogger->error("SED_TryQuery failed: {$e->getMessage()}", ['sql' => $sql, 'params' => $params]);
      return false;
    }
  }

  /**
   * Returns the first row of a query and throws an exception if no row was found.
   * 
   * Use SED_Query(...)->fetchAssociative() if you don't want an exception to be thrown.
   */
  function SED_Row(string $sql, array $params = []): array {
    $data = SED_Query($sql, $params)->fetchAssociative();
    if ($data === false) {
      throw new \Exception("No results for query {$sql}");
    }
    return $data;
  }

  /**
   * Returns the first value of a query and throws an exception if no value was found.
   * 
   * Use SED_Query(...)->fetchOne() if you don't want an exception to be thrown.
   */
  function SED_Value(string $sql, array $params = []): mixed {
    $row = SED_Row($sql, $params);
    return current($row);
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
  
  /**
   * Converts from UTF-8 to the application charset.
   */
  function SED_utf8_decode($str) {
    return Encoding::utf8_decode($str);
  }
