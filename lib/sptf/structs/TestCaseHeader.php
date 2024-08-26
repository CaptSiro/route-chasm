<?php

namespace sptf\structs;

use sptf\interfaces\Html;

readonly class TestCaseHeader implements Html {
    public function __construct(
        protected bool $outcome,
        protected string $name,
        protected float $time
    ) {}

    public function html(): string {
        $outcomeText = $this->outcome ? "PASS" : "FAIL";
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