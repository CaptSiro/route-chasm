<?php

namespace components\tf;

use core\locale\LexiconUnit;
use core\tf\Test;
use core\view\Component;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;

class Interrupt extends Component {
    use LexiconUnit;



    public function __construct(
        protected string $type,
        protected string $message,
        protected array $trace,
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);
        $this->setLexiconGroup(Test::LEXICON_GROUP);
    }



    public function getType(): string {
        return $this->type;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function getTrace(): array {
        return $this->trace;
    }

    public function toText(): string {
        return "[$this->type]: $this->message";
    }

    public function jsonSerialize(): array {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'trace' => $this->trace
        ];
    }
}