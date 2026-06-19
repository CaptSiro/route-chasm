<?php

namespace components\Admin;

use components\forms\controls\PasswordField;
use components\forms\controls\Submit;
use components\forms\Form;
use core\http\HttpMethod;
use core\view\Renderer;
use core\view\ViewTemplate;

class WizardAuth implements ViewTemplate {
    use Renderer;

    protected Form $form;

    public function __construct() {
        $this->form = new Form(HttpMethod::POST, namespace: Form::ns($this->getClass()));

        $this->form
            ->add(Form::title('Setup Wizard'))
            ->add(Form::note('To start setup wizard you must enter admin password'))
            ->add(new PasswordField('password', 'Admin password'))
            ->add(new Submit());
    }
}