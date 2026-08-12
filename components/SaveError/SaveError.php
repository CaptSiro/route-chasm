<?php

namespace components\SaveError;

use components\Message\MessageType;
use core\http\HttpCode;
use core\view\Component;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;

class SaveError extends Component {
    public function __construct(
        protected string $modelProperty,
        protected string $message,
        protected int $code = HttpCode::CE_BAD_REQUEST,
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);
    }



    public function getMessage(): string {
        return $this->message;
    }

    public function getModelProperty(): string {
        return $this->modelProperty;
    }

    public function toText(): string {
        return $this->message;
    }

    public function jsonSerialize(): array {
        return [
            "type" => MessageType::ERROR,
            "message" => $this->message,
            "code" => $this->code,
            "property" => $this->modelProperty
        ];
    }
}