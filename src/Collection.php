<?php
declare(strict_types=1);

namespace LogAnalyzer;

use LogAnalyzer\Database\DatabaseInterface;

class Collection implements \Countable, \IteratorAggregate
{
    protected $itemIds;
    protected $database;
    protected $cache = [];

    /**
     * @param int[] $itemIds
     * @param DatabaseInterface $database
     */
    public function __construct(array $itemIds, DatabaseInterface $database)
    {
        $this->itemIds = $itemIds;
        $this->database = $database;
    }

    public function count(): int
    {
        return count($this->itemIds);
    }

    public function dimension($columnName, callable $procedure = null): View
    {
        $itemIdsByValue = [];
        foreach ($this->database->getColumnSubset($columnName, $this->itemIds) as $value => $itemIds) {
            $calcValue = $this->calcValue($value, $procedure);
            $itemIdsByValue[$calcValue] = array_merge($itemIds, $itemIdsByValue[$calcValue] ?? []);
        }

        $collections = [];
        foreach ($itemIdsByValue as $value => $itemIds) {
            $collections[$value] = new self($itemIds, $this->database);
            // For performance, cache dimension value.
            $collections[$value]->cacheColumnValues($columnName, $value);
        }

        return new View($columnName, $collections);
    }

    public function columnValues($columnName)
    {
        if (isset($this->cache[$columnName])) {
            return $this->cache[$columnName];
        }

        $ret = [];
        foreach ($this->itemIds as $itemId) {
            if (!is_null($value = $this->database->getValue($columnName, $itemId))) {
                $ret[] = $value;
            }
        }

        return $ret;
    }

    public function cacheColumnValues($columnName, $cacheValue): void
    {
        $this->cache[$columnName] = $cacheValue;
    }

    public function flushCache(): void
    {
        $this->cache = [];
    }

    public function filter($columnName, callable $procedure): self
    {
        $itemIds = [];
        foreach ($this->itemIds as $itemId) {
            if ($procedure($this->database->getValue($columnName, $itemId)) === true) {
                $itemIds[] = $itemId;
            }
        }

        return new self($itemIds, $this->database);
    }

    protected function calcValue($value, callable $procedure = null)
    {
        if (!is_null($procedure)) {
            $value = $procedure($value) ?? 'null';
        }

        return $value;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->itemIds);
    }
}
