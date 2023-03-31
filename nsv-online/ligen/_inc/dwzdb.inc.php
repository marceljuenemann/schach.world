<?
/* DWZ Datenbank
 *
 * In dieser Datei wird die Klasse SED_DWZ_Request zur Verfügung
 * gestellt, mit der Spielerdaten aus der DWZ Datenbank abgefragt
 * werden können.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage libs
 */

    require_once ( "main.inc.php" );

class SED_DWZ_Request {
    private $where = "";
    const SORT_DWZ = "dwz DESC, Spielername";
    const SORT_NAME = "Spielername";

    function addCondition ( $where ){
        $this->where .= " AND $where";
    }

    function addConditionList ( $where ){
        $this->where .= " AND (".implode (" OR ", $where ).")";
    }

    function setName ( $name ){
        $this->addCondition ( "Spielername LIKE '$name%'" );
    }

    function setGeburt ( $geburt ){
        $this->addCondition ( "$geburt<=Geburtsjahr" );
    }

    function setGeschlecht ( $ges ){
        $this->addCondition ( "'$ges'=Geschlecht" );
    }

    function setVerband ( $verband ){
        $this->addCondition ( "ZPS like '$verband%'" );
    }

    // zps im Format "ZPS-Mgl_Nr"
    function setZPS ( $zps ){
        $verein = substr ( $zps, 0, 5 );
        $mgl = substr ( $zps, 6 );
        $this->setVerband ( $verein );
        $this->addCondition ( "Mgl_Nr='$mgl'" );
    }

    function doQuery ( $limit, $sort ){
        global $globals;
        $rsrc = mysql_query ( $x = "
            SELECT
                CONCAT(ZPS,'-',Mgl_Nr) zps,
                Spielername,
                FIDE_Titel as titel,
                DWZ as dwz,
                FIDE_Elo as elo,
                Geburtsjahr as geburt,
                LOWER(Geschlecht) as geschlecht
            FROM dwz_spieler WHERE
                (status IS NULL OR status<>'P')
                ".$this->where."
            ORDER BY $sort
            LIMIT $limit", $globals ['db'] );
        return $rsrc;
    }

    // Als Spieler-Objekte zurückgeben
    function getPlayerObjectList ( $limit, $sort ){
        require_once ( "spieler.class.php" );
        $players = array ();
        $rsrc = $this->doQuery ( $limit, $sort );

        // Anfrage auswerten
        if ( $rsrc ) while ( $infos = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ){
            try {
                // Jeden Werte einzeln setzen
                $spieler = new SED_Spieler ();
                foreach ( $infos as $name => $value ){
                    if ( $name == "Spielername" )
                        $spieler->setName ( $value );
                    else
                        $spieler->set ( $name, $value );
                }
                $players [] = $spieler;
            } catch ( WrongFormatException $e ) {
                // Da ist irgenein Fehler in der DWZ-DB!?!
            }
        }
        return $players;
    }
}
?>
