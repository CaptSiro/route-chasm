<?php

namespace core\database_v3\sql\query\clause;

use core\database_v3\sql\query\Parameter;
use core\database_v3\sql\query\Portion;

trait Limit {
    protected int $n = -1;

    public function limit(int $n): static {
        $this->n = $n;
        return $this;
    }

    public function count(int $n): static {
        $this->n = $n;
        return $this;
    }

    protected function addLimit(string &$sql, array &$parameters): void {
        if ($this->n <= 0) {
            return;
        }

        $p = $this->addParameter(
            Portion::PARAMETER_COUNT,
            Parameter::infer($this->n),
            $parameters
        );

        $sql .= ' LIMIT '. $p;
    }
}