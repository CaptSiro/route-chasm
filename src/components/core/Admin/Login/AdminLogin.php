<?php

namespace components\core\Admin\Login;

use components\core\WebPage\WebPage;
use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\view\ContainerContent;
use core\view\View;
use modules\forms\controls\PasswordField\PasswordField;
use modules\forms\controls\Submit\Submit;
use modules\forms\Form;

class AdminLogin extends ContainerContent {
    protected WebPage $page;

    public function __construct() {
        parent::__construct($this->page = new WebPage());
    }



    public function createLoginForm(): View {
        $form = new Form(HttpMethod::POST);

        $form->add(new PasswordField('password', 'Password', addVisibilityControl: true));
        $form->add(new Submit());

        return $form;
    }

    public function execute(Request $request, Response $response): void {
        $this->page
            ->getHead()
            ->setTitle('Login');

        switch ($request->httpMethod) {
            case HttpMethod::GET: {
                parent::execute($request, $response);
                break;
            }

            case HttpMethod::POST: {
                break;
            }

            default: {
                $response->sendMessage(
                    'Invalid HTTP method ' . $request->httpMethod,
                    HttpCode::CE_BAD_REQUEST
                );
            }
        }
    }
}