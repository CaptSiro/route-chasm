<?php

namespace core\database\sql\query\clause;

use core\database\sql\query\Parameter;
use core\database\sql\query\Portion;

trait Offset {
    protected int $offset = -1;

    public function offset(int $offset): static {
        $this->offset = $offset;
        return $this;
    }

    protected function addOffset(string &$sql, array &$parameters): void {
        if ($this->offset <= 0) {
            return;
        }

        $p = $this->addParameter(
            Portion::PARAMETER_OFFSET,
            Parameter::infer($this->offset),
            $parameters
        );

        $sql .= ' OFFSET '. $p;
    }
}