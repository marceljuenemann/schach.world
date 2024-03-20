<?
/* Klasse Spieler
 *
 * In dieser Datei wird die Klasse SED_Spieler zur Verfügung
 * gestellt, mit der ein neuer Spieler in die Datenbank eingefügt
 * werden kann (z.B. nach einer DWZ-DB-Abfrage) sowie ein vorhandener
 * Spieler repräsentiert werden kann.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage libs
 */

/*
 * SED_Spieler ()

 * get ( name ) throws UnknownFieldException
 * getDecoded ( name ) // html_entities_decode
 * getName () // Zusammengesetzt und bereit zur Ausgabe (mit entitiies)
 * isFieldSet ( name )

 * set ( name, value ) throws UnknownFieldException, WrongFormatException
 * check ( name, value ) throws UnknownFieldException, WrongFormatException
 * setName ( name ) throws WrongFormatException
 * autofill ()

 * getJSON ()
 * parseJSON ( json ) throws UFW, WFE
 * saveToDB ()

 * static getNextBrettNr ( mannschaft )
 */

require_once ( "exceptions.inc.php" );

class SED_Spieler {
    // Konstanten
    var $fields = array ( "id", "mannschaft", "nachname", "vorname",
        "titel", "zps", "brettnr", "dwz", "elo", "geburt", "geschlecht",
        "nmSid", "nmR" );
    var $xnachname = array ( "ter", "zur", "zum", "zu", "der", "dem",
        "den", "da", "de", "Jr", "Jr.", "im", "in", "aus", "vor", "el", "la",
        "bei", "v.", "vom", "von", "van" );
    var $xtitel = array ( "Prof.", "Dr.", "GM", "IM", "FM", "CM",
        "WGM", "WIM", "WFM", "WCM" );
    var $regex = array (
        "id" => "/^\d+$/",
        "mannschaft" => "/^\d+$/",
        "nachname" => "/^(.){2,}$/", // wegen entities auch Zahlen!
        "vorname" => "/^(.)*$/",
        "titel" => "/^(.)*$/",
        "zps" => "/^$|^[0-9A-Z]{5}-\d{1,4}$/",
        "brettnr" => "/^\d{1,4}$/",
        "dwz" => "/^\d{0,4}$/",
        "elo" => "/^\d{0,4}$/",
        "geburt" => "/^$|^\d{4}$/",
        "geschlecht" => "/^$|^(m|w)$/",
        "nmSid" => "/^\d*$/",
        "nmR" => "/^\d*$/"
    );

    // Instanzvariablen
    private $data = array ();

    /* Liefert einen Wert, mit htmlentities codiert
     * @return string|UnknownFieldException
     */
    function get ( $name ){
        // Ist das Feld gesetzt
        if ( !$this->isFieldSet ( $name ) )
            throw new UnknownFieldException ( $name );

        // Wert zurückgeben
        return $this->data [$name];
    }

    /* Liefert einen Wert ohne HTML-Entities, in
     * @return string|UnknownFieldException
     */
    function getDecoded ( $name ){
        $value = $this->get ( $name );
        $value = html_entity_decode ( $value, ENT_QUOTES, "ISO-8859-1" );
        return $value;
    }

    // Zusammengesetzten Namen liefern
    function getName (){
        return SED_Spielername ( $this->data );
    }

    /* Prüft, ob ein Feld gesetzt ist
     * @return bool
     */
    function isFieldSet ( $name ){
        // Gibt es das Feld überhaupt?
        if ( !in_array ( $name, $this->fields ) )
            throw new UnknownFieldException ( $name );

        // Ist das Feld gesetzt?
        return isset ( $this->data [$name] );
    }


    /* Setzt ein Datum
     * @return bool|UnknownFieldException|WrongFormatException
     */
    function set ( $name, $value ){
        // Richtiges Format? evlt. Exception
        $this->check ( $name, $value );

        // Umwandlung
        $value = htmlentities ( $value, ENT_QUOTES, "ISO-8859-1", false );

        // FIDE- und Dr. müssen nicht gleichzeitig gesetzt werden
        if ( $name == "titel" && isset ( $this->data ["titel"] ) && strlen ( $this->data ["titel"] ) )
            $this->data ["titel"] = $value . (strlen ($value) ? " " : "") . $this->data ["titel"];
        else
            $this->data [$name] = $value;
        return true;
    }

