<?php

namespace components\Dashboard;

use core\view\Html;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class DashboardSideBarItem implements ViewTemplate {
    use ViewTemplateRenderer;

    public static function button(string $onClick, string $labelHtml, ?string $icon = null): static {
        return new self(
            Html::wrapUnsafe(
                'div',
                $labelHtml,
                [
                    'class' => 'target',
                    'onclick' => $onClick
                ]
            ),
            $icon
        );
    }

    public static function link(string $href, string $labelHtml, ?string $icon = null): static {
        return new self(
            Html::wrapUnsafe(
                'a',
                $labelHtml,
                [
                    'href' => $href
                ]
            ),
            $icon
        );
    }



    public function __construct(
        protected string $label,
        protected ?string $icon = null
    ) {}
}