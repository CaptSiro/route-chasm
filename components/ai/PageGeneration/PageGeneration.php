<?php

namespace components\ai\PageGeneration;

use components\ai\InputMessage;

class PageGeneration extends InputMessage {
    public function __construct(
        string $role,
        protected string $description
    ) {
        parent::__construct($role);
        $this->setTemplate($this->getTemplateVariant(strtolower($role)));
    }
}