<?php

namespace core\database_v3\sql\query\clause;

use core\database_v3\sql\query\Parameter;
use core\database_v3\sql\query\Query;

readonly class WhereClause {
    public const OPERATOR_AND = 'AND';
    public const OPERATOR_OR = 'OR';
    public const OPERATOR_XOR = 'XOR';
    public const OPERATOR_NOR = 'NOR';
    public const OPERATOR_NAND = 'NAND';



    /**
     * @param string $sql
     * @param array<Parameter> $parameters
     * @param string $joinOperator
     * @return WhereClause
     */
    public static function query(string $sql, array $parameters, string $joinOperator = self::OPERATOR_AND): WhereClause {
        return new WhereClause(
            $joinOperator,
            new Query($sql, $parameters)
        );
    }



    public function __construct(
        public string $joinOperator,
        public string|Query $condition
    ) {}
}