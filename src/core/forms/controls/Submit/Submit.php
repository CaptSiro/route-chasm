<?php

namespace core\forms\controls\Submit;

use core\forms\controls\Control;
use core\forms\controls\FormControl;
use core\forms\Form;
use core\view\Renderer;

class Submit implements Control {
    use Renderer, FormControl;



    public function __construct(
        protected ?string $label = null
    ) {
        $this->label ??= Form::getLexiconTranslator()
            ->tr('Submit');
    }



    public function setLabel(string $label): void {
        $this->label = $label;
    }

    public function setValue(mixed $value): void {
    }

    public function setName(string $name): void {
    }
}