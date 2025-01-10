<?php

namespace modules\forms\controls\Submit;

use core\view\TemplateRenderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class Submit implements Control {
    use TemplateRenderer, FormControl;

    public function __construct(
        protected string $label = "Submit"
    ) {}
}