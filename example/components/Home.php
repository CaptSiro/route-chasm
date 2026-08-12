<?php

namespace example\components;

use components\docs\Docs;
use components\layout\Menu\Menu;
use core\App;
use core\locale\LexiconUnit;
use core\RouteChasmEnvironment;
use core\view\Controller;
use core\view\Renderer;
use core\view\renderers\HtmlRenderer;
use models\Setting\Setting;
use const models\extensions\Editable\PROPERTY_EDITABLE;

class Home extends Controller {
    use LexiconUnit;



    protected Menu $menu;

    public const LEXICON_GROUP = 'home';



    public function __construct(
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);

        $this->setTitle(App::getEnvStatic()->get(RouteChasmEnvironment::ENV_PROJECT) ?? 'RouteChasm');
        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->tr('Testing phrase');

        $description = Setting::fromName(
            RouteChasmEnvironment::SETTING_HOME_DESCRIPTION,
            true,
            '',
            [PROPERTY_EDITABLE => true]
        );

        $this->addPropertyHtmlMeta('description', $description->toString());
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