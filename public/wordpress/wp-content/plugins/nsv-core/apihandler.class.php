<?php
namespace NSV\Core;

abstract class ApiHandler extends \NSV\Core\Page {

  function showTheme() {
    return false;
  }
  
  function preprocess() {
    status_header(200); // OK
    header('Content-type: application/json');
  }
  
  function printPage() {
    $data = $this->getResponse();
    echo json_encode($data, JSON_PRETTY_PRINT);
  }
  
  abstract function getResponse();

  function getTitle() {
    return 'NSV API';
  }
}
