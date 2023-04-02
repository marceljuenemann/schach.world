<?php
namespace NSV\Core;

/**
 * Helper for running queries against the NSV database. Note that this is different from the Wordpress database (at least for now).
 */
class Database {

  /** Execute the given query using PDO and returns a PDOStatement. */
  static public function query($sql, $args = null) {
    // TODO: Move NsvPdo from the old core into this class.
    $pdo = NsvPdo();
    if (!$args) return $pdo->query($sql);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($args);
    return $stmt;
  }
}
