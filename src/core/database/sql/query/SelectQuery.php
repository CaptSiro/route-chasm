<?php

namespace core\database\sql\query;

use core\database\sql\Connection;
use core\database\sql\query\clause\JoinClause;
use core\database\sql\query\clause\Limit;
use core\database\sql\query\clause\Offset;
use core\database\sql\query\clause\Where;
use core\utils\Arrays;

class SelectQuery implements Portion, SqlQuery {
    use AddParameter, Where, Limit, Offset;

    protected array $projection = [];
    protected array $from = [];

    /**
     * @var array<JoinClause>
     */
    protected array $joins = [];



    /**
     * @param string $column Escaping the column is responsibility of the caller
     * @return $this
     */
    public function projection(string $column): static {
        Arrays::push($this->projection, $column);
        return $this;
    }

    /**
     * @param string $table Escaping the table is responsibility of the caller
     * @return $this
     */
    public function from(string $table): static {
        $this->from[] = $table;
        return $this;
    }

    public function join(string $table, string|Query $condition, string $type = JoinClause::TYPE_INNER): static {
        if (isset($this->joins)) {
            $this->joins = [];
        }

        $this->joins[] = new JoinClause($type, $table, $condition);
        return $this;
    }

    public function leftJoin(string $table, string|Query $condition): static {
        return $this->join($table, $condition, JoinClause::TYPE_LEFT);
    }

    public function rightJoin(string $table, string|Query $condition): static {
        return $this->join($table, $condition, JoinClause::TYPE_RIGHT);
    }



    public function toQuery(Connection $connection): Query {
        if (!empty($this->where)) {
            $this->setParameterAccess(Query::getParameterAccess(
                Arrays::first($this->where)->condition)
            );
        }

        $parameters = [];
        $sql = 'SELECT ';

        if (isset($this->projection)) {
            $sql .= join(', ', $this->projection);
        } else {
            $sql .= ' * ';
        }

        $sql .= ' FROM ' . join(', ', $this->from);

        foreach ($this->joins as $join) {
            $sql .= ' '. $join->getSql();

            $p = $join->getParameters();
            if (!empty($p)) {
                $parameters = array_merge($parameters, $p);
            }
        }

        $this->addWhere($sql, $parameters);
        $this->addLimit($sql, $parameters);
        $this->addOffset($sql, $parameters);

        return new Query($sql, $parameters);
    }
}