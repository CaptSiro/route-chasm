<?php

namespace components\core\Resource;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPageContent;
use core\database\Table;

class Read extends WebPageContent {
    public function __construct(
        string $title,
        protected Table $model,
    ) {
        parent::__construct(head: new HtmlHead($title));
    }
}