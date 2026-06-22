<?php

namespace components\Admin\SptfTests;

use core\locale\LexiconUnit;
use core\tf\Test;
use core\view\ContainerContent;

class SptfTests extends ContainerContent {
    use LexiconUnit;

    public const LEXICON_GROUP = Test::LEXICON_GROUP;



    public function __construct(
        protected string $directory
    ) {
        parent::__construct();
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    /**
     * @return array<SptfTestFile>
     */
    public function getTestFiles(): array {
        return array_map(
            fn($x) => new SptfTestFile($x),
            Test::evaluate($this->directory)
        );
    }
}