<?php

namespace components\core\Message;

use core\communication\Format;
use core\view\Formatter;
use core\view\Renderer;
use core\view\View;
use JsonSerializable;

class Message implements View, JsonSerializable {
    use Renderer;



    protected Formatter $formatter;

    public function __construct(
        protected string $message
    ) {
        $this->formatter = new Formatter(fn($type) => match ($type) {
            Format::IDENT_HTML => $this->renderTemplated(),
            Format::IDENT_XML => "<message>$this->message</message>",
            Format::IDENT_JSON => json_encode($this),
            default => $this->message
        });
    }



    public function jsonSerialize(): array {
        return ['message' => $this->message];
    }

    public function render(): string {
        return $this->formatter->render();
    }
}