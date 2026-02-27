<?php

namespace components\core\Markdown;

use core\ResourceLoader;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;

class Markdown {
    use ResourceLoader;

    public static function importAssets(): void {
        Javascript::import(self::getSelfResource('markdown.js'));
        Javascript::import(self::getSelfResource('md-tokenizer.js'));
        Javascript::import(self::getSelfResource('md-parser.js'));
        Javascript::import(self::getSelfResource('md-gallery.js'));
        Css::import(self::getSelfResource('markdown.css'));
    }
}