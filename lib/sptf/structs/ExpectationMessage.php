<?php

namespace sptf\structs;

use sptf\interfaces\Html;

class ExpectationMessage implements Html {
    public function __construct(
        protected string $hint,
        protected mixed $expected,
        protected mixed $actual,
    ) {}



    function html(): string {
        return "<div>
            <div class='index'>$this->hint</div>
            <div class='expected'>Expected: ". json_encode($this->expected) ."</div>
            <div class='actual'>Got: ". json_encode($this->actual) ."</div>
        </div>";
    }
}