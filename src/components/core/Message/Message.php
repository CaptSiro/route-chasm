<?php

namespace components\core\Message;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPageContent;
use core\App;
use core\Response;

class Message extends WebPageContent {
    public function __construct(
        protected string $message
    ) {
        parent::__construct(head: new HtmlHead("$message"));
    }



    public function render(?string $template = null): string {
        $type = App::getInstance()
            ->getRequest()
            ->getResponseType();

        return match ($type) {
            Response::TYPE_HTML => parent::render(),
            Response::TYPE_JSON => json_encode([
                "isError" => false,
                "message" => $this->message
            ]),
            default => $this->message,
        };
    }
}