<?php
namespace NSV\Core\Data;

/**
 * Helper for processing tabular data.
 *
 * This is implementated as an iterator over each row of the table, where each row is an array. The column names
 * are stored separately and may repeat. Note that everything is evaluated lazily unless explicitly stated, such
 * that data does not have to be loaded into memory.
 */
class Table extends \IteratorIterator {
  private $columns;
  
  function __construct($columns, $iterator) {
    $this->columns = $columns;
    parent::__construct($iterator);
  }

  public function columns() {
    return $this->columns;
  }
  
  public function current() {
    return new Row($this->columns, parent::current());
  }

  /**
   * Returns a new Table where each row array is processed using the given $callback.
   */
  private function transform($columns, $callback) {
    return new Table($columns, new class($this->getInnerIterator(), $callback, $this->columns) extends \IteratorIterator {
      private $callback;
      private $columns;

      function __construct($iterator, $callback, $columns) {
        parent::__construct($iterator);
        $this->callback = $callback;
        $this->columns = $columns;
      }

      function current() {
        $row = new Row($this->columns, parent::current());
        $result = call_user_func($this->callback, $row);
        return $result ?: $row->getArrayCopy();
      }
    });
  }

  /**
   * Transforms the given column using the callback function. The callback function receives the current value as well
   * as the Row object as parameters.
   */
  function transformColumn($column, $callback) {
    return $this->transform($this->columns, function($row) use ($column, $callback) {
      $row->transformColumn($column, $callback);
    });
  }
  
  /** Transform the column names while leaving all data untouched. */
  function transformColumnNames($callback) {
    return new Table(array_map($callback, $this->columns), $this->getInnerIterator());
  }

  function renameColumns($mapping) {
    return $this->transformColumnNames(function($column) use ($mapping) {
      if (isset($mapping[$column])) {
        return $mapping[$column];
      }
      return $column;
    });
  }

  /**
   * Adds a link to the specified column with the URL returned by the callback. 
   */
  function addLink($column, $callback) {
    return $this->transformColumn($column, function($value, $row) use ($callback) {
      $url = $callback($value, $row);
      return $url !== null ? "<a href='$url'>$value</a>" : $value;
    });
  }
  
  /**
   * Adds links to the specified $column that will add a query vars to the URL for each given $link_column.
   * 
   * For example: addFilterLinks('Name', array('Last name', 'First name')) will add a link to ?last-name=Doe&first-name=John
   * to column 'Name'.
   *
   * If $link_columns is not set, $column will be used.   
   */
  function addFilterLink($column, $link_columns = null) {
    if (!$link_columns) $link_columns = array($column);
    return $this->addLink($column, function($value, $row) use ($link_columns) {
      $query_vars = array_map(function($link_column) use ($row) {
        return sanitize_title($link_column) . '=' . sanitize_title($row->get($link_column));
      }, $link_columns);
      return '?' . implode("&", $query_vars);
    });
  }
  
  /**
   * Adds a column to the table.
   */
  function createColumn($name, $callback) {
    return $this->transform(array_merge($this->columns, array($name)), function($row) use ($callback) {
      $row->append($callback($row));
    });
  }

  /**
   * Filters rows with the provided callback.
   */
  function filter($callback) {
    $columns = $this->columns;
    return new Table($columns, new \CallbackFilterIterator($this, function($data) use ($callback, $columns) {
      return $callback(new Row($columns, $data));
    }));
  }
  
  /**
   * Returns a new Table that only has the specified columns, in the specified order. Does not support duplicate column names.
   */
  function withColumns($columns) {
    return $this->transform($columns, function($row) use ($columns) {
      return array_map(function($column) use ($row) {
        return $row->get($column);
      }, $columns);
    });
  }
  
  /** Limits the number of rows. */
  function limit($limit) {
    return new Table($this->columns, new \LimitIterator($this->getInnerIterator(), 0, $limit));
  }

  /**
   * Filters rows using the given column and appropriate query var.
   *
   * If $default_filter is set, the rows will be filtered by its value if the query var is not set.
   */
  function filterByQueryVar($column, $default_filter = false) {
    $query_value = get_query_var(sanitize_title($column));
    if ($query_value === '') {
      if ($default_filter === false) return $this;
      $query_value = $default_filter;
    }
    return $this->filter(function($row) use($column, $query_value) {
      return sanitize_title($row->get($column)) === $query_value;
    });
  }
  
  /**
   * Returns an array of new Tables, one for each value present in the given column.
   *
   * Note that this will load all values into memory for efficiency. This iterator will be reset to the beginning.
   */
  function groupByColumn($column) {
    $iterators = array();
    foreach ($this as $row) {
      $value = $row->get($column);
      if (!isset($iterators[$value])) {
        $iterators[$value] = new Table($this->columns, new \ArrayIterator());
      }
      $iterators[$value]->getInnerIterator()->append($row->getArrayCopy());
    }
    return $iterators;
  }

  /**
   * Groups the table by the given column and then reduces each group back into a single row using
   * the given callback function. The callback function should return the associate array for the
   * reduced (merged) row. It receives the current reduced row (i.e. carry, see array_reduce) and
   * the next row to reduce. The inital carry is an empty array.
   */
  function groupByColumnAndReduce($column, $callback) {
    $groups = $this->groupByColumn($column);
    $result = array();
    foreach ($groups as $value => $rows) {
      $result[] = array_reduce(iterator_to_array($rows), $callback, null)->getArrayCopy();
    }
    return new Table($this->columns, new \ArrayIterator($result));
  }
  
  static function fromCsvFile($file, $delimiter = ',', $utf8encode = false, $skipEmptyRows = true) {
    $csv = new CsvTable($file, $delimiter, $utf8encode, $skipEmptyRows);
    return new Table($csv->columns(), $csv);
  }
  
  static function fromArray($a) {
    return new Table(array_keys(reset($a)), new \ArrayIterator(array_map('array_values', $a)));
  }
}

/**
 * Convenience view of a row that combines the row data with column names.
 */
class Row extends \ArrayIterator {
  private $columns;

  function __construct($columns, $data) {
    $this->columns = $columns;
    parent::__construct($data);
  }

  function key() {
    return $this->columns[parent::key()];
  }
  
  // Returns all offsets (i.e. column IDs) where the column name matches $key.
  function offsets($key) {
    return array_keys($this->columns, $key, true);
  }

  // Returns the first offset (i.e. column ID) with column name $key.
  function offset($key) {
    $keys = $this->offsets($key);
    return count($keys) ? $keys[0] : null;
  }
  
  function has($key) {
    return $this->offset($key) !== null;
  }

  function get($key) {
    return $this->offsetGet($this->offset($key));
  }

  function set($key, $value) {
    return $this->offsetSet($this->offset($key), $value);
  }
  
  /** Modifies all cells with the given column name. The callback receives the current value and a reference to this Row object. */
  function transformColumn($column, $callback) {
    foreach ($this->offsets($column) as $offset) {
      $this->offsetSet($offset, $callback($this->offsetGet($offset), $this));
    }
  }
}
