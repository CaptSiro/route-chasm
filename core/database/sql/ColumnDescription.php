<?php

namespace core\database\sql;

use Closure;

class ColumnDescription {
    public function __construct(
        protected string $alias,
        protected string $name,
        protected string $type,
        protected bool $nullable,
        protected ?Closure $transform
    ) {}



    /**
     * @return string
     */
    public function getAlias(): string {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool {
        return $this->nullable;
    }

    public function transform(mixed $value): mixed {
        if ($this->nullable && empty($value)) {
            return null;
        }

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