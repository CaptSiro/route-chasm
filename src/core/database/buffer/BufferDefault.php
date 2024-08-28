<?php

namespace core\database\buffer;

use core\database\parameter\Param;

trait BufferDefault {
    /**
     * @var array<Param> $buffer
     */
    private array $buffer = [];




    public function add(Param $value): self {
        $this->buffer[] = $value;
        return $this;
    }

    public function shift(): Param {
        return array_shift($this->buffer);
    }

    public function isEmpty(): bool {
        return empty($this->buffer);
    }

    function dump(): array {
        $temp = $this->buffer;
        $this->buffer = [];
        return $temp;
    }

    /**
     * @param array<Param> $values
     * @return QueryBuffer|BufferDefault|ParamBuffer
     */
    function load(array $values): self {
        $this->buffer = $values;
        return $this;
    }
}