<?php

namespace retval;

use Closure;
use JsonSerializable;

class ResultSet implements JsonSerializable {
    private $success, $failures;



    public function __construct(?array $success, ?array $failures) {
        $this->success = empty($success) ? null : $success;
        $this->failures = empty($failures) ? null : $failures;
    }



    public function getSuccess(): ?array {
        return $this->success;
    }



    public function getFailures(): ?array {
        return $this->failures;
    }



    public function isSuccess(): bool {
        return isset($this->success);
    }



    public function strip(Closure $failFN) {
        if ($this->isFailure()) {
            return $failFN($this->failures);
        }

        return $this->success;
    }



    public function isFailure(): bool {
        return isset($this->failure);
    }



    public function jsonSerialize(): object {
        return (object)[
            "success" => $this->success,
            "failures" => $this->failures
        ];
    }
}