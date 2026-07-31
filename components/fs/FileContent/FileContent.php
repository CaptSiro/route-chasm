<?php

namespace components\fs\FileContent;

use components\html\Attribute;
use components\html\HtmlAttribute;
use core\view\View;
use core\view\ViewTemplateRenderer;

class FileContent implements View, Attribute {
    use ViewTemplateRenderer, HtmlAttribute;

    public function __construct(
        protected string $filePath,
        protected bool $readonly = true
    ) {}
}