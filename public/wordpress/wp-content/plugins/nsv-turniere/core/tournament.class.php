<?
namespace NSV\Turniere\Core;

/**
 * Represents a tournament, consisting of tournament name and year (e.g. lem/2020). Config and results
 * are expected to be stored in wp-content/uploads/turniere/$name/$year.
 */
class Tournament {

  public $dir;
  public $id;
  public $year;
  public $config;   // object loaded from combining the various config.json files. 
  private $files;   // multi-dimensionsional array with all available files: $files[group][type][round]

  private function __construct($dir, $id, $year, $config, $files) {
    $this->dir = $dir;
    $this->id = $id;
    $this->year = $year;
    $this->config = $config;
    $this->files = $files;
  }
  
  public function url() {
    return '/turniere/' . $this->id . '/' . $this->year . '/';
  }
  
  public function getGroup($id) {
    $config = $this->config['groups'][$id];
    if (!$config) throw new \Exception("Group not found");
    return new Group($this, $id, $config, $this->files[$config['swtname']] ?: []);
  }
  
  public function getGroups() {
    return array_map([$this, 'getGroup'], array_keys($this->config['groups']));
  }
  
  public function getLinks() {
    if (!isset($this->config['links'])) return array();
    return $this->config['links'];
  }

  public function loadTable($group, $type, $round) {
    $filename = $this->files[$group][$type][$round]['filename'];
    if (!$filename) throw new \Exception("Requested table not found");
    return \NSV\Core\Data\Table::fromCsvFile($this->dir . $filename, "\t", true)
        ->transformColumn('Teilnehmer', function($name) {
          return str_replace(',', ', ', $name);
        });
  }

  public static function load($id, $year) {
    // TODO: Move to some NSV util?
    if (!filter_var($id, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => "/^[a-z0-9-]+$/")))) throw new \Exception("Invalid tournament ID");
    if (!filter_var($year, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => "/^[a-z0-9]+$/")))) throw new \Exception("Invalid year");
    
    // Directories.
    $upload_dir = wp_upload_dir();
    $base_dir = $upload_dir['basedir'] . '/turniere/';
    $tournament_dir = $base_dir . $id . '/';
    $year_dir = $tournament_dir . $year . '/';
    
    // Load base config.
    $a = json_decode(file_get_contents($base_dir . 'config.json'), true);
    $b = json_decode(file_get_contents($tournament_dir . 'config.json'), true);

    // Load torunament config.
    $config_file = $tournament_dir . $year . '.config.json';
    $legacy_file = $year_dir . 'config.inc.php';
    if (file_exists($config_file)) {
      $c = json_decode(file_get_contents($config_file), true);
    } else if (file_exists($legacy_file)) {
      $c = Tournament::loadLegacyConfig($legacy_file);
    }
    if (!$c) throw new \Exception("Failed to load tournament config");
    $config = array_merge($a, $b, $c);
    
    // Load files and create Tournament instance.
    return new Tournament($year_dir, $id, $year, $config, Tournament::loadFiles($year_dir, $config));
  }

  private static function loadLegacyConfig($config_file) {
    require($config_file);
    $result = [
      'headline' => $config['headline1'],
      'headline2' => $config['headline2'],
      'groups' => []
    ];
    foreach ($config['menuname'] as $id => $name) {
      $result['groups'][$id] = ['name' => $name, 'swtname' => $id];
    }
    return $result;
  }
    
  // Loads all files from the year directory with filenames unchanged from SwissChess export.
  private static function loadFiles($dir, $config) {
    if ($config['format'] === 'legacy-txt') {
      return Tournament::loadFilesLegacy($dir, $config['groups']);
    }
    
    $files = array ();
    if ( $dh = opendir($dir) ) {
      while (($filename = readdir($dh)) !== false) {
        $file = explode ( "-", $filename );
        if ( count ( $file ) < 3 ) continue;

        $r = explode ( ".", $file [count($file)-1] );
        if ( strtolower ( $r [1] ) != "txt" ) continue;
        $r = substr ( $r[0], 1 );

        $ak = implode ( "-", array_slice ( $file, 0, count ( $file ) - 2 ) );
        $type = $file [count($file)-2];

        if ( strlen ( $ak ) && strlen ( $type ) > 2 && is_numeric ( $r ) ) {
          $files[$ak][strtolower($type)][$r] = array(
            'filename' => $filename,
            'mtime' => filemtime( $dir . $filename )
          );
        }
      }
    }
    closedir($dh);
    return $files;
  }
        
  // Loads files named in the legacy structure, i.e. one directory per group with files named tab_R.TXT, fort.TXT, paar.TXT etc.
  private static function loadFilesLegacy($base_dir, $groups) {
    $files = array ();
    foreach ($groups as $group_id => $group_config) {
      $ak = $group_config['swtname'];
      $dir = $base_dir . $ak . '/';
      if ($dh = opendir($dir)) {
        while (($filename = readdir($dh)) !== false) {
          // Is it a TXT file?
          if (strpos($filename, '.TXT') !== strlen($filename) - strlen('.TXT')) continue;
          
          // Is it a table?
          if (strpos($filename, 'tab_') === 0) {
            $r = substr($filename, strlen('tab_'), strlen($filename) - strlen('tab_.TXT'));
            $type = 'teilrang';
          } else {
            $r = '1'; // TODO. Set to maximum round
            $type = substr($filename, 0, strlen($filename) - strlen('.TXT'));            
          }
          
          $files[$ak][$type][$r] = array(
            'filename' => $ak . '/' . $filename,
            'mtime' => filemtime( $dir . $filename )
          );
        }
      }
      closedir($dh);      
    }
    return $files;
  }
}
