<?php

namespace components\Search;

use core\view\FormatAble;
use core\view\FormatAbleTrait;
use core\view\Formatter;
use core\view\ViewTemplate;
use models\Page\Page;
use models\Page\PageLocalization;

class SearchResult implements ViewTemplate, FormatAble {
    use FormatAbleTrait;

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
        $this->setFormatter(Formatter::default($this));
    }



    // FormatAble
    public function jsonSerialize(): array {
        return [
            "label" => $this->label,
            "value" => $this->value
        ];
    }
}