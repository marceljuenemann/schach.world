<?php
namespace NSV\Turniere\Ergebnisse;

/**
 * Redirects to the most recent year of this tournament.
 */
class Redirect extends \Nsv\Core\Page {
 
  function preprocess() {
    $turnier = get_query_var('turnier');
    for ($year = (int) date('Y'); $year >= 2008; $year--) {
      if (\NSV\Turniere\Core\Tournament::exists($turnier, $year)) {
        header("Location: /turniere/$turnier/$year/");
        exit;
      }
    }
    throw new \Exception("Turnier nicht gefunden");
  }

  function getTitle() {
    return "Turnierergebnisse";
  }  
  
  function printPage() {
    throw new \Exception("Failed to redirect");
  }
}
