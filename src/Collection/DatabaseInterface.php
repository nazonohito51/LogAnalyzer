<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection;

use LogAnalyzer\Collection\Column\ColumnFactory;

interface DatabaseInterface
{
    public function addValue($itemId, $columnName, $value): void;
    public function getItemIds($columnName, $value): array;
    public function getValue($itemId, $columnName);
    public function getValues($columnName): array;
    public function getSubset(array $itemIds, $columnName): array;
    public function getScheme(): array;

    public function freeze(): bool;
    public function save(string $saveDir): bool;
    public static function load(string $saveDir, ColumnFactory $factory): self;
}
