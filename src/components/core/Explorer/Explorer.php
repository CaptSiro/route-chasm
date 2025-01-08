<?php

namespace components\core\Explorer;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPageContent;
use core\App;
use modules\SideLoader\Css;

class Explorer extends WebPageContent {
    public function __construct(
        protected string $directory,
        protected string $label,
        protected string $url,
        protected bool $isParentEntryAllowed = true
    ) {
        Css::import($this->getSource("Explorer.css"));

        if (!str_ends_with($this->url, "/")) {
            $this->url .= "/";
        }

        parent::__construct(head: new HtmlHead("Explorer - $this->label"));
    }
}