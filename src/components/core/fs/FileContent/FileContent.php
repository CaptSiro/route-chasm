<?php

namespace components\core\fs\FileContent;

use core\html\Attribute;
use core\html\HtmlAttribute;
use core\view\Renderer;
use core\view\View;

class FileContent implements View, Attribute {
    use Renderer, HtmlAttribute;

    public function __construct(
        protected string $filePath,
        protected bool $readonly = true
    ) {}
}