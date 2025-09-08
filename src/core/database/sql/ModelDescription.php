<?php

namespace core\database\sql;

use core\database\sql\query\SelectQuery;
use ReflectionClass;
use RuntimeException;

class ModelDescription {
    private static array $descriptions = [];

    public static function extract(string $class): ModelDescription {
        if (isset(self::$descriptions[$class])) {
            return self::$descriptions[$class];
        }

        $reflection = new ReflectionClass($class);
        $tables = $reflection->getAttributes(Table::class);
        if (empty($tables)) {
            throw new RuntimeException("Model '$class' must have Table attribute");
        }

        $databases = $reflection->getAttributes(Database::class);
        $database = empty($databases)
            ? new Database()
            : $databases[0]->newInstance();

        $idColumn = null;
        $columns = [];
        $alias = [];

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Column::class);
            if (empty($attributes)) {
                continue;
            }

            /** @var Column $column */
            $column = $attributes[0]->newInstance();
            $description = new ColumnDescription(
                $property->getName(),
                $column->name ?? $property->getName(),
                $column->type,
                $column->nullable,
                $column->transform
            );

            $columns[] = $description;
            $alias[$description->alias] = $description;

            if ($column->primaryKey) {
                if (!is_null($idColumn)) {
                    throw new RuntimeException("Only one primary key column is allowed for model '$class'");
                }

                $idColumn = $description;
            }
        }

        if (is_null($idColumn)) {
            throw new RuntimeException("No primary key found for model '$class'");
        }

        return self::$descriptions[$class] = new ModelDescription(
            $class,
            $tables[0]->newInstance()->name,
            $database->getConnection(),
            $idColumn,
            $columns,
            $alias
        );
    }



    /**
     * @param string $class
     * @param string $table
     * @param Connection $connection
     * @param ColumnDescription $idColumn
     * @param array<ColumnDescription> $columns
     * @param array<string, ColumnDescription> $alias
     */
    public function __construct(
        public readonly string $class,
        public readonly string $table,
        public readonly Connection $connection,
        public readonly ColumnDescription $idColumn,
        public readonly array $columns,
        public readonly array $alias,
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

    public function getFactory(): ModelFactory {
        return ModelFactory::extract($this->class);
    }

    public function projection(SelectQuery $sql): void {
        $table = $this->getEscapedTable();

        foreach ($this->columns as $column) {
            $sql->projection($table .'.'. $column->name);
        }
    }
}