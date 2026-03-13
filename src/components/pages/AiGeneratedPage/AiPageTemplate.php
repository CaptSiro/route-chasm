<?php

namespace components\pages\AiGeneratedPage;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\Search\SearchResult;
use components\layout\Grid\description\GridDescription;
use components\pages\Listing\ListingCard;
use components\pages\Wireframe\Wireframe;
use core\actions\Action;
use core\forms\description\FormDescription;
use core\pages\PageTemplate;
use core\view\Component;
use core\view\View;
use models\core\Language\Language;
use models\core\Page\AiPage;
use models\core\Page\Page;

class AiPageTemplate implements PageTemplate {
    public const DATA_ITEM_HTML = 'html';
    public const DATA_ITEM_JS = 'js';
    public const DATA_ITEM_CSS = 'css';



    public function getName(): string {
        return "AI Generated";
    }

    public function create(Page $page): ?View {
        $aiPage = new AiPage();

        $aiPage->pageId = $page->id;
        $aiPage->description = '';
        $aiPage->save();

        return null;
    }

    public function delete(Page $page): ?View {
        AiPage::fromPage($page)?->delete();

        $page
            ->get(self::DATA_ITEM_HTML)
            ->delete();

        $page
            ->get(self::DATA_ITEM_JS)
            ->delete();

        $page
            ->get(self::DATA_ITEM_CSS)
            ->delete();

        return null;
    }

    public function buildContent(Wireframe $wireframe, Page $page): Component {
        return AiGeneratedPage::build($wireframe, $page);
    }

    public function buildListingCard(Page $page, Language $language): View {
        return new ListingCard($page, $page->getLocalization($language));
    }

    public function buildSearchResult(Page $page, Language $language): View {
        return SearchResult::fromPage($page, $page->getLocalization($language));
    }

    public function hasEditor(): bool {
        return true;
    }

    public function buildEditor(Page $page): Action {
        $editor = new AdminNexusEditor(
            new AiPageEditorBehavior(
                FormDescription::extract(AiPage::class),
                $this
            )
        );
        $nexus = new AdminNexus(
            AiPage::getDescription(),
            $editor,
            GridDescription::extract(AiPage::class)
        );

        $nexus->setTitle('AI Generated page');

        $editor->setContext($nexus);
        $editor->setModel(AiPage::fromPage($page));
        $editor->setFlag(AdminNexusEditor::FLAG_REMOVE_CANCEL_BUTTON);

        return $editor;
    }
}