<?php

namespace components;

use core\locale\LexiconUnit;
use core\view\Component;
use core\view\Renderer;

class NotFound extends Component {
    use LexiconUnit;

    public const LEXICON_GROUP = 'not-found';



    public function __construct(
        string $title,
        ?Renderer $renderer = null
    ) {
        parent::__construct($renderer);

        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->setTitle($this->tr('Page not found') . ': ' . $title);
    }
}