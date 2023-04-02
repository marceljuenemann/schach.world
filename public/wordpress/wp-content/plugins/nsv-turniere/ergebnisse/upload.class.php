<?php
namespace NSV\Turniere\Ergebnisse;

class Upload extends Base {
  function preprocess() {
    parent::preprocess();
    \NSV\Core\Auth::requireAuthKey($this->tournament->id, $this->tournament->year);

    if (isset($_FILES['txtfiles'])) {
      $count = count($_FILES['txtfiles']['name']);
      $this->addInfoMessage("Processing $count files...");
      $this->processUploads($_FILES['txtfiles']);
    }
  }

  function processUploads($files) {
    // Validate.
    for ($i = 0; $i < count($files['name']); $i++) {
      if (!($this->endsWith($files['name'][$i], '.txt') or $this->endsWith($files['name'][$i], '.swt'))) {
        $this->addErrorMessage($files['name'][$i] . ": Muss mit .txt oder .swt enden!");
        return;
      }
      if ($files['size'][$i] > 1024 * 1024) {
        $this->addErrorMessage($files['name'][$i] . ": Datei ist zu groß!");
        return;
      }
    }

    // Upload.
    for ($i = 0; $i < count($files['name']); $i++) {
      $uploaddir = $this->tournament->dir;
      $filename = basename($files['name'][$i]);
      $uploadfile = $uploaddir . $filename;
      if (move_uploaded_file($files['tmp_name'][$i], $uploadfile)) {
          $this->addSuccessMessage("$filename was successfully uploaded.");
      } else {
          $this->addErrorMessage("Upload of $uploadfile failed!");
      }
    }
  }

  function endsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) return true;
    return (strtolower(substr($haystack, -$length)) === strtolower($needle));
  }
  
  function printContent() {
    echo "<h3>Upload</h3>";
    ?>
    <h4>Einmalige Swiss Chess Einstellungen</h4>
    <ul>
    <li>Swiss-Chess updaten
    <li><b>(NEU!)</b> Unter Turnier / Ausgabeeinstellungen, "Dateinamen mit Rundeninfo" aktivieren
    <li>Die Swiss-Chess Dateien müssen dem Namensschema folgen, dass in der config.json eingetragen ist
    <li>Die Einstellung für Textdatei-Ausgabe bearbeiten. Dazu in einem beliebigen Druckdialog auf Textdatei / Zwischenablage klicken
      <ul>
      <li> Checkbox vor "mit Tabulatoren trennen" setzen
      <li> Checkbox vor "in MS-DOS Text" entfernen
      </ul>
    </ul>

    <h4>Nach jeder Runde</h4>
    <ol>
    <li> Turnier/Sammelausgabe aufrufen
    <li> Folgende Listen auswählen:
      <ul>
      <li> Teilnehmerliste
      <li> Paarungsliste (auch der vorherigen Runde)
      <li> Rangliste + Fortschrittstabelle (<b>nach der vorherigen Runde auswählen</b>, es sei denn es war die letzte Runde)
      <li> DWZ- und evtl. ELO-Auswertung
      </ul>
    <li> Die restlichen Listen deaktivieren
    <li> Auf der rechten Seite "Text-Dateien erstellen" drücken
    <li> Die erstellten Dateien hier hochladen:
    </ol>

    <h4>Upload (max. 300 Dateien!)</h4>
    <form action="?auth=<?=get_query_var('auth')?>" method="post" enctype="multipart/form-data">
      <input name="txtfiles[]" type="file" multiple accept=".txt">
      <input type="submit" value="Dateien hochladen" />
    </form>
    <?php
  }
}
