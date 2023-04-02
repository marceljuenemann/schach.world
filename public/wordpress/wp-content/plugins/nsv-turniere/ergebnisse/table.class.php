<?php
namespace NSV\Turniere\Ergebnisse;

class Table extends Base {
  private $type;
  private $round;
  private $table;
  
  function preprocess() {
    parent::preprocess();
    $this->type = get_query_var('typ');
    $this->round = get_query_var('runde') ?: $this->group->getLatestRound($this->type);
    $this->table = $this->group->loadTable($this->type, $this->round)
        ->filterByQueryVar('Attr.')
        ->addFilterLink('Attr.')
        ->filterByQueryVar('Land')
        ->addFilterLink('Land');
  }
  
  function printContent() {
    echo "<h3>" . $this->getTableHeadline($this->type, $this->round) . "</h3>";
    $this->printRoundSelector();
    $this->printTable($this->table, $this->group, $this->type);
  }
  
  function getTableHeadline($type, $r) {
    $headlines = array();
    $headlines ['teil'] = "Teilnehmerliste";
    $headlines ['paar'] = "Paarungen der $r. Runde";
    $headlines ['teilrang'] = "Rangliste nach der $r. Runde";
    $headlines ['kreuz'] = "Kreuztabelle nach der $r. Runde";
    $headlines ['fort'] = "Fortschrittstabelle nach der $r. Runde";
    $headlines ['dwz'] = "Inoffizielle DWZ-Auswertung";
    $headlines ['elo'] = "Inoffizielle ELO-Auswertung";    
    return $headlines[$type];
  }
  
  function printRoundSelector() {
    echo '<ul class="pagination pagination-sm mb-2 d-print-none">';
    echo '<li class="page-item disabled"><span class="page-link" style="border: 0">Runde:</span></li>';
    foreach ($this->group->listRounds($this->type) as $round) {
      echo "<li class='page-item " . ($round == $this->round ? ' active' : '') . "'>";
      echo "<a class='page-link' href='{$this->group->url()}{$this->type}/$round'>$round</a>";
      echo "</li>";
    }
    echo '</ul>';
  }
}
