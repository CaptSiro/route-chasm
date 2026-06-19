<?php

namespace core\database\sql\query;

use core\database\sql\Connection;
use core\database\sql\Driver;
use core\database\sql\SideEffect;
use Exception;

class InsertQuery implements SqlQuery {
    protected array $columns;

    /**
     * @var array<array<Parameter>>
     */
    protected array $values = [];



    public function __construct(
        protected string $table
    ) {}



    public function columns(array $columns): static {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @param array<Parameter> $record
     * @return $this
     */
    public function value(array $record): static {
        $this->values[] = $record;
        return $this;
    }

    protected function generateColumnList(Driver $driver): string {
        $list = '';
        $first = true;

        foreach ($this->columns as $column) {
            if (!$first) {
                $list .= ', ';
            }

            $list .= $driver->escapeColumn($column);
            $first = false;
        }

        return $list;
    }

    public function empty(): bool {
        return empty($this->values);
    }
    
    public function run(Connection $connection): SideEffect {
        return $connection->run($this->toQuery($connection));
    }



    // SqlQuery
    public function toQuery(Connection $connection): Query {
        if (empty($this->columns)) {
            throw new Exception("Cannot insert record without specifying columns");
        }

        if (empty($this->values)) {
            throw new Exception("No records to be insert");
        }

        $driver = $connection->getDriver();

        $parameters = [];
        $sql = "INSERT INTO ". $driver->escapeTable($this->table)
            ."(". $this->generateColumnList($driver) .') VALUES ';

        $firstValue = true;
        foreach ($this->values as $record) {
            if (!$firstValue) {
                $sql .= ',';
            }

            $sql .= '(';

            $first = true;
            foreach ($record as $parameter) {
                if (!$first) {
                    $sql .= ', ';
                }

                $parameters[] = $parameter;

                $sql .= '?';
                $first = false;
            }

            $sql .= ')';
            $firstValue = false;
        }

        return new Query($sql, $parameters);
    }
}