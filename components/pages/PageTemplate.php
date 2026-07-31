<?php

namespace components\pages;

use components\Admin\Nexus\Editor\EditorBehavior;
use core\actions\Action;
use core\view\Component;
use core\view\View;
use models\Language\Language;
use models\Page\Page;

interface PageTemplate {
    public function getName(): string;

    public function getDescription(): string;

    public function create(Page $page): ?View;

    public function delete(Page $page): ?View;

    public function buildContent(Page $page, Language $language): Component;

    public function buildListingCard(Page $page, Language $language): View;

    public function buildSearchResult(Page $page, Language $language): View;

    public function buildRelatedCard(Page $page, Language $language): View;

    public function hasEditor(): bool;

    public function buildEditor(Page $page): Action;

    public function buildEditorBehavior(): ?EditorBehavior;
}