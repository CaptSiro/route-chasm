<?php

namespace components\core\Admin\Editor;

use components\core\Admin\Menu\AdminMenu;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\database\Schema;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\url\UrlPath;
use core\view\ContainerContent;
use modules\forms\controls\CsrfField;
use modules\forms\controls\HiddenField;
use modules\forms\controls\MultiSubmit\MultiSubmit;
use modules\forms\Form;
use modules\forms\FormAction;

class AdminEditor extends ContainerContent {
    public function __construct(
        protected Schema $schema,
        protected ?string $title = null
    ) {
        parent::__construct();
    }

    public function getForm(): Form {
        $form = new Form(HttpMethod::POST);

        $form->add(new CsrfField(App::getInstance()->getRequest()));
//        $form->add(new HiddenField(
//            $this->definition
//                ->getTable()
//                ->getIdColumn()
//        ));

        $this->schema
            ->getForm()
            ->initForm($form, []);

        $form->add(new MultiSubmit([
            new FormAction(FormAction::TYPE_RESET, 'Cancel'),
            new FormAction(FormAction::TYPE_SUBMIT, 'Submit')
        ]));

        return $form;
    }

    public function getTitle(): string {
        if (is_null($this->title)) {
            $segments = UrlPath::segmented(AdminMenu::getRequestPathSource());
            return array_pop($segments);
        }

        return $this->title;
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