<?php
namespace NSV\Core;

class ErrorPage extends \NSV\Core\Page {
  function __construct($e) {
    if ($e instanceof \Exception) {
      $this->addErrorMessage($e->getMessage());
    }
  }

 function getTitle() {
    return 'Fehler';
  } 
  
  public function printPage() {
    
  }
}
