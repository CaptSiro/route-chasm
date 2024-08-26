<?php

namespace sptf\structs;

use sptf\interfaces\Html;

readonly class ErrorMessage implements Html {
    public function __construct(
        protected string $hint,
        protected string $message,
    ) {}

    function html(): string {
        return "<div>
            <div class='index'>$this->hint</div>
            <div class='assertion-failed'>$this->message</div>
        </div>";
    }
}