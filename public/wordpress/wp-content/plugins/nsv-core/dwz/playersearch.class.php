<?php
namespace NSV\Core\Dwz;

class PlayerSearch extends \NSV\Core\ApiHandler {

  public function getResponse() {
    $name = str_replace(", ", ",", $_GET['name']);
      
    $results = \NSV\Core\Database::query(" 
        SELECT    Spielername, ZPS, Mgl_Nr
        FROM      dwz_spieler
        WHERE     Spielername LIKE CONCAT(?, '%') and Status='A'
        ORDER BY  Spielername
        LIMIT     6", [$name])
      ->fetchAll(\PDO::FETCH_NAMED);

    return array_map(function($player) {
      $player['link'] = NsvDsbSpielerLink($player['ZPS'], $player['Mgl_Nr']);
      return $player;
    }, $results);
  }
}
