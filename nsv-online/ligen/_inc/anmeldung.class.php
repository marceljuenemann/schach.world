<?
/* Mannschaftsmeldung: Anmelde-Objekt
 * 
 * In dieser Datei wird die Klasse SED_Anmeldung zur Verfügung
 * gestellt, mit der alle Informationen zu einer Mannschaftsmeldung
 * verarbeitet werden können.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage anmeldung
 */

    require_once ( "turnier.inc.php" );
    require_once ( "spieler.class.php" );
    require_once ( "auth.inc.php" );
  
class SED_Anmeldung {
    var $data = array ();
    var $zusatzempfaenger = array ();
    private $players = array (); // some id => player object
    var $fields = array ( "zps", "name", "mnr", "staffel", "mf_name", "mf_email", "mf_telefon2", "mf_telefon", "so_name", "so_strasse", "so_plz", "so_stadt", "so_telefon" );

    static function getPlayerlessTeams (){
        global $globals;
        return mysql_query ( "SELECT m.* FROM mannschaften m WHERE turnier=$globals[tid] AND NOT EXISTS (SELECT id FROM spieler WHERE mannschaft=m.id)", $globals['db'] ); 
    }
    
    function __construct ( $mid = 0 ){
        // Existiert die Mannschaft bereits in der Datenbank?
        $rsrc = $this->getPlayerlessTeams ();
        while ( $team = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) )
            if ( $mid==$team['id'] ){
                $this->data = $team;
                break;
            }
    }

    // z.B. return array ( "7010170102", "70101", "70102" )
    function getZpsList ( $prefix = "", $suffix = "" ){
        if ( strlen ( $this->data ['zps'] ) == 10 )
            return array ( $prefix.$this->data['zps'].$suffix, 
                $prefix.substr($this->data['zps'],0,5).$suffix,
                $prefix.substr($this->data['zps'],5,5).$suffix );
        return array ( $prefix.$this->data['zps'].$suffix );
    }

    // Liefert einen möglichst guten Namen zu ZPS und MNR
    function getName (){
        // Ist ein Name gesetzt?
        if ( isset ( $this->data ['name'] ) )
            return $this->data ['mnr'] == 1 ? $this->data['name'] : $this->data['name'] . " " . $this->data['mnr'];

        // Ist gar keine ZPS gesetzt?
        if ( $this->data['zps'] == "" ) return "";

        // Guten Namen aus der Datenbank holen
        foreach ( $this->getZpsList () as $zps ){
            if ( $name = SED_MYSQL_Array ( "SELECT name FROM mannschaften WHERE zps='$zps' ORDER BY id DESC LIMIT 1" ) )
                return reset ( $name );
        }
        
        // Einfach den Vereinsnamen abschneiden
        foreach ( $this->getZpsList () as $zps ){
            if ( $name = SED_MYSQL_Array ( "SELECT Vereinname FROM dwz_vereine WHERE ZPS='$zps'" ) )
                return reset ( $name );
        }
        
        // Ansonsten ist irgendwas schief gelaufen...
        return "";
    }
    
    // Liefert ein möglichst gutes Spiellokal
    function getSO (){
        // Spiellokal bereits gesetzt?
        if ( isset ( $this->data['so_name'] ) && $this->data['so_name'] ){
            return $this->data;
        }
        
        // Letztes angegebenes Spiellokal
        foreach ( $this->getZpsList () as $zps ){
            if ( $so = SED_MYSQL_Array ( "SELECT * FROM mannschaften WHERE zps='$zps' AND LENGTH(so_plz)=5 ORDER BY id DESC" ) )
                return $so;
        }
        
        // Leer
        return array ( "so_name"=>"", "so_strasse"=>"", "so_plz"=>"", "so_stadt"=>"", "so_telefon"=>"" );
    }

    // Liefert einen möglichst guten Mannschaftsführer
    function getMF (){
        global $prefs;
        
        // MF bereits gesetzt?
        if ( isset ( $this->data['mf_name'] ) && $this->data['mf_name'] ){
            return $this->data;
        }
        
        // Versuch über letztes Jahr
        if ( $mf = SED_MYSQL_Array ( "SELECT * FROM turniere t INNER JOIN mannschaften m ON m.turnier=t.id AND m.zps='".$this->data['zps']."' AND m.mnr='".$this->data['mnr']."' WHERE t.organisation='$prefs[organisation]' AND t.startjahr=".((int) $prefs['startjahr'])."-1 LIMIT 1" ) )
            return $mf;
        
        // Letzter angegebener Mannschaftsführer
        foreach ( $this->getZpsList () as $zps ){
            if ( $mf = SED_MYSQL_Array ( "SELECT * FROM mannschaften WHERE zps='$zps' AND LENGTH(so_plz)=5 ORDER BY id DESC" ) )
                return $mf;
        }
        
        // Leer
        return array ( "mf_name"=>"", "mf_email"=>"", "mf_telefon2"=>"", "mf_telefon"=>"" );
    }
    
    // Setzt Felder...
    function setFields ( $data ){
        $fields = array_merge ( $this->fields, array_keys ( $this->getZusatzFelder () ) );
        foreach ( $fields as $field )
            if ( isset ( $data [$field] ) )
            {
                // Default mnr ist 1
                if ( $field == "mnr" && !is_numeric ( $data [$field] ) )
                    $data [$field] = 1;

                // ZPS muss 0, 5 oder 10 Stellen haben
                if ( $field == "zps" && strlen ($data[$field]) % 5 != 0 )
                    SED_Error ( "Da ist eine falsche ZPS ins System geraten!", true );

                $this->data [$field] = $data [$field];
            }
    }
    
    // Liefert ein Feld
    function get ( $field ){
        if ( isset ( $this->data [$field] ) )
            return $this->data [$field];
        if ( $field == "staffel" ) 
            return 0;
        if ( $field == "id ")
            return 0;
        return "";
    }

    function getZusatzEmpfaenger (){
        return $this->zusatzempfaenger;
    }

    // Erwartet Eingabe aus Textfeld (einer pro Zeile)
    function setZusatzEmpfaenger ( $text ){
        $this->zusatzempfaenger = explode ( "\n", str_replace ( "\r", "\n", $text ) );
    }

    // Überpüft die Sinnhaftigkeit der Daten
    function checkData (){
        return in_array ( strlen ( $this->get ( "zps" ) ), array ( 0, 5, 10 ) );
    }

    // Liefert eine Liste der Zusatzfelder
    function getZusatzFelder (){
        global $prefs;
        $felder = explode ( "\n", str_replace ( "\r", "\n", $prefs ['anmZusatzfelder'] ) );
        $result = array ();
        foreach ( $felder as $feld )
        {
            // Verarbeitung der Einstellungen
            if ( strlen ( $feld ) < 3 ) continue;
            $tmp = explode ( "#", $feld );
            if ( count ( $tmp ) < 2 ) $tmp [1] = 40;
            $result [base64_encode($tmp[0])] = $tmp;
        }
        return $result;
    }
    
    // Liefert Standartwerte für die Zusatzfelder
    function getZusatzFelderDefault (){
        global $globals; global $prefs;
        $rsrc = mysql_query ( "SELECT a.feldname, a.inhalt 
            FROM mannschaften m
            INNER JOIN turniere t ON t.id=m.turnier
            INNER JOIN anmeldungZusatzfelder a ON a.mannschaft=m.id
            WHERE m.zps='".$this->get("zps")."' AND m.mnr='".$this->get("mnr")."' 
                AND t.organisation='$prefs[organisation]' AND t.startjahr='".(int)$prefs['startjahr']."'-1", $globals ['db'] );
        $default = array ();
        if ( $rsrc ) while ( $entry = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) )
            $default [$entry ["feldname"]] = $entry ["inhalt"];
        return $default;
    }

    function getPlayerList (){
        return $this->players;
    }

    // Liefert eine DWZ-Liste des Vereins
    function getDwzList (){
        global $prefs; global $globals;

        // DWZ-Abfrage vorbereiten
        require_once ( "dwzdb.inc.php" );
        $db = new SED_DWZ_Request ();
        $db->addConditionList ( $this->getZpsList ( "ZPS='", "'" ) );
        if ( $prefs ["anmGeburt"] ) $db->setGeburt ( $prefs ["anmGeburt"] );
        if ( $prefs ["anmGeschlecht"] ) $db->setGeschlecht ( $prefs ["anmGeschlecht"] );

        // Abfrage ausführen
        return $db->getPlayerObjectList ( 999, SED_DWZ_Request::SORT_DWZ );
    }
    
    function addPlayer ( $player ){
        $this->players [] = $player;
    }
    
    function saveToDB (){
        require_once ( "cache.inc.php" );
        global $globals;
        $mid = $this->get("id");

        // Zumindest ein bisschen überprüfen
        if ( !strlen ( $this->get("name") ) )
            SED_Error ( "Einen Mannschaftsnamen sollte man schon angeben!", true );

        // Anfrage (SET) zusammensetzen
        $query = "SET turnier=$globals[tid]";
        foreach ( $this->fields as $field )
            if ( $field == "zps" && $this->get($field)=="" )
                $query .= ", $field=NULL";
            else
                $query .= ", $field='".$this->get($field)."'";

        // Soll eine Mannschaft ohne Spieler bearbeitet werden?
        if ( $mid ){
            $pl = SED_Anmeldung::getPlayerlessTeams ();
            while ( $team = mysql_fetch_array ( $pl ) )
                if ( $team ["id"] == $mid )
                    $query = "UPDATE mannschaften $query WHERE id=$mid LIMIT 1"; 
        } else
            $query = "INSERT INTO mannschaften $query";
            
        // Query ausführen
        if ( !mysql_query ( $query, $globals ['db'] ) )
            return SED_Error ( "Mannschaftsanmeldung fehlgeschlagen <!-- $query -->", false, false, true );
        $this->data ["id"] = $mid ? $mid : mysql_insert_id ();

        // Spieler einfügen
        foreach ( $this->players as $spieler ){
            $spieler->set ( "mannschaft", $this->data ["id"] );
            try {
                $spieler->saveToDB ();
            } catch ( Exception $e ) {
                return SED_Error ( "Spieleranmeldung fehlgeschlagen: ".$e->getMessage(), false, false, true );
            }
        }

        // Zusatzempfänger einfügen
        foreach ( $this->getZusatzEmpfaenger () as $mail )
        {
            if ( SED_IsValidEmail ( $mail ) )
            {
                if ( !mysql_query ( $query = "INSERT INTO zusatzempfaenger SET mannschaft=".$this->data["id"].", email='$mail'", $globals ['db'] ) )
                    return SED_Error ( "Zusatzempfänger fehlgeschlagen <!-- $query -->", false, false, true );
            }
        }
            
        // Zusatzfelder verarbeiten
        foreach ( $this->getZusatzFelder () as $id=>$feld )
        {
            if ( isset ( $this->data [$id] ) )
                if ( $content = $this->data [$id] )
                    if ( !mysql_query ( $query = "INSERT INTO anmeldungZusatzfelder SET mannschaft=".$this->data["id"].", feldname='".base64_decode($id)."', inhalt='$content'", $globals ['db'] ) )
                        return SED_Error ( "Zusatzfeld fehlgeschlagen <!-- $query -->", false, false, true );
        }
    
        // Cache löschen
        require_once ( "cache.inc.php" );
        SED_Cache::clearAll ();
        return $this->data ["id"];
    }

    // Liefert alle Daten 
    function getJSON (){
        return serialize ( $this );
    }
    
    // parst alle Daten
    function parseJSON ( $json ){
        $obj = unserialize ( $json );
        $this->data = $obj->data;
        $this->zusatzempfaenger = $obj->zusatzempfaenger;
    }
        
}
?>
