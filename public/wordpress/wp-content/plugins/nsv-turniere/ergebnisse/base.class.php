<?php
namespace NSV\Turniere\Ergebnisse;

use NSV\Core\Auth;

// TODO: Rename to BasePage
abstract class Base extends \NSV\Core\Page {
  protected $tournament;
  protected $group;
  
  private $hideColumns = array('Geburt', 'At.');
  private $renameColumns = array(
    'Ergebnis' => '',
    'Rang' => '',
    'Start' => '',
    'TlnNr' => '',
    'LfdNr' => '',
    'Tisch' => '',
    'Verein/Ort' => 'Verein',
    'Teilnehmer' => 'Name',
    'NWZ' => 'DWZ',
  );
  private $detailColumns = array(
    'teilrang' => ['Titel', 'Attr.', 'Verein', 'Land', 'S', 'R', 'V', 'BuSumm'],
    'paar' => ['TNr', 'Titel', 'Punkte'],
  );
  
  function preprocess() {
    $this->tournament = \Nsv\Turniere\Core\Tournament::load(get_query_var('turnier'), get_query_var('jahr'));
    $group = get_query_var("gruppe");
    if ($group) {
      $this->group = $this->tournament->getGroup($group);
    }
  }

  function showSidebar() {
    return false;
  }

  function getTitle() {
    return $this->tournament->config['headline'] . ($this->group ? ': ' . $this->group->config['name'] : '');
  }
  
  function getSubtitle() {
    return $this->tournament->config['headline2'];
  }
  
  function getTheme() {
    return $this->tournament->config['theme'];
  }
  
  function printPage() {
    $this->printTabs();
    $this->printContent();
  }
  
  abstract function printContent();

  function printTabs() {
    function printMenuItems($group, $types) {
      foreach ($types as $type => $label) {
        if ($group->hasFileType($type)) {
          echo "<li><a class='dropdown-item' href='{$group->url()}$type/'>$label</a></li>";
        }
      }          
    }

    echo '<ul class="nav nav-tabs mt-3 mb-3 d-print-none">';
    $class = $this->group ? '' : 'active';
    $base_url = $this->tournament->url();
    echo "<li class='nav-item'><a class='nav-link $class' href='$base_url'>Übersicht</a></li>";

    foreach ($this->tournament->getGroups() as $group) {
      $class = $this->group && $this->group->id === $group->id ? 'active' : '';
      $class .= $group->hasAnyFiles() ? '' : ' disabled';
      ?>
      <li class='nav-item dropdown'>
        <a class='nav-link dropdown-toggle <?=$class?>' data-bs-toggle="dropdown" href='#' role="button" aria-expanded="false"><?=$group->config['name']?></a>
        <ul class="dropdown-menu">
          <?php
            printMenuItems($group, array("teilrang" => "Rangliste"));
            
            // Show "Paarungen" only if there's no ranking from that round yet. 
            if ($group->getLatestRound('paar') > $group->getLatestRound('teilrang')) {
              echo "<li><a class='dropdown-item' href='{$group->url()}paar/'>Paarungen</a></li>";
            }
      
            // Show "Ergebnisse" for the last round with a ranking.
            $r = $group->getLatestRound('teilrang');     
            if ($r && $group->hasFile('paar', $r)) {
              echo "<li><a class='dropdown-item' href='{$group->url()}paar/$r/'>Ergebnisse</a></li>";
            }
      
            printMenuItems($group, array(
              "fort" => "Fortschrittstabelle",
              "kreuz" => "Kreuztablle",
              "teil" => "Teilnehmerliste",
              "dwz" => "DWZ Auswertung",
              "elo" => "ELO Auswertung"
            ));
          ?>
        </ul>
      </li>
      <?php
    }

    foreach ($this->tournament->getLinks() as $label => $url) {
      echo "<li class='nav-item'><a class='nav-link' href='$url'>$label</a></li>";
    }
    
    if (Auth::isAdmin()) {
      echo "<li class='nav-item'><a class='nav-link' href='{$base_url}admin/'>Admin</a></li>";
    }

    echo '</ul>';
  }

  function printTable(\NSV\Core\Data\Table $table, $group = null, $type = null) {
    $group = $group ?: $this->group;
    $table = $this->linkClubs($table);
    $table = $table->renameColumns($this->renameColumns)
        ->addLink('Name', function($name) use ($group) {
          return $group->url() . 'spieler/' . sanitize_title($name);
        });
    parent::printTable($table, isset($this->detailColumns[$type]) ? $this->detailColumns[$type] : [], $this->hideColumns);
  }
  
  function linkClubs(\NSV\Core\Data\Table $table) {
    $tournament = $this->tournament;
    return $table->addLink('Verein/Ort', function($name) use ($tournament) {
      return $tournament->url() . 'vereine/' . sanitize_title($name);
    });
  }
}
