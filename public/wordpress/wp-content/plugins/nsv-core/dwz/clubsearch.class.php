<?php
namespace NSV\Core\Dwz;

class ClubSearch extends \NSV\Core\ApiHandler {

  public function getResponse() {
    $name = $_GET['name'];
      
    $results = \NSV\Core\Database::query(" 
        SELECT    Vereinname, ZPS
        FROM      dwz_vereine
        WHERE     Vereinname LIKE CONCAT('%', ?, '%')
        ORDER BY  Vereinname
        LIMIT     6", [$name])
      ->fetchAll(\PDO::FETCH_NAMED);

    return $results;
  }
}
