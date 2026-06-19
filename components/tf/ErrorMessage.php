<?php

namespace components\tf;

use core\view\FormatAble;
use core\view\FormatAbleTrait;
use core\view\Formatter;

class ErrorMessage implements FormatAble {
    use FormatAbleTrait;




    public function __construct(
        protected string $hint,
        protected string $message,
    ) {
        $this->setFormatter(Formatter::default($this));
    }



    public function getHint(): string {
        return $this->hint;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function toText(): string {
        return $this->message . ' (' . $this->hint . ')';
    }

    // JsonSerializable
    public function jsonSerialize(): array {
        return [
            'hint' => $this->hint,
            'message' => $this->message
        ];
    }
}