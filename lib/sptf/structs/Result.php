<?php

namespace sptf\structs;

use sptf\interfaces\Assertion;
use sptf\interfaces\Html;

readonly class Result implements Assertion {
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

    function result(): bool {
        return $this->hasPassed;
    }

    function error(): Html {
        return new ErrorMessage(
            "[$this->line]",
            $this->message ?? "Assertion has not been passed"
        );
    }
}