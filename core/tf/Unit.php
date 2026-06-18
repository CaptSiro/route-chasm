<?php

namespace core\tf;

use Closure;

class Unit {
    public static function expect(mixed $value): Expect {
        Context::assert($ret = new Expectation($value, debug_backtrace()));
        return $ret;
    }

    public static function fail(string $reason = ""): void {
        $result = new TestResult(false, debug_backtrace());

        if ($reason !== "") {
            $result->setMessage($reason);
        }

        Context::assert($result);
    }

    public static function func(Closure $fn, bool $propagateExceptions = false): Func {
        return new Func($fn, $propagateExceptions);
    }

    public static function pass(): void {
        Context::assert(new TestResult(true, debug_backtrace()));
    }
}