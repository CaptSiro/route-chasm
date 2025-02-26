<?php

namespace components\core\HttpError;

use components\core\CallStack\CallStack;
use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPage;
use components\core\WebPage\WebPageRenderCondition;
use core\App;
use core\communication\Format;
use core\view\ContainerContent;
use core\view\Formatter;

class HttpError extends ContainerContent {
    use WebPageRenderCondition;



    protected CallStack $stack;
    protected Formatter $formatter;

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

        $this->formatter = new Formatter(fn(string $format) => match ($format) {
            Format::IDENT_HTML => parent::render(),
            Format::IDENT_XML => parent::renderTemplated($this->getResource("HttpError.xml.phtml")),
            Format::IDENT_JSON => json_encode([
                "isError" => true,
                "message" => $this->message,
                "code" => $this->code
            ]),
            default => "$this->code: $this->message"
        });
    }



    public function render(): string {
        App::getInstance()
            ->getResponse()
            ->setStatus($this->code);

        return $this->formatter->render();
    }
}