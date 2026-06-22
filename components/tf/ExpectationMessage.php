<?php

namespace components\tf;

use core\locale\LexiconUnit;
use core\tf\Test;
use core\view\FormatAble;
use core\view\FormatAbleTrait;

class ExpectationMessage implements FormatAble {
    use FormatAbleTrait, LexiconUnit;



    public function __construct(
        protected string $hint,
        protected mixed $expected,
        protected mixed $actual,
    ) {
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