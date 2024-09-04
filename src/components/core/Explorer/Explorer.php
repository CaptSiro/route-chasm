<?php

namespace components\core\Explorer;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPageContent;
use core\App;

class Explorer extends WebPageContent {
    public function __construct(
        protected string $directory,
        protected string $url,
        protected bool $isParentEntryAllowed = true
    ) {
        App::getInstance()
            ->link(__DIR__ ."/Explorer.css");

        if (!str_ends_with($this->url, "/")) {
            $this->url .= "/";
        }

        parent::__construct(head: new HtmlHead("Explorer - $this->directory"));
    }
}