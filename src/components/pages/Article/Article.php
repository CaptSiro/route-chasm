<?php

namespace components\pages\Article;

use core\view\Component;
use models\core\Page\PageLocalization;
use models\core\Page\Page;

class Article extends Component {
    public const LEXICON_GROUP = 'article';

    public function __construct(
        protected Page $page,
        protected PageLocalization $localization,
        protected string $content
    ) {
        parent::__construct();
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }
}