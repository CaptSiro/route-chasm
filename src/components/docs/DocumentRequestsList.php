<?php

namespace components\docs;

use core\forms\controls\Select\Select;
use core\locale\LexiconUnit;
use core\RouteChasmEnvironment;
use core\view\Renderer;
use core\view\View;
use models\core\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class DocumentRequestsList implements View {
    use Renderer, LexiconUnit;

    public const LEXICON_GROUP = Docs::LEXICON_GROUP;



    public function __construct(
        protected string $name
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function createSearch(): View {
        $select = new Select($this->name .'_async', $this->tr('Search source files'));
        $select->setAsyncSearch(
            Docs::getInstance()
                ->createSearchUrl()
                ->setQueryArgument(Docs::QUERY_SEARCH_NO_LINKS)
                ->setQueryArgument(Docs::QUERY_SEARCH_SOURCES_ONLY),
            RouteChasmEnvironment::QUERY_SEARCH,
            Setting::fromName(
                RouteChasmEnvironment::SETTING_MIN_SEARCH_QUERY_LENGTH,
                true,
                RouteChasmEnvironment::SEARCH_MIN_LENGTH,
                [PROPERTY_EDITABLE => true]
            )->toInt()
        );

        return $select;
    }
}