    /* Überprüft das Format eines Wertes
     * @return bool|UnknownFieldException|WrongFormatException
     */
    function check ( $name, $value ){
        // Gibt es das Feld?
        if ( !isset ( $this->regex [$name] ) )
            throw new UnknownFieldException ( $name );

        // Entspricht das Feld nicht dem nötigen Format
        if ( !preg_match ( $this->regex [$name], $value ) )
            throw new WrongFormatException ( "Der Wert '$value' entspricht nicht dem ben&ouml;tigten Format des Feldes $name" );

        // Ansonsten gibt es im Moment keine Überprüfungen
        return true;
    }

    /* Setzt einen Namen
     * @return bool|WrongFormatException
     */
    function setName ( $name ){
        // Am Komma aufspalten
        $name = str_replace ( ", ", ",", $name );
        $parts = explode ( ",", $name );

        // Nachname,Vorname,Titel
        if ( count ( $parts ) >= 3 ){
            $this->set ( "nachname", ( $parts [0] ) );
            $this->set ( "vorname", ( $parts [1] ) );
            $this->set ( "titel", $parts [2] );
            return true;
        }

        // Nachname, Vorname
        if ( count ( $parts ) == 2 ){
            $this->set ( "nachname", ( $parts [0] ) );
            $this->set ( "vorname", ( $parts [1] ) );
            $this->set ( "titel", "" );
            return true;
        }

        // Titel Vorname Vorname [von] Nachname
        if ( count ( $parts ) == 1 ){
            // An Leerzeichen aufsplitten
            $parts = explode ( " ", $name );
            $nn = $vn = $tit = "";
            $noNachname = true;

            // Parts von hinten nach vorne verarbeiten
            for ( $i = count ( $parts )-1; $i >= 0; --$i ){
                $part = $parts [$i];

                // Definitiv Nachname (z.B. von Jünemann Jr.)
                if ( in_array ( $part, $this->xnachname ) )
                    $nn = $part . " " . $nn;

                // Definitiv Titel (z.B. WGM Prof. Dr.)
                elseif ( in_array ( $part, $this->xtitel ) )
                    $tit = $part . " " . $tit;

                // Sicherlich Nachname (letzter Part im Namen)
                elseif ( $noNachname ){
                    $nn = $part . " " . $nn;
                    $noNachname = false;
                }

                // Wahrscheinlich Vorname (restliche Parts)
                else {
                    $vn = $part . " " . $vn;
                }
            }

            // Speichern
            $this->set ( "nachname",  trim ( $nn ) );
            $this->set ( "vorname", trim ( $vn ) );
            $this->set ( "titel", trim ( $tit ) );
            return true;
        }

        // Sonst
        throw new WrongFormatException ( "Zu viele Kommata im Namen" );
    }

    /* Falls id gesetzt ist, werden die Daten aus der DB geholt
     * Ansonsten werden die Daten mittels des Namens gesetzt
     * Nicht ausgedacht werden: id, mannschaft, brettnr, nmSid, nmR
     * @return bool War autofill halbwegs erfolgreich?
     */
    function autofill ($verband = ""){
        // Ist die ID gesetzt?
        if ( $this->isFieldSet ( "id" ) ){
            global $globals;

            // Datenbank-Abfrage
            $id = $this->get ( "id" );
            $rsrc = mysql_query ( "SELECT * FROM spieler WHERE id=$id LIMIT 1", $globals ['db'] );
            if ( !$rsrc || !mysql_num_rows ( $rsrc ) )
                throw new UnknownIDException ( $id );

            // Felder setzten
            foreach ( mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) as $name=>$value )
                $this->set ( $name, $value );
            return true;
        }

