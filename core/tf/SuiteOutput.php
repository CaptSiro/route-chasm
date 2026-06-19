<?php

namespace core\tf;

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



    public function isWasPrintingAllowed(): bool {
        return $this->wasPrintingAllowed;
    }

    public function getOutput(): string {
        return $this->output;
    }
}