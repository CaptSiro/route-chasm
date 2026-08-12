<?php

namespace components\pages\External;

use core\locale\LexiconUnit;
use core\view\Component;

class External extends Component {
    use LexiconUnit;

    public const LEXICON_GROUP = 'external-page';



    public function __construct(
        protected string $url
    ) {
        parent::__construct();
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }
}