        // Aus DWZ-Datenbank abfragen?
        else {
            // Request erstellen
            require_once ( "dwzdb.inc.php" );
            $req = new SED_DWZ_Request ();
			$req->setVerband ($verband);
			
            // ZPS bekannt?
            if ( $this->isFieldSet ( "zps" ) )
                $req->setZPS ( $this->get ( "zps" ) );
            else
                // Evtl. Exception auslösen, aber unwahrscheinlich
                $req->setName ( $this->getDecoded ( "nachname" ) . "," . $this->getDecoded ( "vorname" ) );

            // evtl. Geburtsjahr mit einbeziehen
            if ( $this->isFieldSet ( "geburt" ) )
                $req->setGeburt ( $this->get ( "geburt" ) );

            // Anfrage durchführen
            $result = $req->doQuery ( 2, SED_DWZ_Request::SORT_DWZ );

            // Anfrage auswerten
            if ( $result && mysql_num_rows ( $result ) == 1 ){
                // Daten einzeln setzen
                try {
                    foreach ( mysql_fetch_array ( $result, MYSQL_ASSOC ) as $name => $value ){
                        if ( $name == "Spielername" )
                            $this->setName ( $value );
                        else
                            $this->set ( $name, $value );
                    }
                    return true;
                } catch ( WrongFormatException $e ){
                    // Ein Fehler in der DWZ-DB!?!
                    return false;
                }
            } else {
                // Kein oder mehr als ein Eintrag in der DB
                return false;
            }
        }
    }


    // Spieler in Datenbank einfügen
    // Es müssen alle Felder gesetzt sein!
    // Ausnahmen: id (ggf. Nachmeldung), brettnr (ggf. nächste)
    function saveToDB (){
        // Vorbereitungen
        require_once ( "turnier.inc.php" );
        global $globals;

        // Brettnr selbst bestimmen
        if ( !$this->isFieldSet ( "brettnr" ) ){
            $this->set ( "brettnr", SED_Spieler::getNextBrettNr ( $this->get ( "mannschaft" ) ) );
        }

        // SET generieren
        $set = array ();
        foreach ( $this->fields as $field ){
            if ( $field == "id" ) continue; // Nachmeldung bzw. so lassen
            $value = $this->isFieldSet ( $field ) ? $this->getDecoded ( $field ) : "";
            $value = str_replace ( "'", "\\'", $value );
            $set [] = "$field='$value'";
        }
        $set = implode ( ", ", $set );

        // Nachmeldung?
        if ( !$this->isFieldSet ( "id" ) ){
            $sql = "INSERT INTO spieler SET $set";
            if ( !mysql_query ( $sql, $globals['db'] ) )
                SED_Error ( "Spielerdaten konnten nicht gespeichert werden! <!-- $set -->", true );
            $this->set ( "id", mysql_insert_id () );

        // Datenänderung
        } else {
            $sql = "UPDATE spieler SET $set WHERE id='".$this->get("id")."' LIMIT 1";
            if ( !mysql_query ( $sql, $globals['db'] ) )
                SED_Error ( "Spielerdaten konnten nicht geändert werden! <!-- $set -->", true );
        }

        // Cache leeren
        SED_Cache::clearTeam ( 0, SED_Cache::TEAM_AUFSTELLUNG );
        SED_Cache::clearSpieltag ();
        return true;
    }

    // Liefert die Spielerdaten als JSON-Object
    function getJSON (){
        // json erwartet UTF-8! Ist durch htmlentities aber erledigt
        return json_encode ( $this->data );
    }

    // Liest einen JSON-String ein (darf nur entities enthalten, keine Umlaute)
    function parseJSON ( $json ){
        $data = json_decode ( $json, true );
        if ( !is_array ( $data ) )
            SED_Error ( "Fehler beim Neu-Einlesen der Spielerdaten! <!-- $json -->", true );
        foreach ( $data as $name=>$value )
            $this->set ( $name, $value );
    }


    // Liefert die nächste freie Brett-Nummer
    static function getNextBrettNr ( $mid ){
        // Wenn es Spieler in der Mannschaft gibt, dann sollte es klappen
        if ( $bnr = SED_Query ( "SELECT brettnr+1 FROM spieler WHERE mannschaft=? ORDER BY brettnr DESC LIMIT 1", [$mid] )->fetchOne() )
            return $bnr;

        // Ansonsten einfach die 1
        global $prefs;
        if ( !$prefs ['spielDreistelligeNr'] ) return 1;
        return SED_Value ( "SELECT mnr*100+1 FROM mannschaften WHERE id=? LIMIT 1", [$mid] );
    }
}
?>
