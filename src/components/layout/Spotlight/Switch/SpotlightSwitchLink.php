<?php

namespace components\layout\Spotlight\Switch;

use core\view\Renderer;
use core\view\View;

class SpotlightSwitchLink implements View {
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