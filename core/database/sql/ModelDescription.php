<?php

namespace core\database\sql;

use core\database\ModelType;
use core\database\sql\query\SelectQuery;
use core\utils\Strings;
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

        $modelTypes = $reflection->getAttributes(ModelType::class);
        $modelType = empty($modelTypes)
            ? Strings::pascalToKebab($reflection->getShortName())
            : $modelTypes[0]->newInstance()->getType();

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
                $column->getName() ?? $property->getName(),
                $column->getType(),
                $column->isNullable(),
                $column->getTransform()
            );

            $columns[] = $description;
            $alias[$description->getAlias()] = $description;

            if ($column->isPrimaryKey()) {
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
            $modelType,
            $tables[0]->newInstance()->name,
            $database->getConnection(),
            $idColumn,
            $columns,
            $alias
        );
    }



    protected string $escapedTable;

    /**
     * @param string $class
     * @param string $modelType
     * @param string $table
     * @param Connection $connection
     * @param ColumnDescription $idColumn
     * @param array<ColumnDescription> $columns
     * @param array<string, ColumnDescription> $alias
     */
    public function __construct(
        protected string $class,
        protected string $modelType,
        protected string $table,
        protected Connection $connection,
        protected ColumnDescription $idColumn,
        protected array $columns,
        protected array $alias,
    ) {
        $this->escapedTable = $this->connection->getDriver()->escapeTable(
            $this->table
        );
    }



    public function getClass(): string {
        return $this->class;
    }

    public function getModelType(): string {
        return $this->modelType;
    }

    public function getTable(): string {
        return $this->table;
    }

    public function getConnection(): Connection {
        return $this->connection;
    }

    public function getIdColumn(): ColumnDescription {
        return $this->idColumn;
    }

    /**
     * @return array<ColumnDescription>
     */
    public function getColumns(): array {
        return $this->columns;
    }

    /**
     * @return array<string, ColumnDescription>
     */
    public function getAlias(): array {
        return $this->alias;
    }

    public function getEscapedTable(): string {
        return $this->escapedTable;
    }

    public function getEscapedColumn(string $column): string {
        return "$this->escapedTable.". $this->connection->getDriver()->escapeColumn($column);
    }

    public function getEscapedIdColumnName(): string {
        return $this->connection->getDriver()->escapeColumn(
            $this->idColumn->getName()
        );
    }

    public function getFactory(): ModelFactory {
        return ModelFactory::extract($this->class);
    }

    public function getColumnAlias(): array {
        return array_map(
            fn(ColumnDescription $x) => $x->getAlias(),
            $this->columns
        );
    }

    public function projection(SelectQuery $sql): void {
        $table = $this->getEscapedTable();

        foreach ($this->columns as $column) {
            $sql->projection($table .'.'. $column->getName());
        }
    }
}