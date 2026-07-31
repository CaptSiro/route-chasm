<?php

namespace components\layout\Spotlight;

use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class SpotlightSwitchLink implements ViewTemplate {
    use ViewTemplateRenderer;

    public static function createAttributes(string $label): string {
        return "x-init=\"spotlight_switch\" data-spotlight-switch=\"$label\"";
    }



    public function __construct(
        protected string $note,
        protected string $item,
        protected string $linkLabel,
    ) {}
}