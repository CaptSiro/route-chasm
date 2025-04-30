<?php

namespace components\core\Admin\SetupWizard;

use core\forms\controls\PasswordField\PasswordField;
use core\forms\controls\Submit\Submit;
use core\forms\Form;
use core\http\HttpMethod;
use core\view\Renderer;
use core\view\View;

class WizardAuth implements View {
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