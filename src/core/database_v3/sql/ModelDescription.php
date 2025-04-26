<?php

namespace core\database_v3\sql;

readonly class ModelDescription {
    /**
     * @param string $table
     * @param Connection $connection
     * @param ColumnDescription $idColumn
     * @param array<ColumnDescription> $columns
     * @param array<string, ColumnDescription> $alias
     */
    public function __construct(
        public string $table,
        public Connection $connection,
        public ColumnDescription $idColumn,
        public array $columns,
        public array $alias,
    ) {}

    public function getEscapedTable(): string {
        return $this->connection->getDriver()->escapeTable(
            $this->table
        );
    }

    public function getEscapedIdColumnName(): string {
        return $this->connection->getDriver()->escapeColumn(
            $this->idColumn->name
        );
    }
}