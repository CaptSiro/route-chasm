<?php

namespace core\database;

interface TableDefinition {
    public function getTableName(): string;

    /**
     * @return array<Column>
     */
    public function getColumns(): array;

    public function getIdColumn(): string;

    public function getDatabase(): Database;
}