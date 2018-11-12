<?php
declare(strict_types=1);

namespace LogAnalyzer\Collection\Column;

use LogAnalyzer\Collection\Column\FileStorageColumn\ValueStore;

class ColumnFactory
{
    public function build($saveDir = ''): ColumnInterface
    {
        if (empty($saveDir)) {
            $saveDir = __DIR__ . '/../../../storage/';
        }

        return new FileStorageColumn($saveDir, new ValueStore());
    }
}