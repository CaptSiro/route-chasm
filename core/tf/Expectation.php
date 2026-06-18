<?php



namespace core\tf;

use Closure;
use components\tf\ExpectationMessage;
use core\view\View;



class Expectation implements Assertion, Expect {
    protected mixed $expected;

    protected Closure $compare;

    protected readonly int $line;



    function __construct(
        private readonly mixed $actual,
        array $trace
    ) {
        $this->line = $trace[0]['line'];
    }



    public function getLine(): int {
        return $this->line;
    }

    public function getActual(): mixed {
        return $this->actual;
    }

    public function getExpected(): mixed {
        return $this->expected;
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


    function error(): View {
        return new ExpectationMessage(
            "[$this->line]",
            $this->expected,
            $this->actual,
        );
    }
}