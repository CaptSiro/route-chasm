<?php

namespace components\pages\Docs;

use core\actions\Action;
use core\pages\PageTemplate;
use core\view\View;
use models\core\Page\Page;

class DocTemplate implements PageTemplate {
    public function getName(): string {
        return "Docs";
    }

    public function create(Page $page): View {

    }

    public function delete(Page $page): ?View {

    }

    public function getEditor(Page $page): Action {

    }

    public function build(Page $page): View {

    }
}