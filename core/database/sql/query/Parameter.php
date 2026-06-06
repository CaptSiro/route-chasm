<?php

namespace core\database\sql\query;

readonly class Parameter {
    public const TYPE_INFER = "infer";
    public const TYPE_STRING = "string";
    public const TYPE_BOOLEAN = "boolean";
    public const TYPE_INTEGER = "integer";
    public const TYPE_FLOAT = "float";
    public const TYPE_DOUBLE = "double";
    public const TYPE_NULL = "NULL";

    public static function infer(mixed $value): Parameter {
        return new Parameter($value, gettype($value));
    }



    protected string $type;

    public function __construct(
        protected mixed $value,
        string $type
    ) {
        $this->type = $type === self::TYPE_INFER
            ? gettype($this->value)
            : $type;
    }



    public function getType(): string {
        return $this->type;
    }

    public function getValue(): mixed {
        return $this->value;
    }
}