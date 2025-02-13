<?php

namespace components\core\Resource;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPage;
use core\database\Entity;
use core\view\ContainerContent;

class Read extends ContainerContent {
    public function __construct(
        string $title,
        protected Entity $model,
    ) {
        parent::__construct(new WebPage(head: new HtmlHead($title)));
    }
}