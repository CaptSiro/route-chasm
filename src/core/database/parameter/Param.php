<?php

namespace core\database\parameter;

interface Param {
    /**
     * Return identifier to add to database query
     *
     * @return string
     */
    function __toString(): string;

    function stringify(): string;

    /**
     * Return value to be used in database query
     *
     * @return mixed
     */
    function getValue(): mixed;

    /**
     * Return any of PDO::PARAM_* constants
     *
     * @return int
     */
    function getType(): int;

    /**
     * Return string that was returned by `__toString()` method or null to use indexed based binding (`"?"` was returned)
     */
    function getName(): ?string;
}