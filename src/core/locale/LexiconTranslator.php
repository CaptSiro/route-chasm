<?php

namespace core\locale;

class LexiconTranslator {
    use LexiconUnit;

    public function __construct(string $group) {
        $this->setLexiconGroup($group);
    }
}