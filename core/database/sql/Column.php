<?php

namespace core\database\sql;

use Attribute;
use Closure;
use core\database\sql\query\Parameter;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column {
    public const TYPE_STRING = Parameter::TYPE_STRING;
    public const TYPE_DATE = Parameter::TYPE_STRING;
    public const TYPE_DATETIME = Parameter::TYPE_STRING;
    public const TYPE_INTEGER = Parameter::TYPE_INTEGER;
    public const TYPE_LONG = Parameter::TYPE_INTEGER;
    public const TYPE_DOUBLE = Parameter::TYPE_DOUBLE;
    public const TYPE_FLOAT = Parameter::TYPE_FLOAT;
    public const TYPE_BOOLEAN = Parameter::TYPE_BOOLEAN;



    /**
     * @param string|null $name
     * @param string $type
     * @param bool $isPrimaryKey
     * @param bool $nullable
     * @param Closure|null $transform Function signature: fn(mixed $value) => mixed
     */
    public function __construct(
        protected ?string $name = null,
        protected string $type = Parameter::TYPE_INFER,
        protected bool $isPrimaryKey = false,
        protected bool $nullable = false,
        protected ?Closure $transform = null
    ) {}



    public function getName(): ?string {
        return $this->name;
    }

    public function getType(): string {
        return $this->type;
    }

    public function isPrimaryKey(): bool {
        return $this->isPrimaryKey;
    }

    public function isNullable(): bool {
        return $this->nullable;
    }

    public function getTransform(): ?Closure {
        return $this->transform;
    }

    public function transform(mixed $value): mixed {
        return ($this->transform)($value);
    }
}