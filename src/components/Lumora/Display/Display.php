<?php

namespace components\Lumora\Display;

use components\core\WebPage\WebPage;
use components\Lumora\Editor\Editor;
use core\view\ContainerContent;

class Display extends ContainerContent {
    protected WebPage $webPage;

    public function __construct(
        string $title,
        protected Editor $editor
    ) {
        parent::__construct($this->webPage = new WebPage());
        $this->webPage->getHead()->setTitle($title);
    }
}