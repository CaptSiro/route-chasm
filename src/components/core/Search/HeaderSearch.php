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
    public const SETTING_NAME_MIN_LENGTH = 'route-chasm-core:search_minimum_query_length';



    public function __construct(
        protected ?int $minLength = null
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);

        $this->minLength ??= Setting::fromName(
            self::SETTING_NAME_MIN_LENGTH,
            true,
            RouteChasmEnvironment::SEARCH_MIN_LENGTH,
            [PROPERTY_EDITABLE => true]
        )->toInt();
    }
}