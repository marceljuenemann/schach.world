<?
/* SL-Bereich: Mannschaft löschen
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */

    require_once ( "login.inc.php");

    // Hat die Mannschaft schon Spiele?
    $hasGames = SED_MYSQL_Array ( "SELECT id FROM paarungen WHERE mannschaft1='$_GET[mid]' OR mannschaft2='$_GET[mid]' LIMIT 1", false );
    if ( $hasGames ) SED_Error ( "F&uuml;r die Mannschaft wurden bereits Paarungen gesetzt => Sie kann nicht gel&ouml;scht werden.", true );

    if ( isset ( $_GET ['mid'] ) )
    {
    // Mannschaft, Spieler und Anmeldungsinformationen löschen  
    if ( mysql_query ( "DELETE FROM mannschaften WHERE id=$_GET[mid] AND turnier=$globals[tid] LIMIT 1", $globals ['db'] ) )
      if ( mysql_affected_rows ( $globals ['db'] ) == 1 )
        if ( mysql_query ( "DELETE FROM spieler WHERE mannschaft=$_GET[mid]", $globals ['db'] ) )
          mysql_query ( "DELETE FROM anmeldungsZusatzfelder WHERE mannschaft=$_GET[mid]", $globals ['db'] );
        
    // Cache und Erfolgsmeldung        
    SED_Cache::clearAll ();
	echo "Mannschaft gel&ouml;scht.";
	echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
  }
?>
