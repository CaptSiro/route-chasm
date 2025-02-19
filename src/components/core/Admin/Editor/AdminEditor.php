<?php

namespace components\core\Admin\Editor;

use core\App;
use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\view\ContainerContent;
use modules\forms\controls\HiddenField;
use modules\forms\controls\MultiSubmit\MultiSubmit;
use modules\forms\Form;
use modules\forms\FormAction;

class AdminEditor extends ContainerContent {
    public function __construct(
        protected string $entityClass
    ) {
        parent::__construct();
    }

    public function getForm(): Form {
        $form = new Form(HttpMethod::POST);

//        $form->add(Form::csrf(App::getInstance()->getRequest()));
//        $form->add(new HiddenField(
//            call_user_func_array([$this->entityClass, 'getIdColumn'], []),
//        ));

        call_user_func_array([$this->entityClass, 'initCreateForm'], [$form]);

        $form->add(new MultiSubmit([
            new FormAction(FormAction::TYPE_RESET, 'Cancel'),
            new FormAction(FormAction::TYPE_SUBMIT, 'Submit')
        ]));

        return $form;
    }

    public function execute(Request $request, Response $response): void {
        switch ($request->httpMethod) {
            case HttpMethod::GET: {
                parent::execute($request, $response);
            }
            default:
                $response->error('Invalid HTTP method '. $request->httpMethod, HttpCode::CE_BAD_REQUEST);
        }
    }
}