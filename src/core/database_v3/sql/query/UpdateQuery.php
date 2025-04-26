<?php

namespace core\database_v3\sql\query;

use core\database_v3\sql\Connection;
use core\database_v3\sql\query\clause\Where;
use core\utils\Arrays;
use Exception;

class UpdateQuery implements SqlQuery {
    use Where, AddParameter;

    /**
     * @var array<string, Parameter>
     */
    protected array $set = [];



    public function __construct(
        protected string $table
    ) {}



    public function set(string $column, Parameter $value): static {
        $this->set[$column] = $value;
        return $this;
    }


    public function toQuery(Connection $connection): Query {
        if (empty($this->set)) {
            throw new Exception("Nothing to update in SET clause");
        }

        if (empty($this->where)) {
            throw new Exception("UPDATE must have WHERE clause. It is not safe to leave it out");
        }

        $driver = $connection->getDriver();

        $parameters = [];
        $sql = "UPDATE ". $driver->escapeTable($this->table);
        $sql .= " SET ";

        $this->setParameterAccess(Query::getParameterAccess(Arrays::first($this->where)->condition));

        $first = true;
        foreach ($this->set as $column => $parameter) {
            if (!$first) {
                $sql .= ', ';
            }

            $sql .= $driver->escapeColumn($column) ." = ". $this->addParameter($column, $parameter, $parameters);
            $first = false;
        }

        $this->addWhere($sql, $parameters);

        return new Query($sql, $parameters);
    }
}