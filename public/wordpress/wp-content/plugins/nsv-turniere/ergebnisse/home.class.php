<?php
namespace NSV\Turniere\Ergebnisse;

class Home extends Base {
  
  function printContent() {
    $news_file = $this->tournament->dir . 'news.txt';
    if (file_exists($news_file)) {
      include($news_file);
    }
    
    foreach ($this->tournament->getGroups() as $group) {
      echo "<h4>" . $group->config['name'] . "</h4>";
      if ($group->hasFileType("teilrang")) {
        $table = $group->loadTable('teilrang')->limit(5);
        $this->printTable($table, $group, 'teilrang');
        echo "<p class='nsv-table-footer d-print-none'><a href='{$group->url()}teilrang/'>Zur vollständigen Rangliste</a></p>";
      } else if ($group->hasFileType("teil")) {
        $table = $group->loadTable('teil')->limit(5);
        $this->printTable($table, $group, 'teil');
        echo "<p class='nsv-table-footer d-print-none'><a href='{$group->url()}teil/'>Zur vollständigen Teilnehmerliste</a></p>";
      } else {
        echo "<p>Es liegen noch keine Ergebnisse vor.</p>";
      }
    }
  }
}
