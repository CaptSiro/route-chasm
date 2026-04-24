<?php

namespace components\ai\MarkdownSpecification;

use components\ai\InputMessage;

class MarkdownSpecification extends InputMessage {
    /**
     * @param string $role
     */
    public function __construct(string $role) {
        parent::__construct($role);
        $this->setTemplate($this->getTemplateVariant(strtolower($role)));
    }
}