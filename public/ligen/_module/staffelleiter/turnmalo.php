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
  $hasGames = SED_Query('SELECT id FROM paarungen WHERE mannschaft1=? OR mannschaft2=? LIMIT 1', [$_GET['mid'], $_GET['mid']])->fetchOne();
  if ( $hasGames ) SED_Error ( "F&uuml;r die Mannschaft wurden bereits Paarungen gesetzt => Sie kann nicht gel&ouml;scht werden.", true );

  if ( isset ( $_GET ['mid'] ) )
  {
    // Mannschaft, Spieler und Anmeldungsinformationen löschen  
    if ($result = SED_TryQuery('DELETE FROM mannschaften WHERE id=? AND turnier=? LIMIT 1', [$_GET['mid'], $globals['tid']]))
      if ($result->rowCount() == 1)
        if (SED_TryQuery('DELETE FROM spieler WHERE mannschaft=?', [$_GET['mid']]))
          SED_TryQuery('DELETE FROM anmeldungsZusatzfelder WHERE mannschaft=?', [$_GET['mid']]);
        
    // Cache und Erfolgsmeldung        
    SED_Cache::clearAll ();
	  echo "Mannschaft gel&ouml;scht.";
	  echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
  }
?>
