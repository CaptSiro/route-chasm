<?php

namespace components\pages\TextPage;

use components\core\Editor\Editor;
use components\core\Html\Html;
use core\actions\Action;
use core\forms\controls\TextArea\TextArea;
use core\forms\Form;
use core\http\HttpMethod;
use core\pages\PageTemplate;
use core\RouteChasmEnvironment;
use core\view\View;
use models\core\Page\Page;

class TextPageTemplate implements PageTemplate {
    public function getName(): string {
        return "Text";
    }

    public function build(Page $page): View {
        return new Html('p', content: $page->getPathToSelf(RouteChasmEnvironment::DEFAULT_CONTEXT_MOUNT));
    }

    public function create(Page $page): ?View {
        return null;
    }

    public function getEditor(Page $page): Action {
        return new Editor($page->get('content'));
    }

    public function delete(Page $page): ?View {
        return null;
    }
}