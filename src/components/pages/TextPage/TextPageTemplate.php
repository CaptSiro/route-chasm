<?php

namespace components\pages\TextPage;

use components\core\ToolBar\ToolBarItem;
use components\Lumora\Display\Display;
use components\Lumora\Editor\Editor;
use components\pages\Listing\ListingCard;
use components\pages\Wireframe\Wireframe;
use core\actions\Action;
use core\pages\PageTemplate;
use core\route\Route;
use core\RouteChasmEnvironment;
use core\view\Component;
use core\view\View;
use models\core\Language\Language;
use models\core\Page\LocalizedPage;
use models\core\Page\Page;

class TextPageTemplate implements PageTemplate {
    public const DATA_ITEM_CONTENT = 'content';



    public function getName(): string {
        return "Text";
    }

    protected function createEditor(Page $page, LocalizedPage $localization): Editor {
        $editor = new Editor($page->get(self::DATA_ITEM_CONTENT), $localization);
        $editor->setTitle($localization->title .' - Content Editor');

        $open = new ToolBarItem('file_open', 'ctrl + o');
        $open->addAttribute(
            'data-url',
            $page->getUrlToModel(RouteChasmEnvironment::MOUNT_DEFAULT_CONTEXT)
        );

        $editor->getToolBar()
            ->add(
                Route::menu('/File/Open'),
                $open,
            );

        return $editor;
    }

    public function buildContent(Wireframe $wireframe, Page $page): Component {
        $localization = $wireframe->getLocalization();

        return new Display(
            $localization->title,
            $this->createEditor($page, $localization)
        );
    }

    public function buildListingCard(Page $page, Language $language): View {
        return new ListingCard($page, $page->getLocalization($language));
    }

    public function create(Page $page): ?View {
        return null;
    }

    public function hasEditor(): bool {
        return true;
    }

    public function buildEditor(Page $page): Action {
        $localization = $page->getLocalizationOrDefault();
        return $this->createEditor($page, $localization);
    }

    public function delete(Page $page): ?View {
        $page
            ->get(self::DATA_ITEM_CONTENT)
            ->delete();

        return null;
    }
}