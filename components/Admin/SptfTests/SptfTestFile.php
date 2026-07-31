<?php

namespace components\Admin\SptfTests;

use core\locale\LexiconUnit;
use core\tf\Test;
use core\tf\TestFile;
use core\view\Component;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;
use JsonSerializable;

class SptfTestFile extends Component implements JsonSerializable {
    use LexiconUnit;

    public const LEXICON_GROUP = Test::LEXICON_GROUP;



    public function __construct(
        protected TestFile $testFile,
        ?Renderer $renderer = null
    ) {
        parent::__construct($renderer);
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function jsonSerialize(): TestFile {
        return $this->testFile;
    }
}