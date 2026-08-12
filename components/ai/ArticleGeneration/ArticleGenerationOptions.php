<?php

namespace components\ai\ArticleGeneration;

use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class ArticleGenerationOptions implements ViewTemplate {
    use ViewTemplateRenderer;



    public function __construct(
        protected ?ArticleGenerationLength $length = null,
        protected ?ArticleGenerationTone $tone = null,
    ) {
        $this->length ??= ArticleGenerationLength::MEDIUM;
        $this->tone ??= ArticleGenerationTone::FORMAL;
    }
}