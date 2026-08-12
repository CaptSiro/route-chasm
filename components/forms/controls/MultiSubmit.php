<?php

namespace components\forms\controls;

use components\forms\FormAction;
use core\view\ViewTemplate;
use core\view\ViewTemplateRenderer;

class MultiSubmit implements ViewTemplate {
    use ViewTemplateRenderer, FormControl;

    /**
     * @param array<FormAction> $actions
     */
    public function __construct(
        private array $actions = []
    ) {}

    public function add(FormAction $action): self {
        $this->actions[] = $action;
        return $this;
    }
}