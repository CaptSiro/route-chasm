<?php

namespace components\core\Search;

use core\view\Renderer;
use core\view\View;
use JsonSerializable;
use models\core\Page\Page;
use models\core\Page\PageLocalization;

class SearchCard implements View, JsonSerializable {
    use Renderer;



    public function __construct(
        protected Page $page,
        protected PageLocalization $localization
    ) {}



    // JsonSerializable
    public function jsonSerialize(): array {
        return [
            'href' => $this->page->getUrl(),
            'title' => $this->localization->title
        ];
    }
}