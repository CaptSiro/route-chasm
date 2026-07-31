<?php

namespace core\view_old;

use Closure;
use core\App;
use core\communication\format\Format;
use core\http\HttpHeader;

class Formatter implements View {
    /**
     * Sets up formatter to render HTML, TEXT, and JSON using to[Type] functions defined on the $item
     *
     * @param FormatAble $item
     * @return self
     *
     * @see Format::IDENT_HTML
     * @see Format::IDENT_TEXT
     * @see Format::IDENT_JSON
     */
    public static function default(FormatAble $item): self {
        return new self(fn(string $format) => match ($format) {
            Format::IDENT_HTML => $item->toHtml(),
            Format::IDENT_TEXT => $item->toText(),
            Format::IDENT_JSON => $item->toJson(),
            Format::IDENT_XML => $item->toXml(),
        });
    }



    /**
     * @param Closure $matcher (string $format) => View, use Format::IDENT_* constants for the parameter
     * @see Format::IDENT_DEFAULT
     */
    public function __construct(
        protected Closure $matcher
    ) {}



    // View
    public function render(): string {
        $response = App::getInstance()
            ->getResponse();

        $format = $response->getFormat();
        $response->setHeader(HttpHeader::CONTENT_TYPE, $format);

        return ($this->matcher)($format);
    }

    public function getRoot(): View {
        return $this;
    }

    public function __toString(): string {
        return $this->render();
    }
}