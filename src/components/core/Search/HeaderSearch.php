<?php

namespace components\core\Search;

use core\locale\LexiconUnit;
use core\RouteChasmEnvironment;
use core\view\Renderer;
use core\view\View;
use models\core\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class HeaderSearch implements View {
    use Renderer, LexiconUnit;

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