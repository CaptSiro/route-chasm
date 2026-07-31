<?php

namespace components\Search;

use core\locale\LexiconUnit;
use core\view\Component;
use core\view\Renderer;
use core\view\View;

class SearchResults extends Component {
    use LexiconUnit;



    /**
     * @param array<View> $results
     */
    public function __construct(
        protected array $results,
        protected ?View $searchFooter = null
    ) {
        parent::__construct();
        $this->setLexiconGroup(Search::LEXICON_GROUP);
    }



    public function setRenderer(Renderer $renderer): static {
        foreach ($this->results as $result) {
            Component::propagateSetRenderer($result, $renderer);
        }

        if (!is_null($this->searchFooter)) {
            Component::propagateSetRenderer($this->searchFooter, $renderer);
        }

        return parent::setRenderer($renderer);
    }

    public function jsonSerialize(): array {
        return $this->results;
    }
}