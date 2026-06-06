<?php

namespace core\database\sql\query;

use core\database\sql\Connection;
use core\database\sql\query\clause\Having;
use core\database\sql\query\clause\JoinClause;
use core\database\sql\query\clause\Limit;
use core\database\sql\query\clause\Offset;
use core\database\sql\query\clause\Where;
use core\utils\Arrays;

class SelectQuery implements Portion, SqlQuery {
    use AddParameter, Where, Having, Limit, Offset;

    protected array $projection = [];
    protected array $from = [];

    /**
     * @var array<JoinClause>
     */
    protected array $joins = [];
    protected ?array $groups;
    protected ?array $orders;
    protected bool $isDistinct = false;



    public function distinct(bool $isDistinct = true): static {
        $this->isDistinct = $isDistinct;
        return $this;
    }

    public function clearProjection(): static {
        $this->projection = [];
        return $this;
    }

    /**
     * Adds column to projection
     *
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
        if (!isset($this->joins)) {
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

    public function naturalJoin(string $table): static {
        return $this->join($table, '1', JoinClause::TYPE_NATURAL);
    }

    /**
     * @param string $column Escaping the column is responsibility of the caller
     * @return $this
     */
    public function group(string $column): static {
        Arrays::push($this->groups, $column);
        return $this;
    }

    /**
     * @param string $column Escaping the column is responsibility of the caller
     * @return $this
     */
    public function order(string $column, string $order = 'ASC'): static {
        Arrays::push($this->orders, $column .' '. $order);
        return $this;
    }

    public function fetch(Connection $connection): ?array {
        return $connection->fetch($this->toQuery($connection));
    }

    public function fetchAll(Connection $connection): array {
        return $connection->fetchAll($this->toQuery($connection));
    }



    // SqlQuery
    public function toQuery(Connection $connection): Query {
        if (!empty($this->where)) {
            $this->setParameterAccess(Query::getParameterAccess(
                Arrays::first($this->where)->condition)
            );
        }

        $parameters = [];
        $sql = 'SELECT ';

        if ($this->isDistinct) {
            $sql .= 'DISTINCT ';
        }

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

        if (isset($this->groups)) {
            $sql .= ' GROUP BY ' . join(', ', $this->groups);
        }

        $this->addHaving($sql, $parameters);

        if (isset($this->orders)) {
            $sql .= ' ORDER BY ' . join(', ', $this->orders);
        }

        $this->addLimit($sql, $parameters);
        $this->addOffset($sql, $parameters);

        return new Query($sql, $parameters);
    }
}