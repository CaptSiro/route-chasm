<?php

namespace components\core\Resource;

use components\core\WebPage\ContainerContent;

class Index extends ContainerContent {
    public function __construct(
        string $title,
        protected array $models
    ) {
        parent::__construct();

        $this->container
            ->getHead()
            ->setTitle($title);
    }
}