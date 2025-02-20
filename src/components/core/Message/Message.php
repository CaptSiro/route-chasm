<?php

namespace components\core\Message;

use components\core\Html\Html;
use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPage;
use core\App;
use core\communication\Format;
use core\view\ContainerContent;

class Message extends ContainerContent {
    public function __construct(
        protected string $message
    ) {
        parent::__construct(new WebPage(head: new HtmlHead("$message")));
    }



    public function render(): string {
        return match (App::getInstance()->getResponse()->getFormat()) {
            Format::IDENT_HTML => parent::render(),
            Format::IDENT_XML => Html::wrap('message', $this->message),
            Format::IDENT_JSON => json_encode([
                "isError" => false,
                "message" => $this->message
            ]),
            default => $this->message,
        };
    }
}