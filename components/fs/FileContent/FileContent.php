<?php

namespace components\fs\FileContent;

use components\html\Attribute;
use components\html\HtmlAttribute;
use core\view\Renderer;
use core\view\View;

class FileContent implements View, Attribute {
    use Renderer, HtmlAttribute;

    public function __construct(
        protected string $filePath,
        protected bool $readonly = true
    ) {}
}