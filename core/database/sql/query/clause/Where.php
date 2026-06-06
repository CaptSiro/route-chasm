<?php

namespace core\database\sql\query\clause;

use core\database\sql\query\Query;

trait Where {
    /**
     * @var array<Condition>
     */
    protected array $where = [];

    public function where(string|Query $condition, string $operator = Condition::OPERATOR_AND): static {
        if (!isset($this->where)) {
            $this->where = [];
        }

        $this->where[] = new Condition($operator, $condition);
        return $this;
    }

    protected function addWhere(string &$sql, array &$parameters): void {
        if (empty($this->where)) {
            return;
        }

        $sql .= ' WHERE';
        $first = true;

        foreach ($this->where as $clause) {
            if (!$first) {
                $sql .= ' '. strtoupper($clause->joinOperator);
            }

            $sql .= ' '. $clause->condition;

            $p = Query::unwrapParameters($clause->condition);
            if (!empty($p)) {
                $parameters = array_merge($parameters, $p);
            }

            $first = false;
        }
    }
}