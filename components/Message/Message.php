<?php

namespace components\Message;

use components\CallStack;
use components\html\HtmlHead;
use components\Icon;
use components\layout\WebPage\ContextAwareWebPage;
use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\communication\Request;
use core\communication\Response;
use core\locale\LexiconUnit;
use core\route\RouteNode;
use core\view\FormatAble;
use core\view\FormatAbleTrait;
use core\view\Formatter;
use core\view\ViewTemplate;

class Message implements Action, ViewTemplate, FormatAble {
    use ActionBindRouteNode, FormatAbleTrait, LexiconUnit;

    public const LEXICON_GROUP = 'message';

    private const MAX_DISPLAY_LENGTH = 16;



    protected CallStack $stack;
    protected Formatter $formatter;

    public function __construct(
        protected string $content,
        protected MessageType $type = MessageType::ERROR,
        int $stackTraceShiftCount = 0,
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->setFormatter(Formatter::default($this));
        $this->stack = new CallStack(max($stackTraceShiftCount, 0));
    }



    public function getType(): MessageType {
        return $this->type;
    }

    public function getTypeLabel(): string {
        return $this->tr(ucfirst($this->type->toLowerCase()));
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getContentTrimmed(): string {
        if (strlen($this->content <= self::MAX_DISPLAY_LENGTH)) {
            return $this->content;
        }

        return substr($this->content, 0, self::MAX_DISPLAY_LENGTH) . '...';
    }

    public function getIcon(): string {
        return Icon::nf(match ($this->type) {
            MessageType::INFO => 'nf-oct-info',
            MessageType::NOTICE => 'nf-fa-check_circle',
            MessageType::WARNING => 'nf-fa-warning',
            MessageType::ERROR => 'nf-cod-error',
        }, $this->getTypeLabel());
    }



    // FormatAble
    public function toText(): string {
        $type = $this->type->value;
        $content = $this->content;
        return "[$type]: $content";
    }

    public function jsonSerialize(): array {
        return [
            'type' => $this->type->toLowerCase(),
            'message' => $this->content,
            'stack' => $this->stack,
        ];
    }



    // Action
    public function isMiddleware(): bool {
        return false;
    }

    public function getActorName(): string {
        $type = $this->type->toLowerCase();
        $content = $this->getContentTrimmed();
        return "Message($type, $content...)";
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function perform(Request $request, Response $response): void {
        $page = new ContextAwareWebPage(head: new HtmlHead($this->getContentTrimmed()));
        $page->addContent($this);
        $response->render($page);
    }
}