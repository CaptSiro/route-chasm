<?php

namespace core\forms\controls\MultiSubmit;

use core\forms\controls\FormControl;
use core\forms\FormAction;
use core\view\Renderer;
use core\view\View;

class MultiSubmit implements View {
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