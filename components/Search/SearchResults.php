<?php

namespace components\Search;

use core\view\Component;
use core\view\FormatAble;
use core\view\FormatAbleTrait;
use core\view\Formatter;
use core\view\View;

class SearchResults extends Component implements FormatAble {
    use FormatAbleTrait;



    /**
     * @param array<FormatAble> $results
     */
    public function __construct(
        protected array $results,
        protected ?View $searchFooter = null
    ) {
        parent::__construct();
        $this->setLexiconGroup(Search::LEXICON_GROUP);

        $this->setFormatter(Formatter::default($this));
    }



    public function jsonSerialize(): array {
        return $this->results;
    }
}