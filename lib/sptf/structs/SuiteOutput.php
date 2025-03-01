<?php

namespace sptf\structs;

class SuiteOutput {
    public function __construct(
        protected bool $wasPrintingAllowed,
        protected string $output
    ) {}

    public function __toString(): string {
        if (!$this->wasPrintingAllowed) {
            return '';
        }

        return $this->output;
    }
}