<?php

namespace components\core\SaveException;

use core\App;
use core\communication\Format;
use core\http\HttpCode;
use core\view\Formatter;
use core\view\Renderer;
use core\view\View;

class SaveError implements View {
    use Renderer;



    protected Formatter $formatter;

    public function __construct(
        protected string $property,
        protected string $message,
        protected int $code = HttpCode::CE_BAD_REQUEST
    ) {
        $this->formatter = new Formatter(fn($type) => match ($type) {
            Format::IDENT_HTML => $this->renderTemplated(),
            Format::IDENT_XML => $this->renderTemplated($this->getResource("SaveException.xml.phtml")),
            Format::IDENT_JSON => json_encode([
                "isError" => true,
                "message" => $this->message,
                "code" => $this->code,
                "property" => $this->property
            ]),
            default => $this->message
        });
    }

    public function render(): string {
        App::getInstance()
            ->getResponse()
            ->setStatus($this->code);

        return $this->formatter->render();
    }
}