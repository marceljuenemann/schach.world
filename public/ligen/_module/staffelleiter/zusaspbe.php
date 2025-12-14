<?
/* SL-Bereich: Spielberechtigungen
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
    
    
    ////////////// BMM U12 Import
 

?>

<form action='<? echo SED_GenerateFormAction(); ?>' method='get'><div><fieldset class='sed_admin_desk'><legend>Unklare Spielgenehmigungen</legend>
Bei folgenden Spielern konnte das System nicht feststellen, ob eine g&uuml;ltige Spielgenehmigung vorliegt.<br />
<br />
<table class='sed_tabelle' cellspacing='0' cellpadding='3'>
  <tr><th>Name</th><th>Mannschaft</th><th>Geburt</th><th>Geschlecht</th></tr>
  <?
    $result = SED_Query ( "SELECT s.mannschaft, s.vorname, s.nachname, s.geburt, s.geschlecht FROM spieler as s INNER JOIN mannschaften as m ON s.mannschaft=m.id WHERE (s.zps is null OR s.zps=0) AND m.turnier=? ORDER BY m.name, s.nachname", [$globals['tid']])->fetchAllAssociative();
    foreach ( $result as $row )
      echo "<td class='l'>$row[nachname], $row[vorname]</td><td>" .SED_TeamLink ( $row ['mannschaft'] ). "</td><td>$row[geburt]</td><td>$row[geschlecht]</td></tr>";
  ?>
</table>
</fieldset></div></form>
