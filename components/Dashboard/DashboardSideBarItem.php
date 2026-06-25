<?php

namespace components\Dashboard;

use core\view\Html;
use core\view\Renderer;
use core\view\ViewTemplate;

class DashboardSideBarItem implements ViewTemplate {
    use Renderer;

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
        /**
         * <a href="<?= AdminLogin::createLogoutUrl($request->getUrl()) ?>">
             * <span><?= Html::escape($this->trg(AdminRouter::LEXICON_GROUP, 'Logout')) ?></span>
             * <span>(<?= Html::escape(User::fromRequest($request)->username) ?>)</span>
         * </a>
         */

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