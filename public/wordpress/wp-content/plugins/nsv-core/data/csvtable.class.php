<?php
namespace NSV\Core\Data;
  
/**
 * CSV file reader that treats the first row of the file as column names. By default, all rows that don't contain the delimiter are ignored.
 */
class CsvTable extends \FilterIterator {
  private $columns;
  private $utf8encode;
  private $skipEmptyRows;
  
  function __construct($filename, $delimiter = ',', $uft8encode = false, $skipEmptyRows = true) {
    $this->utf8encode = $uft8encode;
    $this->skipEmptyRows = $skipEmptyRows;

    $file = new \SplFileObject($filename);
    $file->setFlags(\SplFileObject::READ_CSV);
    $file->setCsvControl($delimiter);

    parent::__construct($file);
    $this->rewind();
  }
  
  public function columns() {
    return $this->columns;
  }
  
  public function accept() {
    if (!$this->skipEmptyRows) return true;
    // TODO: Should trim instead, i.e. only remove rows from the beginning and end.
    return count($this->getInnerIterator()->current()) > 1;
  }
  
  public function current() {
    $data = parent::current();
    if ($this->utf8encode) {
      return array_map('utf8_encode', $data);
    } else {
      return $data;
    }
  }

  function rewind() {
    parent::rewind();
    $this->columns = $this::current();
    $this->next();
  }
}
