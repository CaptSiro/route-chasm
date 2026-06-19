<?php

namespace components\SaveError;

use components\Message\MessageType;
use core\App;
use core\communication\Format;
use core\http\HttpCode;
use core\view\FormatAble;
use core\view\FormatAbleTrait;
use core\view\Formatter;
use core\view\ViewTemplate;

class SaveError implements ViewTemplate, FormatAble {
    use FormatAbleTrait;



    public function __construct(
        protected string $property,
        protected string $message,
        protected int $code = HttpCode::CE_BAD_REQUEST
    ) {
        $this->setFormatter(Formatter::default($this));
    }



    public function getMessage(): string {
        return $this->message;
    }

    public function getProperty(): string {
        return $this->property;
    }



    // FormatAble
    public function render(): string {
        App::getInstance()
            ->getResponse()
            ->setStatus($this->code);

        return $this->renderFormatter();
    }

    public function toText(): string {
        return $this->message;
    }

    public function jsonSerialize(): array {
        return [
            "type" => MessageType::ERROR,
            "message" => $this->message,
            "code" => $this->code,
            "property" => $this->property
        ];
    }
}