<?php

namespace components\layout\Grid;

use components\layout\Grid\Proxy\Proxy;

class GridLayout {
    /**
     * @param array<string, ColumnLayout> $columns
     */
    public function __construct(
        protected array $columns,
        protected ?Proxy $proxy = null
    ) {}

    /**
     * @return array<string, string>
     */
    public function getColumns(): array {
        return $this->columns;
    }

    public function getProxy(): ?Proxy {
        return $this->proxy;
    }

    public function createTable(Proxy $proxy): ?Grid {
        if (empty($this->columns)) {
            return null;
        }

        $table = new Grid($proxy);
        return $table->addAll($this->columns);
    }
}