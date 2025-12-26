<?php
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
    private $params = [];
    const SORT_DWZ = "dwz DESC, Spielername";
    const SORT_NAME = "Spielername";

    private function addCondition ( $where, $param ){
        $this->where .= " AND $where";
        $this->params[] = $param;
    }

    function setZpsOptions ( $options ){
      $conditions = array_map(function($zps) { return 'ZPS = ?'; }, $options);
      $this->where .= " AND (".implode (" OR ", $conditions ).")";
      $this->params = array_merge($this->params, $options);
    }

    function setName ( $name ){
        $this->addCondition ( "Spielername LIKE ?", $name .'%' );
    }

    function setGeburt ( $geburt ){
        $this->addCondition ( "?<=Geburtsjahr", $geburt );
    }

    function setGeschlecht ( $ges ){
        $this->addCondition ( "?=Geschlecht", $ges );
    }

    function setVerband ( $verband ){
        $this->addCondition ( "ZPS like ?", $verband . '%' );
    }

    // zps im Format "ZPS-Mgl_Nr"
    function setZPS ( $zps ){
        $verein = substr ( $zps, 0, 5 );
        $mgl = substr ( $zps, 6 );
        $this->setVerband ( $verein );
        $this->addCondition ( "Mgl_Nr=?", $mgl );
    }

    function doQuery ( $limit, $sort ){
        return SED_Query('
            SELECT
                CONCAT(ZPS,\'-\',Mgl_Nr) zps,
                Spielername,
                FIDE_Titel as titel,
                DWZ as dwz,
                FIDE_Elo as elo,
                Geburtsjahr as geburt,
                LOWER(Geschlecht) as geschlecht
            FROM dwz_spieler WHERE
                (status IS NULL OR status<>\'P\')
                '.$this->where.'
            ORDER BY '.$sort.'
            LIMIT '.$limit, $this->params
        )->fetchAllAssociative();
    }

    // Als Spieler-Objekte zurückgeben
    function getPlayerObjectList ( $limit, $sort ){
        require_once ( "spieler.class.php" );
        $players = array ();
        foreach ($this->doQuery ( $limit, $sort ) as $infos) {
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
