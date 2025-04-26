<?php

namespace core\database_v3\sql;

use Attribute;
use core\database_v3\sql\query\Parameter;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Column {
    public const TYPE_STRING = Parameter::TYPE_STRING;
    public const TYPE_INTEGER = Parameter::TYPE_INTEGER;
    public const TYPE_DOUBLE = Parameter::TYPE_DOUBLE;
    public const TYPE_FLOAT = Parameter::TYPE_FLOAT;
    public const TYPE_BOOLEAN = Parameter::TYPE_BOOLEAN;



    public function __construct(
        public string $name,
        public string $type,
        public bool $primaryKey = false
    ) {}
}