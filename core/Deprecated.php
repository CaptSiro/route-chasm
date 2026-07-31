<?php

namespace core;

use Closure;
use ReflectionFunction;
use RuntimeException;

class Deprecated extends RuntimeException {
    public function __construct(?Closure $useInstead = null, ?Closure $unsupported = null) {
        if (is_null($useInstead)) {
            parent::__construct("No alternatives for deprecated call");
            return;
        }

        $insteadName = self::describe($useInstead);
        $message = "Use $insteadName() instead";

        if (!is_null($unsupported)) {
            $unsupportedName = self::describe($unsupported);
            $message .= " of $unsupportedName()";
        }

        parent::__construct($message);
    }

    private static function describe(Closure $c): string {
        $r = new ReflectionFunction($c);
        $class = $r->getClosureScopeClass()?->getName();

        return $class
            ? "$class::{$r->getName()}"
            : $r->getName();
    }
}