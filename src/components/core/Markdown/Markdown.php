<?php

namespace components\core\Markdown;

use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
use core\view\Renderer;
use core\view\View;

class Markdown implements View {
    use Renderer;

    public static function importAssets(): void {
        Javascript::import(self::getSelfResource('markdown.js'));
        Javascript::import(self::getSelfResource('md-tokenizer.js'));
        Javascript::import(self::getSelfResource('md-parser.js'));
        Css::import(self::getSelfResource('markdown.css'));
    }



    public function __construct(
        protected string $markDown
    ) {}
}