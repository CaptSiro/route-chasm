<?php

namespace components\core\Admin\Page;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\BreadCrumbs\BreadCrumb;
use components\core\BreadCrumbs\BreadCrumbs;
use components\core\Icon;
use components\core\Message\Message;
use components\core\Terminal\Terminal;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\pages\Pages;
use core\route\RouteNode;
use core\url\Url;
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
        $context->setTitle($this->getLocalizedTitle());
        $context->setBreadCrumbs($this->getBreadCrumbs());
        return parent::setContext($context);
    }

    public function getLocalizedTitle(): string {
        $request = App::getInstance()->getRequest();
        $url = $request->getUrl();
        $pageLabel = $this->tr('Pages');

        if (empty($parentId = $url->getQuery()->get(self::QUERY_PARENT))) {
            return $pageLabel;
        }

        $page = Page::fromId(intval($parentId));
        $title = $page->getLocalization($request->getLanguage())->title
            ?? $this->tr('(No title)');
        return "$pageLabel - $title";
    }

    public function getBreadCrumbs(): BreadCrumbs {
        $request = App::getInstance()->getRequest();

        $url = $request->getUrl()
            ->copy()
            ->setQueryArgument(self::QUERY_PARENT);
        $breadCrumbs = [
            $url->toString() => Icon::nf('nf-fa-home', 'Home')
        ];

        if (empty($parentId = $request->getUrl()->getQuery()->get(self::QUERY_PARENT))) {
            return BreadCrumbs::from($breadCrumbs);
        }

        $page = Page::fromId(intval($parentId));
        $language = $request->getLanguage();

        foreach ($page->getParents() as $parent) {
            $parentUrl = $url
                ->copy()
                ->setQueryArgument(self::QUERY_PARENT, $parent->getId());
            $breadCrumbs[$parentUrl->toString()] = $parent->getLocalizationOrDefault($language)->title;
        }

        $ret = BreadCrumbs::from($breadCrumbs);
        $ret->add(new BreadCrumb($page->getLocalizationOrDefault($language)->title));

        return $ret;
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
            if (is_null($templateRecord = $page->getTemplateRecord())) {
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