<?php

namespace components\core\SaveError;

use core\App;
use core\communication\Format;
use core\http\HttpCode;
use core\view\Formatter;
use core\view\Renderer;
use core\view\View;
use JsonSerializable;

class SaveError implements View, JsonSerializable {
    use Renderer;



    protected Formatter $formatter;

    public function __construct(
        protected string $property,
        protected string $message,
        protected int $code = HttpCode::CE_BAD_REQUEST
    ) {
        $this->formatter = new Formatter(fn($type) => match ($type) {
            Format::IDENT_HTML => $this->renderTemplated(),
            Format::IDENT_XML => $this->renderTemplated($this->getResource("SaveError.xml.phtml")),
            Format::IDENT_JSON => json_encode($this),
            default => $this->message
        });
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function render(): string {
        App::getInstance()
            ->getResponse()
            ->setStatus($this->code);

        return $this->formatter->render();
    }

    public function jsonSerialize(): array {
        return [
            "isError" => true,
            "message" => $this->message,
            "code" => $this->code,
            "property" => $this->property
        ];
    }
}