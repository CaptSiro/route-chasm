<?php

namespace components\tf;

use core\view\Component;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;

class ErrorMessage extends Component {
    public function __construct(
        protected string $hint,
        protected string $message,
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);
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

    public function jsonSerialize(): array {
        return [
            'hint' => $this->hint,
            'message' => $this->message
        ];
    }
}