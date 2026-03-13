<?php

namespace components\core\Markdown;

use core\ResourceLoader;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;

class Markdown {
    use ResourceLoader;

    public static function importAssets(): void {
        Javascript::import(self::getStaticResource('markdown.js'));
        Javascript::import(self::getStaticResource('md-tokenizer.js'));
        Javascript::import(self::getStaticResource('md-parser.js'));
        Javascript::import(self::getStaticResource('md-gallery.js'));
        Css::import(self::getStaticResource('markdown.css'));
    }
}