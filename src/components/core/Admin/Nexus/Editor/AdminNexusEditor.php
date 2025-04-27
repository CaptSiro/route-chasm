<?php

namespace components\core\Admin\Nexus\Editor;

use components\core\Admin\Nexus\AdminNexus;
use components\core\WebPage\WebPage;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\database\sql\Model;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\http\HttpMethod;
use core\view\ContainerContent;
use core\view\View;
use modules\forms\controls\CsrfField;
use modules\forms\controls\HiddenField;
use modules\forms\controls\MultiSubmit\MultiSubmit;
use modules\forms\Form;
use modules\forms\FormAction;

class AdminNexusEditor extends ContainerContent implements Editor {
    public const STATE_CREATOR = 0;
    public const STATE_UPDATER = 1;



    protected WebPage $page;
    protected ?Model $model = null;
    protected AdminNexus $context;

    public function __construct() {
        parent::__construct($this->page = new WebPage());
    }



    public function setContext(AdminNexus $context): static {
        $this->context = $context;
        return $this;
    }

    public function setModel(Model $model): static {
        $this->model = $model;
        return $this;
    }

    protected function getModelData(): array {
        if (!isset($this->model)) {
            return [];
        }

        return $this->model->getData();
    }

    public function getState(): int {
        return isset($this->model)
            ? self::STATE_UPDATER
            : self::STATE_CREATOR;
    }

    public function getForm(): View {
        $modelDescription = $this->context->getModelDescription();

        $form = new Form($this->getState() === self::STATE_CREATOR
            ? HttpMethod::POST
            : HttpMethod::PUT
        );

        $form->add(new CsrfField(App::getInstance()->getRequest()));
        $form->add(new HiddenField(
            $modelDescription->idColumn->name
        ));

        $this->context
            ->getFormSection()
            ->add($form, $this->getModelData());

        $submitLabel = $this->getState() === self::STATE_CREATOR
            ? 'Create'
            : 'Update';

        $form->add(new MultiSubmit([
            new FormAction(FormAction::TYPE_RESET, 'Cancel'),
            new FormAction(FormAction::TYPE_SUBMIT, $submitLabel)
        ]));

        return $form;
    }

    public function getTitle(): string {
        $title = $this->context->getTitle() .' - ';
        $title .= $this->getState() === self::STATE_CREATOR
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

                $model = $this->context
                    ->getModelDescription()
                    ->getFactory()
                    ->new();

                $model->set($request->getBody()->toArray());
                $error = $model->save();

                if ($error instanceof View) {
                    $response->renderRoot($error);
                }

                $response->setStatus(HttpCode::S_CREATED);
                $response->setHeader(HttpHeader::X_NEXT, $this->context->getLink());
                $response->flush();
            }

            case HttpMethod::PUT: {
                if (!CsrfField::check($request)) {
                    $response->sendMessage(
                        'Cross-Site request forgery detected',
                        HttpCode::CE_NOT_ACCEPTABLE
                    );
                }

                $model = $this->context
                    ->getModelDescription()
                    ->getFactory()
                    ->fromId($this->model->getId());

                $model->set($request->getBody()->toArray());
                $error = $model->save();

                if ($error instanceof View) {
                    $response->renderRoot($error);
                }

                $response->setStatus(HttpCode::S_OK);
                $response->setHeader(HttpHeader::X_NEXT, $this->context->getLink());
                $response->flush();
            }

            default:
                $response->sendMessage(
                    'Invalid HTTP method '. $request->httpMethod,
                    HttpCode::CE_BAD_REQUEST
                );
        }
    }
}