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
use core\http\HttpHeader;
use core\http\HttpMethod;
use core\view\ContainerContent;
use core\view\View;
use modules\forms\controls\CsrfField;
use modules\forms\controls\HiddenField;
use modules\forms\controls\MultiSubmit\MultiSubmit;
use modules\forms\Form;
use modules\forms\FormAction;

class AdminNexusEditor extends ContainerContent {
    public const STATE_CREATOR = 0;
    public const STATE_UPDATER = 1;



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

    protected function getEntityData(): array {
        if (!isset($this->entity)) {
            return [];
        }

        return $this->entity->getData();
    }

    public function getState(): int {
        return isset($this->entity)
            ? self::STATE_UPDATER
            : self::STATE_CREATOR;
    }

    public function getForm(): View {
        $form = new Form($this->getState() === self::STATE_CREATOR
            ? HttpMethod::POST
            : HttpMethod::PUT
        );

        $form->add(new CsrfField(App::getInstance()->getRequest()));
        $form->add(new HiddenField(
            $this->schema
                ->getTable()
                ->getIdColumn()
        ));

        $this->schema
            ->getForm()
            ->initForm($form, $this->getEntityData());

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

                $entity = $this->schema->create($request->getBody()->asArray());
                $error = $entity->save();

                if (!is_null($error = $entity->save())) {
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

                $entity = $this->schema
                    ->getEntityFactory()
                    ->fromId($this->entity->getId());

                $entity->set($request->getBody()->asArray());

                if (!is_null($error = $entity->save())) {
                    $response->renderRoot($error);
                }

                $response->setStatus(HttpCode::S_ACCEPTED);
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