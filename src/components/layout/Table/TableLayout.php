<?php

namespace components\layout\Table;

use components\layout\Table\Proxy\Proxy;

class TableLayout {
    /**
     * @param array<string, string> $columns
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

    public function createTable(Proxy $proxy): ?Table {
        if (empty($this->columns)) {
            return null;
        }

        $table = new Table($proxy);
        return $table->addAll($this->columns);
    }
}