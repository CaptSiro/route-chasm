<?php

namespace components\ai\ArticleGeneration;

use core\view\Renderer;
use core\view\View;

class ArticleGenerationOptions implements View {
    use Renderer;

    public function __construct(
        protected ?ArticleGenerationLength $length = null,
        protected ?ArticleGenerationTone $tone = null,
    ) {
        $this->length ??= ArticleGenerationLength::MEDIUM;
        $this->tone ??= ArticleGenerationTone::FORMAL;
    }
}