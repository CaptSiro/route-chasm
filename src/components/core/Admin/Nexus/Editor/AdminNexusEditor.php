<?php

namespace components\core\Admin\Nexus\Editor;

use components\core\Admin\Nexus\AdminNexus;
use components\core\WebPage\WebPage;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\database\Entity;
use core\database\Schema;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\view\ContainerContent;
use core\view\View;
use modules\forms\controls\CsrfField;
use modules\forms\controls\HiddenField;
use modules\forms\controls\MultiSubmit\MultiSubmit;
use modules\forms\Form;
use modules\forms\FormAction;

class AdminNexusEditor extends ContainerContent {
    protected Schema $schema;
    protected WebPage $page;
    protected ?Entity $entity = null;

    public function __construct(
        protected AdminNexus $context
    ) {
        parent::__construct($this->page = new WebPage());
        $this->schema = $this->context->getSchema();
    }



    public function setEntity(Entity $entity): static {
        $this->entity = $entity;
        return $this;
    }

    public function getForm(): View {
        $form = new Form(HttpMethod::POST);

        $form->add(new CsrfField(App::getInstance()->getRequest()));
        $form->add(new HiddenField(
            $this->schema
                ->getTable()
                ->getIdColumn()
        ));

        $this->schema
            ->getForm()
            ->initForm($form, []);

        $cancel = new FormAction(FormAction::TYPE_RESET, 'Cancel');
        $form->add(new MultiSubmit([
            $cancel,
            new FormAction(FormAction::TYPE_SUBMIT, 'Submit')
        ]));

        return $form;
    }

    public function getTitle(): string {
        $title = $this->context->getTitle() .' - ';
        $title .= is_null($this->entity)
            ? 'Create'
            : 'Update';

        return $title;
    }

    public function execute(Request $request, Response $response): void {
        $this->page
            ->getHead()
            ->setTitle($this->getTitle());

        switch ($request->httpMethod) {
            case HttpMethod::GET: {
                parent::execute($request, $response);
            }

            case HttpMethod::POST: {
                if (!CsrfField::check($request)) {
                    $response->sendMessage(
                        'Cross-Site request forgery detected',
                        HttpCode::CE_NOT_ACCEPTABLE
                    );
                }

                $entity = $this->schema->create($request->getBody()->asArray());
                $error = $entity->save();
                if (!is_null($error)) {
                    $response->renderRoot($error);
                }

                $response->setStatus(HttpCode::S_CREATED);
                $response->redirect($this->context->getLink());
            }

            default:
                $response->sendMessage(
                    'Invalid HTTP method '. $request->httpMethod,
                    HttpCode::CE_BAD_REQUEST
                );
        }
    }
}