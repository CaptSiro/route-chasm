<?php

namespace components\ai\FragmentGeneration;

use components\ai\InputMessage;

class FragmentGeneration extends InputMessage {
    public function __construct(
        string $role,
        protected string $file
    ) {
        parent::__construct($role);
        $this->setTemplate($this->getTemplateVariant(strtolower($role)));
    }
}