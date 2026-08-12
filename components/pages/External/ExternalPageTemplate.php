<?php

namespace components\pages\External;

use components\forms\description\FormDescription;
use components\Message\Message;
use components\nexus\NexusEditorBehavior;
use components\pages\PagePreview;
use components\pages\PageTemplate;
use core\actions\Action;
use core\view\Component;
use core\view\PageView;
use core\view\View;
use models\Language\Language;
use models\Page\ExternalPage;
use models\Page\Page;

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
        return PageView::fromComponent(new Message('External Page has no content editor associated with its template'));
    }

    public function buildEditorBehavior(): ?NexusEditorBehavior {
        return new ExternalPageEditorBehavior(
            FormDescription::extract(ExternalPage::class)
        );
    }

    public function buildContent(Page $page, Language $language): Component {
        return new External(ExternalPage::fromPage($page)->url);
    }
}