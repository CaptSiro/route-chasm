<?php

namespace components\pages\External;

use core\view\Component;

class External extends Component {
    public const LEXICON_GROUP = 'external-page';



    public function __construct(
        protected string $url
    ) {
        parent::__construct();
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }
}