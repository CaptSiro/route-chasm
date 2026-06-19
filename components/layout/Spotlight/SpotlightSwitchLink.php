<?php

namespace components\layout\Spotlight;

use core\view\Renderer;
use core\view\ViewTemplate;

class SpotlightSwitchLink implements ViewTemplate {
    use Renderer;

    public static function createAttributes(string $label): string {
        return "x-init=\"spotlight_switch\" data-spotlight-switch=\"$label\"";
    }



    public function __construct(
        protected string $note,
        protected string $item,
        protected string $linkLabel,
    ) {}
}