<?php

namespace components\core\HttpError;

use components\core\CallStack\CallStack;
use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPage;
use components\core\WebPage\WebPageRenderCondition;
use core\App;
use core\communication\Format;
use core\http\HttpHeader;
use core\view\ContainerContent;

class HttpError extends ContainerContent {
    use WebPageRenderCondition;



    protected CallStack $stack;

    public function __construct(
        protected string $message,
        protected int $code,
        int $stackTraceShiftCount = 0
    ) {
        parent::__construct(
            new WebPage(head: new HtmlHead("Error - $message"))
        );

        $this->stack = new CallStack(max($stackTraceShiftCount, 0));
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