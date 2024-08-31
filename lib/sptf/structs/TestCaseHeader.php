<?php

namespace sptf\structs;

use sptf\interfaces\Html;
use sptf\TestOutcome;

readonly class TestCaseHeader implements Html {
    public function __construct(
        protected TestOutcome $outcome,
        protected string $name,
        protected float $time
    ) {}

    public function html(): string {
        $outcomeText = $this->outcome->value;
        $time = sprintf("%.02f s", $this->time);

        return "
            <div>
                <span class='outcome'>$outcomeText</span>
                <span class='time'>$time</span>
                <span class='name'>$this->name</span>
            </div>
        ";
    }
}