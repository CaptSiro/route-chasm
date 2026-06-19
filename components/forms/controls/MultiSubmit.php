<?php

namespace components\forms\controls;

use components\forms\FormAction;
use core\view\Renderer;
use core\view\ViewTemplate;

class MultiSubmit implements ViewTemplate {
    use Renderer, FormControl;

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