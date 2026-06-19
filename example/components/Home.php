<?php

namespace example\components;

use components\docs\Docs;
use components\html\HtmlHead;
use components\layout\Menu\Menu;
use components\layout\WebPage\ContextAwareWebPage;
use core\App;
use core\RouteChasmEnvironment;
use core\view\ContainerContent;
use models\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class Home extends ContainerContent {
    protected Menu $menu;

    public const LEXICON_GROUP = 'home';



    public function __construct() {
        parent::__construct(
            new ContextAwareWebPage(
                head: $head = new HtmlHead(
                    App::getEnvStatic()->get(RouteChasmEnvironment::ENV_PROJECT) ?? 'RouteChasm'
                )
            )
        );

        $this->setLexiconGroup(self::LEXICON_GROUP);

        $description = Setting::fromName(
            RouteChasmEnvironment::SETTING_HOME_DESCRIPTION,
            true,
            '',
            [PROPERTY_EDITABLE => true]
        );

        $head->addMeta('description', $description->toString());
    }



    public function createDocsLink(): ?string {
        $docs = Docs::getInstance();
        if (!$docs->isBound()) {
            return null;
        }

        return $docs
            ->createUrl()
            ->toString();
    }

    public function getProjectLink(): ?string {
        return App::getEnvStatic()
            ->get(RouteChasmEnvironment::ENV_PROJECT_LINK);
    }

    public function getProjectName(): ?string {
        return App::getEnvStatic()
            ->get(RouteChasmEnvironment::ENV_PROJECT) ?? 'RouteChasm';
    }
}