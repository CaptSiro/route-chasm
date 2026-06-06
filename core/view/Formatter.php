<?php

namespace core\view;

use Closure;
use core\App;
use core\http\HttpHeader;

class Formatter implements View {
    public function __construct(
        protected Closure $matcher
    ) {}



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