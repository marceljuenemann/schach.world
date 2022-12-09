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
    /*
$teams = array (
2098,
2099,
2051,
2012,
2076,
2095,
2126,
2086,
2103,
2214,
2062 );


$data = 
"!797A87A89!BB65B!423143142
!99BBA!127187182!335465463
!A596A96A5!473843847!BB21B
!A291A91A2!33BB4!757687685
!9394A94A3!652612615!87BB8";

$round = 2;
$staffel = 583;



   // Zeilen auslesen und in Hexadezimal umwandeln
    $zeilen = explode ("\n", str_replace("\r", "", $data));

echo $data;
print_r ($zeilen);

    // Die Spieltage / Zeilen durchgehen
    foreach ($zeilen as $row)
    {
		echo $row;
		
		// Die einzelnen Zeichen durchgehen
		$ausrichter = "NULL";
		$first = 0;
        for ( $j = 0; $j < strlen($row); ++$j ){
			if ($row[$j]=="!"){
				$j++;
				$ausrichter = "'".$teams[hexdec($row[$j])-1]."'";
			} else if ($first == 0) {
				$first = $teams[hexdec($row[$j])-1];
			} else {
				$second = $teams[hexdec($row[$j])-1];
				$sql = "INSERT INTO paarungen SET staffel=$staffel, runde=$round, mannschaft1=$first, mannschaft2=$second, ausrichter=$ausrichter";
				$first = 0;
				echo  $sql."<br>";
				mysql_query($sql, $globals['db']);
			}
        }
        
        $round++;
    }

////////////////// END

*/

?>

<form action='<? echo SED_GenerateFormAction(); ?>' method='get'><div><fieldset class='sed_admin_desk'><legend>Unklare Spielgenehmigungen</legend>
Bei folgenden Spielern konnte das System nicht feststellen, ob eine gültige Spielgenehmigung vorliegt.<br />
<br />
<table class='sed_tabelle' cellspacing='0' cellpadding='3'>
  <tr><th>Name</th><th>Mannschaft</th><th>Geburt</th><th>Geschlecht</th></tr>
  <?
    $result = mysql_query ( "SELECT s.mannschaft, s.vorname, s.nachname, s.geburt, s.geschlecht FROM spieler as s INNER JOIN mannschaften as m ON s.mannschaft=m.id WHERE (s.zps is null OR s.zps=0) AND m.turnier=$globals[tid] ORDER BY m.name, s.nachname", $globals ['db'] );
    if ( mysql_num_rows ( $result ) > 0 )
    {
      while ( $row = mysql_fetch_array ( $result, MYSQL_ASSOC ) )
        echo "<td class='l'>$row[nachname], $row[vorname]</td><td>" .SED_TeamLink ( $row ['mannschaft'] ). "</td><td>$row[geburt]</td><td>$row[geschlecht]</td></tr>";
    }
  ?>
</table>
</fieldset></div></form>
