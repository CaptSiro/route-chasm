<?php

namespace components\Admin\SptfTests;

use core\locale\LexiconUnit;
use core\tf\Test;
use core\tf\TestFile;
use core\view\Renderer;
use core\view\ViewTemplate;

class SptfTestFile implements ViewTemplate {
    use Renderer, LexiconUnit;

    public const LEXICON_GROUP = Test::LEXICON_GROUP;



    public function __construct(
        protected TestFile $testFile
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }
}