<?php

namespace components\Search;

use core\view\Component;
use models\Page\Page;
use models\Page\PageLocalization;

class SearchResult extends Component {
    public static function fromPage(Page $page, PageLocalization $localization): static {
        return new static(
            $localization->title,
            $page->getUrl(),
            $page->getTemplate()?->getName()
        );
    }



    public function __construct(
        protected string $label,
        protected string $value,
        protected ?string $meta = null,
        protected bool $isLink = true,
    ) {
        parent::__construct();
    }



    // FormatAble
    public function jsonSerialize(): array {
        return [
            "label" => $this->label,
            "value" => $this->value
        ];
    }
}