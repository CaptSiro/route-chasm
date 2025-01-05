<?php

namespace components\core\HttpError;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPageContent;
use components\core\WebPage\WebPageRenderCondition;
use core\App;
use core\communication\Format;
use core\http\HttpHeader;
use core\Request;
use core\Response;

class HttpError extends WebPageContent {
    use WebPageRenderCondition;



    public function __construct(
        protected string $message,
        protected int $code
    ) {
        parent::__construct(head: new HtmlHead("Error - $message"));
        $this->initCondition(self::createHtmlPageCondition());
    }



    public function render(?string $template = null): string {
        $response = App::getInstance()
            ->getResponse();

        $response->setStatus($this->code);
        $format = $response->getFormat();
        $response->setHeader(HttpHeader::CONTENT_TYPE, $format);

        return match ($format) {
            Format::IDENT_HTML => parent::render(),
            Format::IDENT_XML => parent::render($this->getSource("HttpError.xml.phtml")),
            Format::IDENT_JSON => json_encode([
                "isError" => true,
                "message" => $this->message,
                "code" => $this->code
            ]),
            default => "$this->code: $this->message"
        };
    }
}