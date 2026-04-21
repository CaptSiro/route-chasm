<?php

namespace components\core\PageMenu;

use components\core\Html\Html;
use core\admin\Admin;
use core\App;
use core\locale\LexiconUnit;
use core\RouteChasmEnvironment;
use core\view\Renderer;
use core\view\View;
use models\core\Menu;
use models\core\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class Footer implements View {
    use Renderer, LexiconUnit;

    public const LEXICON_GROUP = 'footer';



    public static function default(): static {
        return new static(
            PageMenu::fromModelName(Menu::NAME_FOOTER),
            PageMenu::fromModelName(Menu::NAME_LEGAL),
        );
    }



    public function __construct(
        protected PageMenu $menu,
        protected PageMenu $legal
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function getAdminLoginLink(): string {
        $setting = Setting::fromName(
            RouteChasmEnvironment::SETTING_SHOW_ADMIN_LOGIN_IN_FOOTER,
            true,
            true,
            [PROPERTY_EDITABLE => true]
        );

        if (!$setting->toBoolean() || is_null($url = Admin::getUrl())) {
            return '';
        }

        return Html::wrap(
            'a',
            $this->tr('Admin'),
            ['href' => $url]
        );
    }
}