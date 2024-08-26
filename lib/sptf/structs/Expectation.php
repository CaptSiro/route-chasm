<?php



namespace sptf\structs;

use Closure;
use sptf\interfaces\Assertion;
use sptf\interfaces\Expect;
use sptf\interfaces\Html;



class Expectation implements Assertion, Expect {
    private mixed $expected;
    private Closure $compare;
    private readonly int $line;



    function __construct(
        private readonly mixed $actual,
        array $trace
    ) {
        $this->line = $trace[0]['line'];
    }



    function toBe(mixed $value): self {
        $this->expected = $value;
        return $this;
    }



    function compare(Closure $compare): self {
        $this->compare = $compare;
        return $this;
    }



    function result(): bool {
        $compare = $this->compare ?? fn($a, $b) => $a === $b;
        return boolval($compare($this->expected, $this->actual));
    }



    function error(): Html {
        return new ExpectationMessage(
            "[$this->line]",
            $this->expected,
            $this->actual,
        );
    }
}