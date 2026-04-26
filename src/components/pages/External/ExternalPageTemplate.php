<?php

namespace components\pages\External;

use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Message\Message;
use components\core\Search\SearchResult;
use components\pages\Listing\ListingCard;
use components\pages\Wireframe\Wireframe;
use core\actions\Action;
use core\forms\description\FormDescription;
use core\pages\PagePreview;
use core\pages\PageTemplate;
use core\view\Component;
use core\view\View;
use models\core\Language\Language;
use models\core\Page\ExternalPage;
use models\core\Page\Page;

class ExternalPageTemplate implements PageTemplate {
    use PagePreview;



    public function getName(): string {
        return "External";
    }

    public function getDescription(): string {
        return "Used to link external pages or statically bind pages via URL link";
    }

    public function create(Page $page): ?View {
        $external = new ExternalPage();

        $external->pageId = $page->id;
        $external->save();

        return null;
    }

    public function delete(Page $page): ?View {
        ExternalPage::fromPage($page)->delete();
        return null;
    }

    public function hasEditor(): bool {
        return false;
    }

    public function buildEditor(Page $page): Action {
        return new Message('External Page has no content editor associated with its template');
    }

    public function buildEditorBehavior(): ?EditorBehavior {
        return new ExternalPageEditorBehavior(
            FormDescription::extract(ExternalPage::class)
        );
    }

    public function buildContent(Wireframe $wireframe, Page $page): Component {
        return new External(ExternalPage::fromPage($page)->url);
    }
}