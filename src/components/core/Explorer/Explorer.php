<?php

namespace components\core\Explorer;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPage;
use core\view\ContainerContent;
use modules\SideLoader\Css;

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

        parent::__construct(new WebPage(head: new HtmlHead("Explorer - $this->label")));
    }
}