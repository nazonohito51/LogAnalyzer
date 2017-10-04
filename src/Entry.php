<?php
namespace LogAnalyzer;

class Entry implements EntryInterface
{
    private $attributes = [];

    public function __construct($iterable)
    {
        foreach ($iterable as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    public function __get($name)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return null;
    }
}
