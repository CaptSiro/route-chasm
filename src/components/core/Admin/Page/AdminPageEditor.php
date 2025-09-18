<?php

namespace components\core\Admin\Page;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Message\Message;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\pages\Pages;
use core\route\RouteNode;
use core\url\Url;
use models\core\Language\Language;
use models\core\Page\Page;

class AdminPageEditor extends AdminNexusEditor {
    public const LEXICON_GROUP = 'admin.page.editor';
    public const QUERY_PARENT = 'parent';
    public const QUERY_PAGE = 'page';



    public function __construct(EditorBehavior $behaviour) {
        parent::__construct($behaviour);
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function setContext(AdminNexus $context): static {
        if (!is_null($title = $this->getLocalizedTitle())) {
            $context->setTitle($title);
        }

        return parent::setContext($context);
    }

    public function getLocalizedTitle(): ?string {
        $request = App::getInstance()->getRequest();
        if (is_null($parentId = $request->getUrl()->getQuery()->get(self::QUERY_PARENT))) {
            return null;
        }

        $parent = Page::fromId(intval($parentId));
        $localization = $parent->getLocalization($request->getLanguage())
            ?? $parent->getLocalization(Language::getDefault());

        return $localization?->title;
    }

    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();
        $router->use('template', function (Request $request, Response $response) {
            $pageId = $request->getUrl()->getQuery()->get(self::QUERY_PAGE);
            if (is_null($pageId)) {
                $page = self::QUERY_PAGE;
                $response->renderRoot(new Message($this->tr("URL Query parameter '$page' is missing")));
            }

            $page = Page::fromId(intval($pageId));
            if (is_null($templateRecord = $page->getTemplate())) {
                $response->renderRoot(new Message($this->tr("Template is not set for this page")));
            }

            $template = Pages::getTemplate($templateRecord->getId());
            return $template->getEditor($page);
        });
    }

    public function getTemplateEditorLink(): ?Url {
        if (is_null($this->model) || is_null($url = $this->context->getEditorLink())) {
            return null;
        }

        $url->getPath()->append('template');
        $url->setQueryArgument('page', $this->model->getId());

        return $url;
    }
}