<?php

namespace components\pages\Article;

use core\view\Component;

class Article extends Component {
    public const LEXICON_GROUP = 'article';

    public function __construct(
        protected string $content
    ) {
        parent::__construct();
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }
}