<?php
namespace NSV\Turniere\Ergebnisse;

class Club extends Base {
  private $id;
  
  function preprocess() {
    parent::preprocess();
    $this->id = get_query_var('name');
    $this->name = $this->findName();
  }

  function printContent() {
    echo "<h3>" . $this->name . "</h3>";

    foreach ($this->tournament->getGroups() as $group) {
      if ($group->hasFileType("teilrang")) {
        $table = $this->filterClub($group->loadTable('teilrang'));
        if ($table->valid()) {
          echo "<h4>" . $group->config['name'] . "</h4>";
          $this->printTable($table, $group, 'teilrang');
          echo "<p class='nsv-table-footer d-print-none'><a href='{$group->url()}teilrang/'>Zur vollständigen Rangliste</a></p>";
        }
      } else if ($group->hasFileType("teil")) {
        $table = $this->filterClub($group->loadTable('teil'));
        if ($table->valid()) {
          echo "<h4>" . $group->config['name'] . "</h4>";
          $this->printTable($table, $group, 'teil');
          echo "<p class='nsv-table-footer d-print-none'><a href='{$group->url()}teil/'>Zur vollständigen Teilnehmerliste</a></p>";
        }
      }
    }
  }
  
  function filterClub($table) {
    $id = $this->id;
    $table = $table->filter(function($row) use ($id) {
      return sanitize_title($row->get('Verein/Ort')) == $id;
    });
    $table->rewind();
    return $table;
  }
  
  function findName() {
    foreach (array('teil', 'teilrang') as $type) {
      foreach ($this->tournament->getGroups() as $group) {
        if ($group->hasFileType($type)) {
          $table = $this->filterClub($group->loadTable($type));
          if ($table->valid()) {
            $row = $table->current();
            return $row->get('Verein/Ort');
          }
        }
      }
    }
    throw new \Exception("Verein nicht gefunden");
  }
}
