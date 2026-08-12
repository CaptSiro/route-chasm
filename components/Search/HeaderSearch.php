<?php

namespace components\Search;

use core\locale\LexiconUnit;
use core\RouteChasmEnvironment;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;
use models\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class HeaderSearch implements ViewTemplate {
    use ViewTemplateRenderer, LexiconUnit;

    public const LEXICON_GROUP = Search::LEXICON_GROUP;



    public function __construct(
        protected ?string $url = null,
        protected ?int $minLength = null,
        protected string $placeholder = "Search articles...",
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);

        $this->minLength ??= Setting::fromName(
            RouteChasmEnvironment::SETTING_MIN_SEARCH_QUERY_LENGTH,
            true,
            RouteChasmEnvironment::SEARCH_MIN_LENGTH,
            [PROPERTY_EDITABLE => true]
        )->toInt();
    }
}