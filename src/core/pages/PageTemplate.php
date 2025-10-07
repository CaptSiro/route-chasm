<?php

namespace core\pages;

use core\actions\Action;
use core\view\View;
use models\core\Page\Page;

interface PageTemplate {
    public function getName(): string;

    public function create(Page $page): ?View;

    public function delete(Page $page): ?View;

    public function build(Page $page): View;

    public function getEditor(Page $page): Action;
}