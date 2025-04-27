<?php

namespace core\database\sql;

use Closure;

readonly class ColumnDescription {
    public function __construct(
        public string $alias,
        public string $name,
        public string $type,
        protected ?Closure $transform
    ) {}



    public function transform(mixed $value): mixed {
        if (!is_null($this->transform)) {
            return ($this->transform)($value);
        }

        return match ($this->type) {
            "string" => (string) $value,
            "integer" => intval($value),
            "boolean" => boolval($value),
            "float" => floatval($value),
            "double" => doubleval($value),
            "NULL" => null,
        };
    }
}