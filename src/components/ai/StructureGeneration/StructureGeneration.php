<?php

namespace components\ai\StructureGeneration;

use components\ai\InputMessage;
use core\pages\PageTemplate;
use models\core\Language\Language;

class StructureGeneration extends InputMessage {
    /**
     * @param string $role
     * @param string $prompt
     * @param array<PageTemplate> $pageTemplates
     * @param Language $language
     */
    public function __construct(
        string $role,
        protected string $prompt,
        protected array $pageTemplates,
        protected Language $language,
    ) {
        parent::__construct($role);
        $this->setTemplate($this->getTemplateVariant(strtolower($role)));
    }
}