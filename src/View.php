<?php
namespace LogAnalyzer;

use LogAnalyzer\CollectionBuilder\Collection;
use LucidFrame\Console\ConsoleTable;

class View implements \Countable
{
    const COUNT_COLUMN = '_count';

    private $dimension;
    private $columns;

    /**
     * @var Collection[]
     */
    private $collections;

    public function __construct($dimension, array $collections)
    {
        $this->dimension = $dimension;
        $this->columns[$dimension] = $dimension;
        $this->columns['Count'] = self::COUNT_COLUMN;
        $this->collections = $collections;
    }

    public function addColumn($column_name, callable $calc_column = null)
    {
        $this->columns[$column_name] = !is_null($calc_column) ? $calc_column : $column_name;

        return $this;
    }

    public function display(array $options = [])
    {
        $table = new ConsoleTable();
        $str_length = isset($options['length']) ? $options['length'] : null;
        $sort = isset($options['sort']) ? $options['sort'] : null;
        $where = isset($options['where']) ? $options['where'] : null;

        foreach ($this->columns as $column_name => $calc_column) {
            $table->addHeader($column_name);
        }
        foreach ($this->toArray($sort, $where) as $row) {
            $table->addRow();
            foreach ($this->columns as $column_name => $calc_column) {
                $trimmed_value = $this->formatColumnValue($row[$column_name], $str_length);
                $table->addColumn($trimmed_value);
            }
        }

        $table->display();
    }

    public function toArray(callable $sort = null, callable $where = null)
    {
        $ret = [];
        foreach ($this->collections as $dimension_value => $collection) {
            $row = [];
            foreach ($this->columns as $column_name => $calc_column) {
                if ($column_name == $this->dimension) {
                    $row[$column_name] = $dimension_value;
                } elseif ($calc_column == self::COUNT_COLUMN) {
                    $row[$column_name] = count($collection);
                } elseif (is_callable($calc_column)) {
                    $row[$column_name] = $collection->sum($calc_column);
                } else {
                    $row[$column_name] = array_unique($collection->sum($calc_column));
                }
            }
            $ret[] = $row;
        }

        if ($where) {
            // array_values will number index again.
            $ret = array_values(array_filter($ret, $where));
        }
        if ($sort) {
            usort($ret, $sort);
        }

        return $ret;
    }

    public function count()
    {
        return count($this->collections);
    }

    public function getCollection($dimension_value)
    {
        return isset($this->collections[$dimension_value]) ? $this->collections[$dimension_value] : null;
    }

    /**
     * @param string|array $column_value
     * @param int $str_length
     * @return bool|string
     */
    private function formatColumnValue($column_value, $str_length = null)
    {
        if (is_array($column_value)) {
            if (count($column_value) > 1) {
                $column_value = '[' . implode(', ', $column_value) . ']';
            } else {
                $column_value = $column_value[0];
            }
        }

        if ($str_length && strlen($column_value) > $str_length) {
            $column_value = substr($column_value, 0, $str_length) . '...';
        }

        return $column_value;
    }
}
