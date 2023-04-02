<?php
namespace NSV\Turniere\Ergebnisse;

class Player extends Base {
  private $id;
  private $teil;

  function preprocess() {
    parent::preprocess();
    $this->id = get_query_var('name');
  
    $this->teil = $this->findPlayerById($this->linkClubs($this->group->loadTable('teil')));
    if (!$this->teil) throw new \Exception("Spieler nicht in Teilnehmerliste gefunden");

    if ($this->group->hasFileType('fort')) {
      $this->fortAll = $this->group->loadTable('fort');
      $this->fort = $this->findPlayerById($this->fortAll);
    }
    if ($this->group->hasFileType('dwz')) {
      $this->dwz = $this->findPlayerById($this->group->loadTable('dwz'));
    }
  }
  
  function printContent() {
    $this->printInfo();
    if ($this->fort) {
      $this->printGames();
    }
    if ($this->dwz) {
      $this->printDwz();
    }
  }
  
  function printInfo() {
    echo "<h3>" . $this->teil->get('Teilnehmer') . "</h3>";
    echo '<dl class="row">';
    $this->printValue('Verein', $this->teil, 'Verein/Ort');
    $this->printValue('Land', $this->teil);
    $this->printValue('Titel', $this->teil);
    $this->printValue('DWZ', $this->fort, 'NWZ');
    $this->printValue('ELO', $this->fort);
    $this->printValue('TWZ', $this->fort);
    $this->printValue('Setzliste', $this->teil, 'Start');
    $this->printValue('Platz', $this->fort, 'Nr.');
    $this->printValue('Punkte', $this->fort);
    echo "</dl>";
  }

  function printDwz() {
    echo "<h3>Inoffizielle DWZ Auswertung</h3>";
    echo '<dl class="row">';
    $this->printValue('Alte DWZ', $this->dwz, 'Ro');
    $this->printValue('Partien', $this->dwz, 'n');
    $this->printValue('Schnitt', $this->dwz, 'Niv');
    $this->printValue('Erwartung', $this->dwz, 'We');
    $this->printValue('Punkte', $this->dwz, 'W');
    $this->printValue('Leistung', $this->dwz, 'Rh');
    $this->printValue('Neue DWZ', $this->dwz, 'Rn');
    $this->printValue('Differenz', $this->dwz, 'Diff.');
    echo "</dl>";
  }

  function printValue($label, $row, $column = null) {
    $column = $column ?: $label;
    if (!$row || !$row->has($column)) return;
    $value = $row->get($column);
    echo "<dt class='col-4 col-sm-3 col-md-2'>$label:</dt>";
    echo "<dd class='col-8 col-sm-9 col-md-10'>$value&nbsp;</dt>";  // &nbsp; to ensure empty value takes the same height.
  }
  
  function printGames() {
    $games = [];
    for ($r = 1; $this->fort->has((string) $r); $r++) {
      /**
       * Parse the SwissChess Fortschritstabelle
       *
       * Some examples of possible formats:
       * - 43W/1
       * - 23s0
       * - 16w+
       * - 16/+
       * - 13+
       * - -
       * - 
       *
       * TODO: some old versions (LEM 2015) seem to have the results over two rows      
       */
      $matches = [];
      $info = $this->fort->get((string) $r);
      $result = preg_match('/([0-9]*)([WwSs]?)\/?([01½+-]?)/u', $info, $matches);
      if ($result) {
        $game = [];
        $game['Runde'] = $r;
        $game['Farbe'] = strtoupper($matches[2]);
        $game['Teilnehmer'] = '';
        $game['DWZ'] = '';
        $game['Platz'] = '';
        $game['Erg.'] = $matches[3];
        if ($matches[1]) {
          $gegner = $this->findPlayer($this->fortAll, function($row) use ($matches) {
            return $row->get('Nr.') == $matches[1] . '.';
          });
          $game['Teilnehmer'] = $gegner->get('Teilnehmer');
          $game['DWZ'] = $gegner->get('NWZ');
          $game['Platz'] = $gegner->get('Nr.');
        }
        $games[] = $game;        
      }

      /*
      // Process the opponent info.
      $info = $this->fort->get((string) $r);
      $info = str_replace('w/', 'w', str_replace('s/', 's', $info));  // Old versions oututed 43w/1
      $colorIndex = max(stripos($info, 'w'), stripos($info, 's'), strpos($info, '/'));

      // Translate into table.
      if ($colorIndex) {
        $gegner = substr($info, 0, $colorIndex);
        $gegner = $this->findPlayer($this->fortAll, function($row) use ($gegner) {
          return $row->get('Nr.') == $gegner . '.';
        });
        $game = [];
        $game['Runde'] = $r;
        $game['Farbe'] = str_replace('/', '', strtoupper($info[$colorIndex]));
        $game['Teilnehmer'] = $gegner->get('Teilnehmer');
        $game['DWZ'] = $gegner->get('NWZ');
        $game['Platz'] = $gegner->get('Nr.');
        $game['Erg.'] = substr($info, $colorIndex + 1);
        $games[] = $game;
      } else {
        // Should be +, - or empty
        $games[] = [
          'Runde' => $r,
          'Farbe' => '',
          'Teilnehmer' => '',
          'DWZ' => '',
          'Platz' => '',
          'Erg.' => $info
        ];
      }
      */
    }
      
    $group = $this->group;
    $table = \NSV\Core\Data\Table::fromArray($games)
        ->addLink('Runde', function($r) use ($group ) {
          if (!$group->hasFile('paar', $r)) return null;
          return $group->url() . "paar/$r/";
        });
    $this->printTable($table);
  }

  function findPlayer($table, $callback) {
    $table->rewind();
    foreach ($table as $row) {
      if ($callback($row)) {
        return $row;
      }
    }
    return null;
  }

  function findPlayerById($table) {
    $id = $this->id;
    return $this->findPlayer($table, function($row) use ($id) {
      return sanitize_title($row->get('Teilnehmer')) === $id;
    });
  }
}
