<?php

namespace sptf\structs;

use sptf\interfaces\Html;

class ExpectationMessage implements Html {
    public function __construct(
        protected string $hint,
        protected mixed $expected,
        protected mixed $actual,
    ) {}



    public function getHint(): string {
        return $this->hint;
    }

    public function getActual(): mixed {
        return $this->actual;
    }

    public function getExpected(): mixed {
        return $this->expected;
    }

    function html(): string {
        return "<div>
            <div class='index'>$this->hint</div>
            <div class='expected'>Expected: ". htmlspecialchars(json_encode($this->expected)) ."</div>
            <div class='actual'>Got: ". htmlspecialchars(json_encode($this->actual)) ."</div>
        </div>";
    }
}