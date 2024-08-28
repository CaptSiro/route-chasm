<?php

namespace core\database\buffer;

use core\database\parameter\Param;

interface Buffer {
    function add(Param $value): self;

    function shift(): Param;

    function isEmpty(): bool;

    /**
     * @return array<Param>
     */
    function dump(): array;

    /**
     * @param array<Param> $values
     * @return self
     */
    function load(array $values): self;
}