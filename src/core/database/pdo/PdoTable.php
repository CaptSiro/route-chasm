<?php

namespace core\database\pdo;

use core\App;
use core\database\column\PrimaryKey;
use core\database\Database;
use core\database\TableDefinition;

class PdoTable implements TableDefinition {
    public function __construct(
        protected string $name,
        protected array $columns,
        protected ?string $idColumn = null,
        protected ?Database $database = null
    ) {
        if (is_null($this->idColumn) && !empty($this->columns)) {
            $this->idColumn = $this->findIdColumn() ?? array_keys($this->columns)[0];
        }

        if (is_null($this->database)) {
            $this->database = App::getInstance()
                ->getDefaultDatabase();
        }
    }



    protected function findIdColumn(): ?string {
        foreach ($this->columns as $name => $column) {
            if ($column instanceof PrimaryKey) {
                return $name;
            }
        }

        return null;
    }

    public function getTable(): string {
        return $this->name;
    }

    public function getColumns(): array {
        return $this->columns;
    }

    public function getIdColumn(): string {
        return $this->idColumn;
    }

    public function getDatabase(): Database {
        return $this->database;
    }
}