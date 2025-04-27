<?php

namespace components\core\Resource;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\ContextAwareWebPage;
use core\database\sql\Model;
use core\view\ContainerContent;

class Read extends ContainerContent {
    public function __construct(
        string $title,
        protected Model $model,
    ) {
        parent::__construct(new ContextAwareWebPage(head: new HtmlHead($title)));
    }
}