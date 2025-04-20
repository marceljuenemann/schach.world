<?
/* SL-Bereich: Staffel löschen
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

  if ( isset ( $_GET ['aks'] ) )
  {
    SED_TryQuery("UPDATE staffeln SET turnier=0 WHERE id=? AND turnier=?", [$admin['staffel'], $globals['tid']]);
    SED_TryQuery("UPDATE mannschaften SET staffel=0 WHERE staffel=? AND turnier=?", [$admin['staffel'], $globals['tid']]);
    SED_TryQuery("UPDATE paarungen SET staffel=0 WHERE staffel=?", [$admin['staffel']]);
	SED_Cache::clearAll ();
    echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
  }

  echo "M&ouml;chten Sie die Staffel " . $globals ['staffeln'][$admin ['staffel']] . " wirklich l&ouml;schen?<br /><br />";
  echo "<a href='?admin=turnstlo-$admin[userid]-$admin[session]&staffel=$admin[staffel]&aks=true'>Ja</a> ";
  echo "<a href='?admin=desktop-$admin[userid]-$admin[session]'>Nein</a> ";
?>
