<?php

namespace components\core\Search;

use core\view\Renderer;
use core\view\View;
use JsonSerializable;
use models\core\Page\Page;
use models\core\Page\PageLocalization;

class SearchResult implements View, JsonSerializable {
    use Renderer;

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
    ) {}



    // JsonSerializable
    public function jsonSerialize(): array {
        return [
            "label" => $this->label,
            "value" => $this->value
        ];
    }
}