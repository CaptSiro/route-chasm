<?php

namespace modules\forms\controls\MultiSubmit;

use core\view\Renderer;
use core\view\View;
use modules\forms\controls\FormControl;
use modules\forms\FormAction;

class MultiSubmit implements View {
    use Renderer, FormControl;

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