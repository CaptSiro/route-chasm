<?php

namespace components\core\Explorer;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\ContextAwareWebPage;
use core\sideloader\importers\Css\Css;
use core\view\ContainerContent;

class Explorer extends ContainerContent {
    public function __construct(
        protected string $directory,
        protected string $label,
        protected string $url,
        protected bool $isParentEntryAllowed = true
    ) {
        Css::import($this->getResource("Explorer.css"));

        if (!str_ends_with($this->url, "/")) {
            $this->url .= "/";
        }

        parent::__construct(new ContextAwareWebPage(head: new HtmlHead("Explorer - $this->label")));
    }
}