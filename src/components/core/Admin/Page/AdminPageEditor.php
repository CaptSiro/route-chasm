<?php

namespace components\core\Admin\Page;

use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\Message\Message;
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



    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();
        $router->use('template', function (Request $request, Response $response) {
            $pageId = $request->getUrl()->getQuery()->get('page');
            if (is_null($pageId)) {
                $response->renderRoot(new Message(
                    $this->tr(self::LEXICON_GROUP, "URL Query parameter 'page' is missing")
                ));
            }

            $page = Page::fromId(intval($pageId));
            if (is_null($templateRecord = $page->getTemplate())) {
                $response->renderRoot(new Message(
                    $this->tr(self::LEXICON_GROUP, "Template is not set for this page")
                ));
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