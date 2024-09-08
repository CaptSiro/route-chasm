<?php

namespace components\core\Resource;

use components\core\WebPage\WebPageContent;

class Index extends WebPageContent {
    public function __construct(
        string $title,
        protected array $models
    ) {
        parent::__construct();

        $this->page
            ->getHead()
            ->setTitle($title);
    }
}