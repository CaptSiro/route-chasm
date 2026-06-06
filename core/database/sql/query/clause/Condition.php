<?php

namespace core\database\sql\query\clause;

use core\database\sql\query\Parameter;
use core\database\sql\query\Query;

readonly class Condition {
    public const OPERATOR_AND = 'AND';
    public const OPERATOR_OR = 'OR';
    public const OPERATOR_XOR = 'XOR';
    public const OPERATOR_NOR = 'NOR';
    public const OPERATOR_NAND = 'NAND';



    /**
     * @param string $sql
     * @param array<Parameter> $parameters
     * @param string $joinOperator
     * @return Condition
     */
    public static function query(string $sql, array $parameters, string $joinOperator = self::OPERATOR_AND): Condition {
        return new Condition(
            $joinOperator,
            new Query($sql, $parameters)
        );
    }



    public function __construct(
        public string $joinOperator,
        public string|Query $condition
    ) {}
}