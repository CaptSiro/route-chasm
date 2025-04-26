<?php

namespace core\database_v3\sql\query;

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

    public function toQuery(): Query {
        if (empty($this->columns)) {
            throw new Exception("Cannot insert record without specifying columns");
        }

        if (empty($this->values)) {
            throw new Exception("No records to be insert");
        }

        $parameters = [];
        $sql = "INSERT INTO `$this->table`(". join(', ', $this->columns) .') VALUES ';

        foreach ($this->values as $record) {
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
        }

        return new Query($sql, $parameters);
    }
}