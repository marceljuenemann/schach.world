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
        return SED_Query("SELECT m.* FROM mannschaften m WHERE turnier=? AND NOT EXISTS (SELECT id FROM spieler WHERE mannschaft=m.id)", [$globals['tid']])->fetchAllAssociative(); 
    }
    
    function __construct ( $mid = 0 ){
        // Existiert die Mannschaft bereits in der Datenbank?
        foreach($this->getPlayerlessTeams() as $team) {
            if ( $mid==$team['id'] ){
                $this->data = $team;
                break;
            }
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
            if ( $name = SED_Query ( "SELECT name FROM mannschaften WHERE zps=? ORDER BY id DESC LIMIT 1", [$zps] )->fetchOne() )
                return $name;
        }
        
        // Einfach den Vereinsnamen abschneiden
        foreach ( $this->getZpsList () as $zps ){
            if ( $name = SED_Query ( "SELECT Vereinname FROM dwz_vereine WHERE ZPS=?", [$zps] )->fetchOne() )
                return $name;
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
            if ( $so = SED_Query ( "SELECT * FROM mannschaften WHERE zps=? AND LENGTH(so_plz)=5 ORDER BY id DESC", [$zps] )->fetchAssociative() )
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
        if ( $mf = SED_Query ( "
                SELECT *
                FROM turniere t
                INNER JOIN mannschaften m ON m.turnier=t.id AND m.zps=? AND m.mnr=?
                WHERE t.organisation=? AND t.startjahr=?
                LIMIT 1", 
                [$this->data['zps'], $this->data['mnr'], $prefs['organisation'], $prefs['startjahr'] - 1]
            )->fetchAssociative())
            return $mf;
        
        // Letzter angegebener Mannschaftsführer
        foreach ( $this->getZpsList () as $zps ){
            if ( $mf = SED_Query ( "SELECT * FROM mannschaften WHERE zps=? AND LENGTH(so_plz)=5 ORDER BY id DESC", [$zps] )->fetchAssociative() )
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
        $felder = explode ( "\n", str_replace ( "\r", "\n", $prefs ['anmZusatzfelder'] ?: '' ) );
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
        global $prefs;
        $rows = SED_Query ( "SELECT a.feldname, a.inhalt 
            FROM mannschaften m
            INNER JOIN turniere t ON t.id=m.turnier
            INNER JOIN anmeldungZusatzfelder a ON a.mannschaft=m.id
            WHERE m.zps=? AND m.mnr=? 
                AND t.organisation=? AND t.startjahr=?", 
            [$this->get("zps"), $this->get("mnr"), $prefs['organisation'], ((int)$prefs['startjahr'])-1] )
            ->fetchAllAssociative();
        $default = array ();
        foreach ($rows as $entry) 
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
        $query = "SET turnier=?";
        $params = [$globals['tid']];
        foreach ( $this->fields as $field ) {
            if ( $this->get($field) || $field != "zps" ) {
                $query .= ", $field=?";
                $params[] = $this->get($field);
            }
        }

        // Soll eine Mannschaft ohne Spieler bearbeitet werden?
        if ( $mid ){
            $pl = SED_Anmeldung::getPlayerlessTeams ();
            foreach ($pl as $team) {
                if ( $team ["id"] == $mid ) {
                    $query = "UPDATE mannschaften $query WHERE id=? LIMIT 1"; 
                    $params[] = $mid;
                }
            }
        } else
            $query = "INSERT INTO mannschaften $query";
            
        // Query ausführen
        if ( !SED_TryQuery ( $query, $params ) )
            return SED_Error ( "Mannschaftsanmeldung fehlgeschlagen <!-- $query $params -->", false, false, true );
        $this->data ["id"] = $mid ? $mid : SED_Connection()->lastInsertId();

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
                if ( !SED_TryQuery ( $query = "INSERT INTO zusatzempfaenger SET mannschaft=?, email=?", [$this->data["id"], $mail]) )
                    return SED_Error ( "Zusatzempfänger fehlgeschlagen <!-- $query -->", false, false, true );
            }
        }
            
        // Zusatzfelder verarbeiten
        foreach ( $this->getZusatzFelder () as $id=>$feld )
        {
            if ( isset ( $this->data [$id] ) )
                if ( $content = $this->data [$id] )
                    if ( !SED_TryQuery ( $query = "INSERT INTO anmeldungZusatzfelder SET mannschaft=?, feldname=?, inhalt=?", [$this->data["id"], base64_decode($id), $content] ) )
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
