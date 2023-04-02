<?php
namespace NSV\Core;

class TestPage extends \NSV\Core\Page {

  function getTitle() {
    return 'Sandbox';
  } 
  
  public function printPage() {
    echo "Hello World!";
  }
  
}
