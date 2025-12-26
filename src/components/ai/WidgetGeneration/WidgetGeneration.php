<?php

namespace components\ai\WidgetGeneration;

use components\ai\InputMessage;

class WidgetGeneration extends InputMessage {
    public function __construct(
        string $role,
        protected string $description
    ) {
        parent::__construct($role);
        $this->setTemplate($this->getTemplateVariant(strtolower($role)));
    }
}