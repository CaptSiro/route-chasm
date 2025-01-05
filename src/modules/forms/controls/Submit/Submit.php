<?php

namespace modules\forms\controls\Submit;

use core\Render;
use core\TemplateRenderer;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class Submit implements Render, Control {
    use TemplateRenderer, FormControl;



    public function __construct(
        protected string $label = "Submit"
    ) {}
}