<?php

namespace modules\forms\controls\MultiSubmit;

use core\view\TemplateRenderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;
use modules\forms\FormAction;

class MultiSubmit implements Control {
    use TemplateRenderer, FormControl;

    /**
     * @param FormAction[] $actions
     */
    public function __construct(
        private array $actions = []
    ) {}

    public function add(FormAction $action): self {
        $this->actions[] = $action;
        return $this;
    }
}