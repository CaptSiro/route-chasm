<?php

namespace components\pages\Article;

use components\pages\Wireframe\Wireframe;
use core\actions\Action;
use core\pages\PageTemplate;
use core\view\Component;
use core\view\View;
use models\core\Page\Page;

class ArticleTemplate implements PageTemplate {
    public const DATA_CONTENT = 'article.md';



    public function getName(): string {
        return "Article";
    }

    public function create(Page $page): ?View {
        return null;
    }

    public function delete(Page $page): ?View {
        foreach ($page->getLocalizations() as $localization) {
            $localization
                ->get(self::DATA_CONTENT)
                ->delete();
        }

        return null;
    }

    public function build(Wireframe $wireframe, Page $page): Component {
        return new Article($wireframe->getLocalization()
            ->get(self::DATA_CONTENT)
            ->read() ?? '');
    }

    public function getEditor(Page $page): Action {
        return new ArticleEditor($page);
    }
}