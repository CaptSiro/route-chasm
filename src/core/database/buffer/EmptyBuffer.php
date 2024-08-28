<?php

namespace core\database\buffer;

use BadFunctionCallException;
use core\database\parameter\Param;

class EmptyBuffer implements Buffer {
    function add(Param $value): Buffer {
        throw new BadFunctionCallException("Cannot modify empty buffer");
    }

    function shift(): Param {
        throw new BadFunctionCallException("Cannot get parameter from empty buffer");
    }

    function isEmpty(): bool {
        return true;
    }

    function dump(): array {
        return [];
    }

    function load(array $values): Buffer {
        throw new BadFunctionCallException("Cannot modify empty buffer");
    }
}