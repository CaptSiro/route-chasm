<?php

namespace components\core\HttpMessage;

use components\core\CallStack\CallStack;
use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\ContextAwareWebPage;
use components\core\WebPage\WebPageRenderCondition;
use core\App;
use core\communication\Format;
use core\view\ContainerContent;
use core\view\Formatter;

class HttpMessage extends ContainerContent {
    use WebPageRenderCondition;



    protected CallStack $stack;
    protected Formatter $formatter;

    public function __construct(
        protected string $message,
        protected int $code,
        int $stackTraceShiftCount = 0
    ) {
        parent::__construct(
            new ContextAwareWebPage(head: new HtmlHead("Error - $message"))
        );

        $this->stack = new CallStack(max($stackTraceShiftCount, 0));
        $this->initCondition(self::createHtmlPageCondition());

        $this->formatter = new Formatter(fn(string $format) => match ($format) {
            Format::IDENT_HTML => parent::render(),
            Format::IDENT_XML => parent::renderTemplated($this->getResource("HttpMessage.xml.phtml")),
            Format::IDENT_JSON => json_encode([
                "isError" => $this->code >= 400,
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