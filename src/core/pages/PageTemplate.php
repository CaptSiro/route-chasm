<?php

namespace core\pages;

use components\pages\Wireframe\Wireframe;
use core\actions\Action;
use core\view\Component;
use core\view\View;
use models\core\Page\Page;

interface PageTemplate {
    public function getName(): string;

    public function create(Page $page): ?View;

    public function delete(Page $page): ?View;

    public function build(Wireframe $wireframe, Page $page): Component;

    public function getEditor(Page $page): Action;
}