<?php

namespace core\database\sql\query;

use core\database\sql\Connection;
use core\database\sql\query\clause\Where;
use core\database\sql\SideEffect;
use core\utils\Arrays;
use Exception;

class UpdateQuery implements SqlQuery {
    use Where, AddParameter;

    /**
     * @var array<string, string|Query|Parameter>
     */
    protected array $set = [];



    public function __construct(
        protected string $table
    ) {}



    /**
     * @param string $column Column is escaped automatically on query string creation
     * @param string|Query|Parameter $value
     * @return $this
     */
    public function set(string $column, string|Query|Parameter $value): static {
        $this->set[$column] = $value;
        return $this;
    }

    public function run(Connection $connection): SideEffect {
        return $connection->run($this->toQuery($connection));
    }



    // SqlQuery
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

            $first = false;
            $sql .= $driver->escapeColumn($column) ." = ";

            if ($parameter instanceof Parameter) {
                $sql .= $this->addParameter($column, $parameter, $parameters);
                continue;
            }

            $query = Query::resolve($parameter);
            $sql .= $query->getSql();

            foreach ($query->getParameters() as $name => $p) {
                $this->addParameter((string) $name, $p, $parameters);
            }
        }

        $this->addWhere($sql, $parameters);

        return new Query($sql, $parameters);
    }
}