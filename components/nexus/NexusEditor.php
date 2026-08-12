<?php

namespace components\nexus;

use components\forms\controls\CsrfField;
use components\forms\controls\HiddenField;
use components\forms\controls\MultiSubmit;
use components\forms\Form;
use components\forms\FormAction;
use core\actions\UnexpectedHttpMethod;
use core\App;
use core\communication\body\DictionaryBody;
use core\communication\Request;
use core\communication\Response;
use core\database\sql\Model;
use core\database\sql\ModelDescription;
use core\Flags;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\http\HttpMethod;
use core\locale\LexiconUnit;
use core\sideloader\importers\Javascript\Javascript;
use core\view\Component;
use core\view\Controller;
use core\view\Renderer;
use core\view\View;
use core\view\ViewTemplateSlotTrait;
use models\Privilege\Privilege;
use UnexpectedValueException;

class NexusEditor extends Controller {
    use Flags, UnexpectedHttpMethod, LexiconUnit, ViewTemplateSlotTrait;

    public const FLAG_REMOVE_CANCEL_BUTTON = 1;

    public const NAME_SUBMIT_ACTION = 'nexus_submitAction';

    /** Replaces the whole header with custom header view */
    public const SLOT_HEADER = 'nexus-editor:header';
    /**
     * Adds additional content to the header. If `SLOT_HEADER` is set then this slot is not applied
     * @see NexusEditor::SLOT_HEADER
     */
    public const SLOT_HEADER_CONTENT = 'nexus-editor:header-content';

    public const STATE_CREATE = 'Create';
    public const STATE_UPDATE = 'Update';



    /**
     * @var array<NexusEditorBehavior>
     */
    protected array $behaviors = [];
    protected ?Model $model = null;
    protected Nexus $context;

    public function __construct(
        protected ModelDescription $modelDescription,
        NexusEditorBehavior $behaviour,
        ?Renderer $renderer = null
    ) {
        parent::__construct($renderer);
        $this->setLexiconGroup(Nexus::LEXICON_GROUP);
        $this->behaviors[] = $behaviour;
    }



    public function addBehavior(NexusEditorBehavior $behavior): static {
        $this->behaviors[] = $behavior;
        return $this;
    }

    public function setContext(Nexus $context): static {
        $this->context = $context;
        Javascript::import(Nexus::getStaticResource(Nexus::getBaseClass() . '.js'));
        return $this;
    }

    public function setModel(Model $model): static {
        $this->model = $model;
        return $this;
    }

    public function getState(): string {
        return isset($this->model)
            ? self::STATE_UPDATE
            : self::STATE_CREATE;
    }

    public function getForm(): View {
        $form = new Form(match ($this->getState()) {
            self::STATE_CREATE => HttpMethod::POST,
            self::STATE_UPDATE => HttpMethod::PUT,
            default => throw new UnexpectedValueException()
        });

        $form->add(new CsrfField(App::getInstance()->getRequest()));
        $form->add(new HiddenField(
            $this->modelDescription->getIdColumn()->getAlias(),
            $this->getState() === self::STATE_UPDATE
                ? $this->model->getId()
                : ''
        ));

        foreach ($this->behaviors as $behavior) {
            if (!is_null($error = $behavior->initialize($this, $form, $this->model))) {
                return $error;
            }
        }

        foreach ($this->behaviors as $behavior) {
            if (!is_null($error = $behavior->generate($form, $this->model))) {
                return $error;
            }
        }

        $submitLabel = $this->tr($this->getState());
        $andReturnLabel = $this->tr('and return');

        $actions = [];
        if (!$this->hasFlag(self::FLAG_REMOVE_CANCEL_BUTTON) && isset($this->context)) {
            $actions[] = (new FormAction(FormAction::TYPE_BUTTON, $this->tr('Cancel')))
                ->addJavascriptInit('nexus_cancelButton')
                ->addAttribute('data-url', $this->context->getUrl());
        }

        $actions[] = (new FormAction(FormAction::TYPE_SUBMIT, $submitLabel))
            ->setValue(self::NAME_SUBMIT_ACTION, 'stay');

        if (isset($this->context)) {
            $actions[] = (new FormAction(FormAction::TYPE_SUBMIT, $submitLabel .' '. $andReturnLabel))
                ->setValue(self::NAME_SUBMIT_ACTION, 'return');
        }

        $form->add(new MultiSubmit($actions));

        Component::propagateSetRenderer($form, $this->renderer);
        return $form;
    }

    protected function sendResult(Request $request, Response $response, Model $model, NexusEditorAction $action): void {
        foreach ($this->behaviors as $behavior) {
            if (!is_null($error = $behavior->onSubmit($model, $action))) {
                $response->setStatus(HttpCode::CE_BAD_REQUEST);
                $response->render($error);
            }
        }

        if ($action === NexusEditorAction::CREATE) {
            $response->setStatus(HttpCode::S_CREATED);
        } else {
            $response->setStatus(HttpCode::S_OK);
        }

        $submitAction = $request
            ->body(DictionaryBody::class)
            ->getFields()
            ->get(self::NAME_SUBMIT_ACTION);

        if ($submitAction === 'stay') {
            if ($action === NexusEditorAction::CREATE) {
                $response->setHeader(HttpHeader::X_NEXT, $this->context->getUpdateUrl($model->getId()));
            } else {
                $response->setHeader(HttpHeader::X_RELOAD, 'Reload');
            }
        }

        if ($submitAction === 'return' && !is_null($next = $this->context->getUrl())) {
            $response->setHeader(HttpHeader::X_NEXT, $next);
        }

        $response->flush();
    }

    public function performControllerAction(Request $request, Response $response): void {
        $messageCrossSiteForgery = $this->tr('Cross-Site request forgery detected');

        switch ($request->getHttpMethod()) {
            case HttpMethod::GET: {
                parent::performControllerAction($request, $response);
            }

            case HttpMethod::POST: {
                if (!$this->hasRequestAccess(Privilege::fromName(Privilege::CREATE))) {
                    $response->sendStatus(HttpCode::CE_FORBIDDEN);
                }

                if (!CsrfField::check($request)) {
                    $response->sendMessage($messageCrossSiteForgery, HttpCode::CE_NOT_ACCEPTABLE);
                }

                $model = $this->modelDescription
                    ->getFactory()
                    ->new();

                $this->sendResult(
                    $request,
                    $response,
                    $model,
                    NexusEditorAction::CREATE
                );
            }

            case HttpMethod::PUT: {
                if (!$this->hasRequestAccess(Privilege::fromName(Privilege::UPDATE))) {
                    $response->sendStatus(HttpCode::CE_FORBIDDEN);
                }

                if (!CsrfField::check($request)) {
                    $response->sendMessage($messageCrossSiteForgery, HttpCode::CE_NOT_ACCEPTABLE);
                }

                $model = $this->modelDescription
                    ->getFactory()
                    ->fromId($this->model->getId());

                $this->sendResult(
                    $request,
                    $response,
                    $model,
                    NexusEditorAction::UPDATE
                );
            }

            default: {
                $this->handleUnexpectedMethod($request, $response);
                break;
            }
        }
    }
}