<?php
namespace NSV\Turniere\Ergebnisse;

class Admin extends Base {
  function preprocess() {
    \NSV\Core\Auth::requireAdmin();
    parent::preprocess();
  }
  
  function printContent() {
    echo "<h3>Admin-Bereich</h3>";
    $key = \NSV\Core\Auth::generateAuthKey($this->tournament->id, $this->tournament->year);
    $link = $this->tournament->url() . 'upload/?auth=' . $key;
    echo "Die Ergebnisse können über den folgenden (<b>öffentlichen!</b>) Link hochgeladen werden: ";
    echo "<a href='$link'>$link</a>";    
  }
}
