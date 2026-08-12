<?php

namespace example\components;

use components\Admin\Admin;
use components\layout\PageMenu\PageMenu;
use core\App;
use core\locale\LexiconUnit;
use core\RouteChasmEnvironment;
use core\view\Html;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;
use models\Menu;
use models\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class Footer implements ViewTemplate {
    use ViewTemplateRenderer, LexiconUnit;

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

        if (!$setting->toBoolean() || is_null($url = Admin::getInstance()->createUrl())) {
            return '';
        }

        return Html::wrap(
            'a',
            $this->tr('Admin'),
            ['href' => $url]
        );
    }

    public function getProjectName(): ?string {
        return App::getEnvStatic()
            ->get(RouteChasmEnvironment::ENV_PROJECT);
    }
}