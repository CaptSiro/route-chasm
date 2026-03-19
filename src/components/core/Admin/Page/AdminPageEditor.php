<?php

namespace components\core\Admin\Page;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\BreadCrumbs\BreadCrumb;
use components\core\BreadCrumbs\BreadCrumbs;
use components\core\Icon;
use components\core\Message\Message;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\pages\Pages;
use core\pages\PageTemplate;
use core\route\RouteNode;
use core\RouteChasmEnvironment;
use core\url\Url;
use models\core\Page\Page;
use RuntimeException;

class AdminPageEditor extends AdminNexusEditor {
    public const LEXICON_GROUP = 'admin.page.editor';



    public function __construct(
        EditorBehavior $behaviour,
        protected string $navigatorMountAlias = RouteChasmEnvironment::MOUNT_DEFAULT_CONTEXT
    ) {
        parent::__construct($behaviour);
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function setContext(AdminNexus $context): static {
        $context->setTitle($this->getLocalizedTitle());
        $context->setBreadCrumbs($this->getBreadCrumbs());
        return parent::setContext($context);
    }

    public function getUrlToModel(): Url {
        if (!($this->model instanceof Page)) {
            throw new RuntimeException("Model is not Page");
        }

        return $this->model->getUrlToModel(
            RouteChasmEnvironment::MOUNT_DEFAULT_CONTEXT
        );
    }

    public function getLocalizedTitle(): string {
        $request = App::getInstance()->getRequest();
        $url = $request->getUrl();
        $pageLabel = $this->tr('Pages');

        if (empty($parentId = $url->getQuery()->get(RouteChasmEnvironment::QUERY_PAGE_PARENT))) {
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
            ->setQueryArgument(RouteChasmEnvironment::QUERY_PAGE_PARENT);
        $breadCrumbs = [
            $url->toString() => Icon::home()
        ];

        if (empty($parentId = $request->getUrl()->getQuery()->get(RouteChasmEnvironment::QUERY_PAGE_PARENT))) {
            return BreadCrumbs::from($breadCrumbs);
        }

        $page = Page::fromId(intval($parentId));
        $language = $request->getLanguage();

        foreach ($page->getParents() as $parent) {
            $parentUrl = $url
                ->copy()
                ->setQueryArgument(RouteChasmEnvironment::QUERY_PAGE_PARENT, $parent->id);
            $breadCrumbs[$parentUrl->toString()] = $parent->getLocalizationOrDefault($language)->title;
        }

        $ret = BreadCrumbs::from($breadCrumbs);
        $ret->add(new BreadCrumb($page->getLocalizationOrDefault($language)->title));

        return $ret;
    }

    public function getPageTemplate(?Request $request = null): ?PageTemplate {
        $request ??= App::getInstance()
            ->getRequest();

        $pageId = $request->getUrl()->getQuery()->get(RouteChasmEnvironment::QUERY_PAGE);
        if (is_null($pageId)) {
            return null;
        }

        $page = Page::fromId(intval($pageId));
        return $page->getTemplate();
    }

    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();
        $router->use('template', function (Request $request, Response $response) {
            $pageId = $request->getUrl()->getQuery()->get(RouteChasmEnvironment::QUERY_PAGE);
            if (is_null($pageId)) {
                $queryParameter = RouteChasmEnvironment::QUERY_PAGE;
                $response->renderRoot(new Message(
                    $this->tr("URL Query parameter '$queryParameter' is missing")
                ));
            }

            $page = Page::fromId(intval($pageId));
            if (is_null($template = $page->getTemplate())) {
                $response->renderRoot(new Message($this->tr("Template is not set for this page")));
            }

            return $template->buildEditor($page);
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