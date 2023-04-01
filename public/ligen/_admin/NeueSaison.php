<?
/* Neues Turnier anlegen
 * 
 * Zum Anlegen eines neuen Turnieres.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage admin
 */

    require_once ( "admin.inc.php" );
    if ( $_GET ["auth"] != substr ( $globals ["masterpasswort"], 5, 5 ) )
        SED_Error ( "Keine Berechtigung!", true );

	// hole turniere
	$jahr = ((int) date("Y"))-1;
	$turniere = mysql_query ( "SELECT * FROM turniere WHERE startjahr=$jahr", $globals['db'] );
	echo "processing $jahr<br>";
	while ($turnier = mysql_fetch_array($turniere, MYSQL_ASSOC)){
		echo "processing $turnier[name]<br>";
		
		// turnierleiter
		$turnier['leiter'] = CopyUser($turnier["leiter"]);
		
		// namen / jahreszahlen
		$turnier['name'] = ProcessName($turnier['name'], $jahr);
		$turnier['startjahr'] = $jahr+1;
		$turnier['directory'] = ProcessName($turnier['directory'], $jahr);
		
		// insert
		$newTID = InsertRow("turniere", $turnier);
		
		// staffeln
		$staffeln = mysql_query("SELECT * FROM staffeln WHERE turnier=$turnier[id]");
		while ($staffel = mysql_fetch_array($staffeln, MYSQL_ASSOC)){
			$staffel['leiter'] = CopyUser($staffel['leiter']);
			$staffel['turnier'] = $newTID;
			InsertRow("staffeln", $staffel);
		}
		
		echo "$turnier[directory] angelegt<br>";
	}
	
	function ProcessName ($name, $jahr){
		return
			str_replace ( substr($jahr, 2, 2) . substr($jahr+1, 2, 2), substr($jahr+1, 2, 2) . substr($jahr+2, 2, 2),
			str_replace ( $jahr, $jahr+1,
			str_replace ( $jahr+1, $jahr+2,
			str_replace ( "/".substr($jahr+1, 2, 2), "/".substr($jahr+2, 2, 2),
				$name
		))));
	}
	
	function CopyUser ($id){
		$user = mysql_fetch_array ( mysql_query ("SELECT * FROM benutzer WHERE id=$id"), MYSQL_ASSOC);
		return InsertRow ("benutzer", $user);
	}
	
	function InsertRow ($table, $data){
		$query = "INSERT INTO $table SET ";
		$fields = array ();
		foreach ($data as $key=>$value){
			if ($key == "id") continue;
			if ($value === NULL){
				$fields [] = "$key = null";
			} else {
				$fields [] = "$key = '$value'";
			}
		}
		$query .= implode (", ", $fields);
		mysql_query($query);
    return mysql_insert_id();
	}
	
?>
