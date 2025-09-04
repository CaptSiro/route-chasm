<?php

namespace core\database\sql\query;

readonly class Query {
    public static function static(string $sql): Query {
        return new Query($sql, []);
    }

    public static function infer(string $sql, mixed ...$parameters): Query {
        foreach ($parameters as $i => $parameter) {
            $parameters[$i] = Parameter::infer($parameter);
        }

        return new Query($sql, $parameters);
    }

    public static function unwrapParameters(string|Query $query): array {
        if ($query instanceof Query) {
            return $query->parameters;
        }

        return [];
    }

    public static function getParameterAccess(null|string|Query $query): ParameterAccess {
        if (!($query instanceof Query) || empty($query->parameters)) {
            return ParameterAccess::POSITION;
        }

        return gettype(array_key_first($query->parameters)) === "string"
            ? ParameterAccess::NAME
            : ParameterAccess::POSITION;
    }



    /**
     * @param string $sql
     * @param array<string|int, Parameter> $parameters
     */
    public function __construct(
        public string $sql,
        public array $parameters
    ) {}



    public function __toString(): string {
        return $this->sql;
    }
}