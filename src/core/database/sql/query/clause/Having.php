<?php

namespace core\database\sql\query\clause;

use core\database\sql\query\Query;

trait Having {
    /**
     * @var array<Condition>
     */
    protected array $having = [];

    public function having(string|Query $condition, string $operator = Condition::OPERATOR_AND): static {
        if (!isset($this->having)) {
            $this->having = [];
        }

        $this->having[] = new Condition($operator, $condition);
        return $this;
    }

    protected function addHaving(string &$sql, array &$parameters): void {
        if (empty($this->having)) {
            return;
        }

        $sql .= ' HAVING';
        $first = true;

        foreach ($this->having as $clause) {
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