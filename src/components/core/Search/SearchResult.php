<?php

namespace components\core\Search;

use core\view\Renderer;
use core\view\View;
use models\core\Page\Page;
use models\core\Page\PageLocalization;

class SearchResult implements View {
    use Renderer;

    public static function fromPage(Page $page, PageLocalization $localization): static {
        return new static(
            $localization->title,
            $page->getUrl(),
            $page->getTemplate()?->getName()
        );
    }



    public function __construct(
        protected string $title,
        protected string $url,
        protected ?string $meta = null
    ) {}
}