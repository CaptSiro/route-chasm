<?php

namespace sptf\structs;

use sptf\interfaces\Html;

class Interrupt implements Html {
    public function __construct(
        protected string $type,
        protected string $message,
        protected array $trace,
    ) {}

    function html(): string {
        $buffer = "
            <div class='assertion-failed'>$this->type: $this->message</div>";

        foreach ($this->trace as $t) {
            if (isset($t["file"]) && isset($t["line"])) {
                $buffer .= "<div class='index'><span style='width: 1em'></span>at $t[file]:$t[line]</div>";
            }
        }

        return $buffer;
    }
}