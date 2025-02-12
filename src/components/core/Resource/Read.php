<?php

namespace components\core\Resource;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\ContainerContent;
use components\core\WebPage\WebPage;
use core\database\Table;

class Read extends ContainerContent {
    public function __construct(
        string $title,
        protected Table $model,
    ) {
        parent::__construct(new WebPage(head: new HtmlHead($title)));
    }
}