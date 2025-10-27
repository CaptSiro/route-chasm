<?php

namespace components\pages\TextPage;

use components\core\Editor\Editor;
use components\core\Html\Html;
use components\core\ToolBar\ToolBarItem;
use core\actions\Action;
use core\App;
use core\pages\PageTemplate;
use core\route\Route;
use core\RouteChasmEnvironment;
use core\view\View;
use models\core\Page\Page;

class TextPageTemplate implements PageTemplate {
    public function getName(): string {
        return "Text";
    }

    public function build(Page $page): View {
        return new Html('p', content: $page->getPathToSelf(RouteChasmEnvironment::DEFAULT_CONTEXT_MOUNT));
    }

    public function create(Page $page): ?View {
        return null;
    }

    public function getEditor(Page $page): Action {
        $editor = new Editor($page->get('content'));

        $localization = $page->getLocalizationOrDefault(App::getInstance()->getRequest()->getLanguage());
        $editor->setTitle($localization->title .' - Content Editor');

        $open = new ToolBarItem('file_open', 'ctrl + o');
        $open->addAttribute(
            'data-url',
            $page->getUrlToModel(RouteChasmEnvironment::DEFAULT_CONTEXT_MOUNT)
        );

        $editor->getToolBar()
            ->add(
                Route::menu('/File/Open'),
                $open,
            );

        return $editor;
    }

    public function delete(Page $page): ?View {
        return null;
    }
}