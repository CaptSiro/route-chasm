<?php

namespace core\pages;

use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\pages\Wireframe\Wireframe;
use core\actions\Action;
use core\view\Component;
use core\view\View;
use models\core\Language\Language;
use models\core\Page\Page;

interface PageTemplate {
    public function getName(): string;

    public function create(Page $page): ?View;

    public function delete(Page $page): ?View;

    public function buildContent(Wireframe $wireframe, Page $page): Component;

    public function buildListingCard(Page $page, Language $language): View;

    public function buildSearchResult(Page $page, Language $language): View;

    public function hasEditor(): bool;

    public function buildEditor(Page $page): Action;

    public function buildEditorBehavior(): ?EditorBehavior;
}