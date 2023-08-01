<?php
namespace NSV\Core;

/**
 * Represents a custom dynamically generated page.
 */
abstract class Page {
  private $infoMessages = array();

  /** Called before anything is outputted. Ideal for sending headers or processing POST data. */
  function preprocess() {}

  /** Page title for title tag and <h1> */
  abstract function getTitle();
  
  /** Optional subtitle to be printed at top of the page. */
  function getSubtitle() {
    return null;
  }

  /** Whether the theme should output any HTML at all. */
  function showTheme() {
    return true;
  }

  /** Whether the theme should output the standard sidebar. */
  function showSidebar() {
    return true;
  }
  
  /** Returns the nsv2020 theme (not to be confused with the wordpress theme!) */
  function getTheme() {
    return 'nsv';
  }
  
  abstract function printPage();

  function printPageTitle() {
    echo '<h1 class="card-title">' . $this->getTitle() . '</h1>';
    $subtitle = $this->getSubtitle();
    if ($subtitle) {
      echo '<h5 class="card-subtitle text-muted mb-3">' . $subtitle . '</h5>';
    }
  }
  
  function printInfoMessages() {
    foreach ($this->infoMessages as $info) {
      echo '<div class="alert alert-' . $info['type'] . '" role="alert">';
      echo $info['message'];
      echo '</div>';
    }
  }
  
  /**
   * Prints a table. On small screens, the $detailColumns will be hidden, unless the user utilizes the toggle for showing them.
   */
  function printTable(Data\Table $table, $detailColumns = array(), $skipColumns = array()) {
    static $idCounter = 0;
    echo "<div class='overflow-auto mb-4'>";
    if ($detailColumns) {
      $id = 'nsv-details-toggle-' . ++$idCounter;
      ?>
        <div class="custom-control custom-switch d-sm-none mb-2">
          <input type="checkbox" class="custom-control-input" id="<?=$id?>" onclick="jQuery(this).parent().parent().toggleClass('nsv-details-show')">
          <label class="custom-control-label" for="<?=$id?>">Details anzeigen</label>
        </div>
      <?
    }
    echo "<table class='nsv-table'>";
    echo "<tr>";
    foreach ($table->columns() as $column) {
      if (in_array($column, $skipColumns)) continue;
      $class = in_array($column, $detailColumns) ? 'nsv-details' : '';
      echo "<th class='$class'>$column</th>";
    }
    echo "</tr>";

    foreach ($table as $row) {
      echo "<tr>";
      foreach ($row as $column => $value) {
        if (in_array($column, $skipColumns)) continue;
        $class = in_array($column, $detailColumns) ? 'nsv-details' : '';
        echo "<td class='$class'>$value</td>";
      }
      echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
  }

  /* Outputs a bootstrap accordion with the given options and using the callback to generate the content for each option. */
  function printAccordion($options, $content_callback) {
    static $id;
    $id++;
    
    echo "<div class='accordion' id='nsv-accordion-$id'>";
    $show_option = true;
    foreach ($options as $option) {
      $oid = sanitize_title($option);
      ?> 
        <div class="card">
          <div class="card-header" id="heading-<?=$oid?>">
            <h2 class="mb-0">
              <button class="btn btn-block text-start" type="button" data-toggle="collapse" data-target="#collapse-<?=$oid?>" aria-expanded="true" aria-controls="collapse-<?=$oid?>">
                <b><?=$option?></b>
              </button>
            </h2> 
          </div>
          <div id="collapse-<?=$oid?>" class="collapse <?=$show_option ? 'show' : '';?>" aria-labelledby="heading-<?=$oid?>" data-parent="#nsv-accordion-<?=$id?>">
            <div class="card-body">
              <?php $content_callback($option) ?>
            </div>
          </div>
        </div>
      <?php
      $show_option = false;
    }
    echo "</div>";
  }
  
  function addInfoMessage($msg) {
    $this->infoMessages[] = array('type' => 'primary', 'message' => $msg);
  }

  function addSuccessMessage($msg) {
    $this->infoMessages[] = array('type' => 'success', 'message' => $msg);
  }

  function addErrorMessage($msg) {
    $this->infoMessages[] = array('type' => 'danger', 'message' => $msg);
  }
}
