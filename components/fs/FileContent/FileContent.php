<?php

namespace components\fs\FileContent;

use core\view\Attribute;
use core\view\HtmlAttribute;
use core\view\View;
use core\view\ViewTemplateRenderer;

class FileContent implements View, Attribute {
    use ViewTemplateRenderer, HtmlAttribute;

    public function __construct(
        protected string $filePath,
        protected bool $readonly = true
    ) {}
}