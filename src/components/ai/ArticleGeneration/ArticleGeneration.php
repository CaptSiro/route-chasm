<?php

namespace components\ai\ArticleGeneration;

use components\ai\InputMessage;
use components\ai\MarkdownSpecification\MarkdownSpec;
use models\core\Language\Language;

class ArticleGeneration extends InputMessage {
    use MarkdownSpec;



    public function __construct(
        string $role,
        protected string $prompt,
        protected Language $language,
        protected ArticleGenerationOptions $options = new ArticleGenerationOptions()
    ) {
        parent::__construct($role);
        $this->setTemplate($this->getTemplateVariant(strtolower($role)));
    }
}