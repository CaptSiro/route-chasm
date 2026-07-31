<?php

namespace components\Message;

use components\CallStack;
use components\Icon;
use core\locale\LexiconUnit;
use core\view\Component;
use core\view\Renderer;
use core\view\ViewTemplate;
use JsonSerializable;

class Message extends Component implements ViewTemplate, JsonSerializable {
    use LexiconUnit;

    public const LEXICON_GROUP = 'message';

    private const MAX_DISPLAY_LENGTH = 16;



    protected CallStack $stack;

    public function __construct(
        protected string $content,
        protected MessageType $type = MessageType::ERROR,
        int $stackTraceShiftCount = 0,
        ?Renderer $renderer = null,
    ) {
        parent::__construct($renderer);
        $this->stack = new CallStack(max($stackTraceShiftCount, 0));

        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->setTitle($this->getContentTrimmed());
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
            MessageType::CONFIRMATION => 'nf-fa-check_circle',
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
}