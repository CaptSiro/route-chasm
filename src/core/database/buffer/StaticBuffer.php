<?php

namespace core\database\buffer;

use core\database\Param;
use core\database\pdo\parameter\PdoPrimitiveParam;

class StaticBuffer implements Buffer {
    public const PARAM_IDENT = PdoPrimitiveParam::IDENT;

    public static function from(array $values): self {
        return new self(array_map(fn($x) => new PdoPrimitiveParam($x), $values));
    }



    public function __construct(
        protected array $params
    ) {}



    function add(Param $value): Buffer {
        throw new BufferMutationNotAllowedException();
    }

    function shift(): Param {
        return array_shift($this->params);
    }

    function isEmpty(): bool {
        return empty($this->params);
    }

    function dump(): array {
        return $this->params;
    }

    function load(array $values): Buffer {
        throw new BufferMutationNotAllowedException();
    }
}