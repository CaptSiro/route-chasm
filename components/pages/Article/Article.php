<?php

namespace components\pages\Article;

use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
use core\view\Component;
use models\Page\Page;
use models\Page\PageLocalization;

class Article extends Component {
    public const LEXICON_GROUP = 'article';

    public static function importAssets(): void {
        Javascript::import(self::getStaticResource('article.js'));
        Css::import(self::getStaticResource('article.css'));
    }



    public function __construct(
        protected Page $page,
        protected PageLocalization $localization,
        protected string $content
    ) {
        parent::__construct();
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }
}