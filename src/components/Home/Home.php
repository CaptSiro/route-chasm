<?php

namespace components\Home;

use components\core\HtmlHead\HtmlHead;
use components\core\Menu\Menu;
use components\core\WebPage\ContextAwareWebPage;
use components\docs\Docs;
use core\App;
use core\RouteChasmEnvironment;
use core\view\ContainerContent;
use models\core\Setting\Setting;
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