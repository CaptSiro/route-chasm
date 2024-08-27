<?php

namespace components\core;

use components\core\CodeError\CodeError;
use components\core\Head\Head;
use components\core\WebPage\WebPage;
use core\App;
use core\ComponentStructure;

class HttpError extends ComponentStructure {
    public function __construct(
        protected readonly string $message,
        protected readonly int $code,
    ) {
        parent::__construct(
            new WebPage(
                "en",
                Head::def("Error - $message"),
                new CodeError($this->message, $this->code)
            )
        );
    }



    public function render(?string $template = null): string {
        $app = App::getInstance();
        $type = $app
            ->getRequest()
            ->getResponseType();

        $app
            ->getResponse()
            ->setStatus($this->code);

        return match ($type) {
            'HTML' => $this->root->render(),
            'JSON' => json_encode([
                "isError" => true,
                "message" => $this->message,
                "code" => $this->code
            ]),
            default => "$this->code: $this->message"
        };
    }
}