<?php

namespace components\core\Admin\Nexus\Editor;

use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Nexus\Editor;
use components\core\WebPage\AdminWebPage;
use core\actions\UnexpectedHttpMethod;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\database\sql\Model;
use core\Flags;
use core\forms\controls\CsrfField;
use core\forms\controls\HiddenField;
use core\forms\controls\MultiSubmit\MultiSubmit;
use core\forms\Form;
use core\forms\FormAction;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\http\HttpMethod;
use core\route\RouteNode;
use core\sideloader\importers\Javascript\Javascript;
use core\view\ContainerContent;
use core\view\View;
use models\core\Privilege\Privilege;

class AdminNexusEditor extends ContainerContent implements Editor {
    use Flags, UnexpectedHttpMethod;

    public const FLAG_REMOVE_CANCEL_BUTTON = 1;

    public const NAME_SUBMIT_ACTION = 'nexus_submitAction';

    public const STATE_CREATOR = 0;
    public const STATE_UPDATER = 1;



    protected AdminWebPage $page;
    protected ?Model $model = null;
    protected AdminNexus $context;
    protected ?View $headerContent = null;

    public function __construct(
        protected EditorBehavior $behaviour
    ) {
        parent::__construct($this->page = new AdminWebPage());
        $this->setLexiconGroup(AdminNexus::LEXICON_GROUP);
        $this->behaviour->setEditor($this);
    }



    public function setContext(AdminNexus $context): static {
        $this->context = $context;
        Javascript::import($this->context->getResource('nexus.js'));
        return $this;
    }

    public function setHeaderContent(View $headerContent): void {
        $this->headerContent = $headerContent;
    }

    public function setModel(Model $model): static {
        $this->model = $model;
        return $this;
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
            $modelDescription->getIdColumn()->getAlias(),
            $this->getState() === self::STATE_UPDATER
                ? $this->model->getId()
                : ''
        ));

        if (!is_null($error = $this->behaviour->initForm($form, $this->model))) {
            return $error;
        }

        if (!is_null($error = $this->behaviour->addControls($form, $this->model))) {
            return $error;
        }

        $submitLabel = $this->getState() === self::STATE_CREATOR
            ? $this->tr('Create')
            : $this->tr('Update');

        $andReturnLabel = $this->tr('and return');

        $actions = [];
        if (!$this->hasFlag(self::FLAG_REMOVE_CANCEL_BUTTON)) {
            $actions[] = (new FormAction(FormAction::TYPE_BUTTON, $this->tr('Cancel')))
                ->addJavascriptInit('nexus_cancelButton')
                ->addAttribute('data-url', $this->context->getLink());
        }

        $actions[] = (new FormAction(FormAction::TYPE_SUBMIT, $submitLabel))
            ->setValue(self::NAME_SUBMIT_ACTION, 'stay');
        $actions[] = (new FormAction(FormAction::TYPE_SUBMIT, $submitLabel .' '. $andReturnLabel))
            ->setValue(self::NAME_SUBMIT_ACTION, 'return');

        $form->add(new MultiSubmit($actions));
        return $form;
    }

    public function getTitle(): string {
        $title = $this->context->getTitle() .' - ';
        $title .= $this->getState() === self::STATE_CREATOR
            ? 'Create'
            : 'Update';

        return $title;
    }

    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();
    }

    protected function sendResult(Request $request, Response $response, Model $model, EditorBehaviorAction $action): void {
        $error = $this->behaviour->onSubmit($model, $action);

        if ($error instanceof View) {
            $response->setStatus(HttpCode::CE_BAD_REQUEST);
            $response->renderRoot($error);
        }

        if ($action === EditorBehaviorAction::CREATE) {
            $response->setStatus(HttpCode::S_CREATED);
        } else {
            $response->setStatus(HttpCode::S_OK);
        }

        $submitAction = $request->getBody()
            ->get(self::NAME_SUBMIT_ACTION);

        if ($submitAction === 'stay') {
            if ($action === EditorBehaviorAction::CREATE) {
                $response->setHeader(HttpHeader::X_NEXT, $this->context->getUpdateLink($model->getId()));
            } else {
                $response->setHeader(HttpHeader::X_RELOAD, 'Reload');
            }
        }

        if ($submitAction === 'return' && !is_null($next = $this->context->getLink())) {
            $response->setHeader(HttpHeader::X_NEXT, $next);
        }

        $response->flush();
    }

    public function perform(Request $request, Response $response): void {
        $this->page
            ->getHead()
            ->setTitle($this->getTitle());

        switch ($request->getHttpMethod()) {
            case HttpMethod::GET: {
                $this->setUserResource($this->context->getUserResource());
                parent::perform($request, $response);
            }

            case HttpMethod::POST: {
                if (!$this->hasRequestAccess(Privilege::fromName(Privilege::CREATE))) {
                    $response->sendStatus(HttpCode::CE_FORBIDDEN);
                }

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

                $this->sendResult(
                    $request,
                    $response,
                    $model,
                    EditorBehaviorAction::CREATE
                );
            }

            case HttpMethod::PUT: {
                if (!$this->hasRequestAccess(Privilege::fromName(Privilege::UPDATE))) {
                    $response->sendStatus(HttpCode::CE_FORBIDDEN);
                }

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

                $this->sendResult(
                    $request,
                    $response,
                    $model,
                    EditorBehaviorAction::UPDATE
                );
            }

            default: {
                $this->handleUnexpectedMethod($request, $response);
                break;
            }
        }
    }
}