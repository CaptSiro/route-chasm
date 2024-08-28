<?php

namespace core\database;

use core\database\buffer\StaticBuffer;
use core\database\column\Column;
use core\database\column\ForeignKey;
use core\database\parameter\Primitive;
use core\database\query\Query;
use core\Init;

abstract class Table extends Init {
    abstract public static function getTable(): string;

    /**
     * @return array<Column>
     */
    abstract public static function getColumns(): array;



    public static function init(): void {}

    public static function getIdColumn(): string {
        return 'id';
    }

    public static function getColumnEnumString(bool $includeIdColumn = true): string {
        $table = '`'. static::getTable() .'`';
        $string = $includeIdColumn
            ? "$table.`". static::getIdColumn() .'`'
            : "";

        $first = !$includeIdColumn;
        foreach (static::getColumns() as $name => $definition) {
            if ($definition->isVirtual()) {
                continue;
            }

            if (!$first) {
                $string .= ', ';
            }

            $string .= "$table.`$name`";

            $first = false;
        }

        return $string;
    }



    public static function foreignKey(string $alias): ForeignKey {
        return new ForeignKey(static::class, $alias);
    }

    public static function fetch(string|Query|null $additional = null): self {
        $sql = "SELECT ". static::getColumnEnumString() ." FROM `". static::getTable(). "`";
        return Database::getInstance()
            ->fetch(Query::from($sql, $additional), static::class);
    }

    public static function fetchAll(?string $additional = null) {
        $sql = "SELECT ". static::getColumnEnumString() ." FROM `". static::getTable(). "`";
        return Database::getInstance()
            ->fetchAll(Query::from($sql, $additional), static::class);
    }



    public static function fromId(int $id): ?self {
        if ($id === 0) {
            return new static();
        }

        $_id = new Primitive($id);
        return Database::getInstance()
            ->fetch(
                "SELECT ". static::getColumnEnumString() ." FROM `". static::getTable()
                ."` WHERE `". static::getIdColumn() ."` = $_id",
                static::class
            );
    }

    public static function fromRow(array|false $row): ?self {
        if ($row === false) {
            return null;
        }

        $self = new static();
        $self->data = $row;
        return $self;
    }




    private array $data = [];
    /**
     * @var array<string> $updated
     */
    private array $updated = [];
    private int $id;



    public function __get(string $name): mixed {
        if (!isset(static::getColumns()[$name])) {
            return null;
        }

        $column = static::getColumns()[$name];
        if ($column instanceof ForeignKey) {
            $name = $column->getAlias();
        }

        return $column->transform($this->data[$name]);
    }

    public function set(array $data): self {
        foreach ($data as $column => $value) {
            $this->data[$column] = $value;
            $this->updated[] = $column;
        }

        return $this;
    }

    public function save(): void {
        if (empty($this->updated)) {
            return;
        }

        if ($this->getId() === 0) {
            $this->insert();
            return;
        }

        $sql = "UPDATE `". static::getTable() ."` SET ";
        $params = [];
        $first = true;

        foreach (array_unique($this->updated) as $column) {
            if (!$first) {
                $sql .= ', ';
            }

            $sql .= "`$column` = ". StaticBuffer::PARAM_IDENT;
            $params[] = $this->data[$column];

            $first = false;
        }

        $sql .= ' WHERE `'. static::getIdColumn() .'` = '. $this->getId();

        Database::getInstance()
            ->run(new Query($sql, StaticBuffer::from($params)));
    }

    protected function insert(): void {
        $columns = [];
        $params = [];
        $values = "";
        $first = true;

        foreach (static::getColumns() as $name => $definition) {
            if ($definition->isVirtual()) {
                continue;
            }

            if (!$first) {
                $values .= ', ';
            }

            $columns[] = "`$name`";
            $params[] = $this->data[$name];
            $values .= StaticBuffer::PARAM_IDENT;

            $first = false;
        }

        $sql = 'INSERT INTO `'. static::getTable() ."` (". implode(', ', $columns) .") VALUES (". $values .")";
        Database::getInstance()
            ->run(new Query($sql, StaticBuffer::from($params)));
    }

    public function delete(): void {
        $_id = new Primitive($this->getId());
        Database::getInstance()
            ->run("DELETE FROM `". static::getTable() ."` WHERE `". static::getIdColumn() ."` = $_id");
    }

    public function getId(): int {
        if (isset($this->id)) {
            return $this->id;
        }

        return $this->id = $this->data[static::getIdColumn()] ?? 0;
    }
}