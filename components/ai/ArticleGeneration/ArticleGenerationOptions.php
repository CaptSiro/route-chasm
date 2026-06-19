<?php

namespace components\ai\ArticleGeneration;

use core\view\Renderer;
use core\view\ViewTemplate;

class ArticleGenerationOptions implements ViewTemplate {
    use Renderer;

    public function __construct(
        protected ?ArticleGenerationLength $length = null,
        protected ?ArticleGenerationTone $tone = null,
    ) {
        $this->length ??= ArticleGenerationLength::MEDIUM;
        $this->tone ??= ArticleGenerationTone::FORMAL;
    }
}