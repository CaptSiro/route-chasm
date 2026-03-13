<?php

namespace components\core\Search;

use core\communication\Format;
use core\view\Component;
use core\view\Formatter;
use core\view\View;
use JsonSerializable;

class SearchResults extends Component implements JsonSerializable {
    protected Formatter $formatter;



    /**
     * @param array<View> $results
     */
    public function __construct(
        protected array $results,
        protected ?View $searchFooter = null
    ) {
        parent::__construct();
        $this->setLexiconGroup(Search::LEXICON_GROUP);

        $this->formatter = new Formatter(fn(string $format) => match ($format) {
            Format::IDENT_HTML => parent::render(),
            default => json_encode($this)
        });
    }



    public function render(): string {
        return $this->formatter->render();
    }

    public function jsonSerialize(): array {
        return $this->results;
    }
}