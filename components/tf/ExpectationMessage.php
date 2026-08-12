<?php

namespace components\tf;

use core\locale\LexiconUnit;
use core\tf\Test;
use core\view\Component;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;

class ExpectationMessage extends Component {
    use LexiconUnit;



    public function __construct(
        protected string $hint,
        protected mixed $expected,
        protected mixed $actual,
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);
        $this->setLexiconGroup(Test::LEXICON_GROUP);
    }



    public function getHint(): string {
        return $this->hint;
    }

    public function getActual(): mixed {
        return $this->actual;
    }

    public function getExpected(): mixed {
        return $this->expected;
    }

    public function jsonSerialize(): array {
        return [
            'hint' => $this->hint,
            'expected' => $this->expected,
            'actual' => $this->actual
        ];
    }
}