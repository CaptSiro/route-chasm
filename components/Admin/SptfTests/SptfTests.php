<?php

namespace components\Admin\SptfTests;

use core\locale\LexiconUnit;
use core\tf\Test;
use core\view\Controller;
use core\view\Renderer;
use JsonSerializable;

class SptfTests extends Controller implements JsonSerializable {
    use LexiconUnit;

    public const LEXICON_GROUP = Test::LEXICON_GROUP;



    public function __construct(
        protected string $entry,
        ?Renderer $renderer = null
    ) {
        parent::__construct($renderer);
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    /**
     * @return array<SptfTestFile>
     */
    public function getTestFiles(): array {
        return array_map(
            fn($x) => new SptfTestFile($x, $this->renderer),
            Test::evaluate($this->entry)
        );
    }



    // JsonSerializable
    public function jsonSerialize(): array {
        return [
            'entry' => $this->entry,
            'testFiles' => $this->getTestFiles()
        ];
    }
}