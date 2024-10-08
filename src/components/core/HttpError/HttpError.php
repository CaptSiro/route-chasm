<?php

namespace components\core\HttpError;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPageContent;
use core\App;
use core\Response;

class HttpError extends WebPageContent {
    public function __construct(
        protected string $message,
        protected int $code
    ) {
        parent::__construct(head: new HtmlHead("Error - $message"));
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
            Response::TYPE_HTML => parent::render(),
            Response::TYPE_JSON => json_encode([
                "isError" => true,
                "message" => $this->message,
                "code" => $this->code
            ]),
            default => "$this->code: $this->message"
        };
    }
}