<?php

namespace core\tf;

use components\tf\ErrorMessage;
use core\view\View;

readonly class TestResult implements Assertion {
    private int $line;
    private string $message;



    public function __construct(
        private bool $hasPassed,
        array $trace
    ) {
        $this->line = $trace[0]["line"];
    }



    /**
     * @param string $message
     */
    public function setMessage(string $message): void {
        $this->message = $message;
    }

    public function getLine(): int {
        return $this->line;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function result(): bool {
        return $this->hasPassed;
    }

    public function error(): View {
        return new ErrorMessage(
            "[$this->line]",
            $this->message ?? "Assertion has not been passed"
        );
    }
}