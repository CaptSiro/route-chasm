<?php

namespace components\tf;

use core\view\FormatAble;
use core\view\FormatAbleTrait;

class Interrupt implements FormatAble {
    use FormatAbleTrait;



    public function __construct(
        protected string $type,
        protected string $message,
        protected array $trace,
    ) {}



    public function getType(): string {
        return $this->type;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function getTrace(): array {
        return $this->trace;
    }

    public function toText(): string {
        return "[$this->type]: $this->message";
    }

    public function jsonSerialize(): array {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'trace' => $this->trace
        ];
    }
}