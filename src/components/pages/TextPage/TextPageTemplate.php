<?php

namespace components\pages\TextPage;

use components\core\Html\Html;
use core\actions\Action;
use core\forms\controls\TextArea\TextArea;
use core\forms\Form;
use core\http\HttpMethod;
use core\pages\PageTemplate;
use core\view\View;
use models\core\Page\Page;

class TextPageTemplate implements PageTemplate {
    public function getName(): string {
        return "Text";
    }

    public function build(Page $page): View {
        return new Html('p', content: 'test');
    }

    public function create(Page $page): ?View {
        return null;
    }

    public function getEditor(Page $page): Action {
        $form = new Form(HttpMethod::POST);
        $form->add(new TextArea('text', 'Text', 'test'));
        return $form;
    }

    public function delete(Page $page): ?View {
        return null;
    }
}