<?
namespace NSV\Turniere\Core;

/**
 * Represents a group within a tournament.
 */
class Group {
  public $tournament;
  public $id;
  public $config;
  private $files;

  function __construct($tournament, $id, $config, $files) {
    $this->tournament = $tournament;
    $this->id = $id;
    $this->config = $config;
    $this->files = $files;
  }
  
  public function url() {
    return $this->tournament->url() . $this->id . '/';
  }
  
  public function hasAnyFiles() {
    return count($this->files) > 0;
  }
  
  public function hasFileType($type) {
    return isset($this->files[$type]);
  }

  public function hasFile($type, $round) {
    return $this->hasFileType($type) && isset($this->files[$type][$round]);
  }

  public function listRounds($type) {
    if (!$this->hasFileType($type)) return array();
    $rounds = array_keys($this->files[$type]);
    sort($rounds);
    return $rounds;
  }
  
  public function getLatestRound($type) {
    $rounds = $this->listRounds($type);
    if (!$rounds) return null;
    return end($rounds);
  }
  
  public function loadTable($type, $round = null) {
    return $this->tournament->loadTable($this->config['swtname'], $type, $round ?: $this->getLatestRound($type));
  }
}
