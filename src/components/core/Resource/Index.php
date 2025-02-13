<?php

namespace components\core\Resource;

use core\view\ContainerContent;

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