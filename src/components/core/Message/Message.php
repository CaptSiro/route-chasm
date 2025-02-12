<?php

namespace components\core\Message;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\ContainerContent;
use components\core\WebPage\WebPage;
use core\App;
use core\communication\Format;

class Message extends ContainerContent {
    public function __construct(
        protected string $message
    ) {
        parent::__construct(new WebPage(head: new HtmlHead("$message")));
    }



    public function render(?string $template = null): string {
        return match (App::getInstance()->getResponse()->getFormat()) {
            Format::IDENT_HTML => parent::render(),
            Format::IDENT_JSON => json_encode([
                "isError" => false,
                "message" => $this->message
            ]),
            default => $this->message,
        };
    }
}