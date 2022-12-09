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
    mysql_query ( "UPDATE staffeln SET turnier=0 WHERE id=$admin[staffel] AND turnier=$globals[tid]", $globals ['db'] );
    mysql_query ( "UPDATE mannschaften SET staffel=0 WHERE staffel=$admin[staffel] AND turnier=$globals[tid]", $globals ['db'] );
    mysql_query ( "UPDATE paarungen SET staffel=0 WHERE staffel=$admin[staffel]", $globals ['db'] );
	SED_Cache::clearAll ();
    echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
  }

  echo "Möchten Sie die Staffel " . $globals ['staffeln'][$admin ['staffel']] . " wirklich löschen?<br /><br />";
  echo "<a href='?admin=turnstlo-$admin[userid]-$admin[session]&staffel=$admin[staffel]&aks=true'>Ja</a> ";
  echo "<a href='?admin=desktop-$admin[userid]-$admin[session]'>Nein</a> ";
?>
