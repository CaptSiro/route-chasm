<?php

namespace components\pages;

use components\layout\ToolBar\ToolBarItem;
use components\Lumora\Display;
use components\Lumora\Editor\Editor;
use components\nexus\NexusEditorBehavior;
use core\actions\Action;
use core\route\Route;
use core\RouteChasmEnvironment;
use core\view\Component;
use core\view\View;
use models\Language\Language;
use models\Page\Page;
use models\Page\PageLocalization;

class TextPageTemplate implements PageTemplate {
    use PagePreview;

    public const DATA_ITEM_CONTENT = 'content';



    public function getName(): string {
        return "Text";
    }

    public function getDescription(): string {
        return "General content page aimed for text pages with few custom elements such as images or links. Includes component-base content editor";
    }

    public function create(Page $page): ?View {
        return null;
    }

    public function delete(Page $page): ?View {
        $page
            ->get(self::DATA_ITEM_CONTENT)
            ->delete();

        return null;
    }

    public function hasEditor(): bool {
        return true;
    }

    protected function createEditor(Page $page, PageLocalization $localization): Editor {
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

    public function buildEditor(Page $page): Action {
        $localization = $page->getLocalizationOrDefault();
        return $this->createEditor($page, $localization);
    }

    public function buildEditorBehavior(): ?NexusEditorBehavior {
        return null;
    }

    public function buildContent(Page $page, Language $language): Component {
        $localization = Wireframe::getLocalization($page, $language);

        $display = new Display(
            $localization->title,
            $this->createEditor($page, $localization)
        );

        $display->setProperty(Wireframe::PAYLOAD_DO_ADD_HEADER, false);
        $display->setProperty(Wireframe::PAYLOAD_DO_ADD_BREAD_CRUMBS, false);
        $display->setProperty(Wireframe::PAYLOAD_DO_ADD_FOOTER, false);

        return $display;
    }
}