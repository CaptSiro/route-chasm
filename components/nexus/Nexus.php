<?php

namespace components\nexus;

use components\forms\description\FormDescription;
use components\layout\Dashboard\Dashboard;
use components\layout\Dashboard\DashboardContent;
use components\layout\Grid\description\GridDescription;
use components\layout\Grid\GridLayoutFactory;
use components\Message\Message;
use components\Message\MessageType;
use core\database\sql\ModelDescription;
use core\Dispatcher;
use core\http\Http;
use core\locale\Lexicon;
use core\locale\LexiconUnit;
use core\route\Path;
use core\route\Route;
use core\route\RouteNode;
use core\url\Url;
use core\view\Controller;
use core\view\Renderer;
use core\view\View;
use core\view\ViewTemplateSlotTrait;
use models\Privilege\Privilege;
use models\UserResource;

class Nexus extends Controller implements DashboardContent {
    use ViewTemplateSlotTrait, Dispatcher, LexiconUnit;

    public const LEXICON_GROUP = 'nexus';

    public const COLUMN_EDIT = 'nexus_edit';
    public const COLUMN_DELETE = 'nexus_delete';

    public const SLOT_HEADER = 'nexus:header';
    public const SLOT_BREAD_CRUMBS = 'nexus:bread-crumbs';
    public const SLOT_OVERVIEW = 'nexus:overview';
    public const SLOT_FOOTER = 'nexus:footer';

    public const EVENT_EXTENSION_ADDED = 'nexus:extension-added';



    public static function fromModel(string $modelClass, ?UserResource $resource = null): self {
        $ret = self::fromEditor(
            ModelDescription::extract($modelClass),
            FormDescription::getEditor($modelClass),
            GridDescription::extract($modelClass),
        );

        $ret->setUserResource($resource);
        return $ret;
    }

    public static function createOverviewFromGridLayout(
        Nexus $nexus,
        GridLayoutFactory $gridFactory,
        bool $addControlColumns = true
    ): View {
        $g = self::LEXICON_GROUP;
        $messageGridNotCreated = Lexicon::translate($g, 'Grid description has zero columns');

        $proxy = $gridFactory->getProxy() ?? new NexusProxy();
        if ($proxy instanceof NexusProxy) {
            $proxy->setContext($nexus);
        }

        if (is_null($grid = $gridFactory->createGrid($proxy))) {
            return new Message($messageGridNotCreated, MessageType::ERROR);
        }

        if ($addControlColumns) {
            $grid
                ->addAsFirst(self::COLUMN_EDIT, Lexicon::translate($g, 'Edit'), '64px')
                ->add(self::COLUMN_DELETE, Lexicon::translate($g, 'Delete'), '80px');
        }

        return $grid->load(
            $gridFactory->getLoader()->load($grid)
        );
    }

    public static function fromEditor(
        ModelDescription $modelDescription,
        NexusEditor $editor,
        GridLayoutFactory $gridFactory,
        ?string $createButtonLabel = null,
    ): self {
        $factory = $modelDescription->getFactory();

        $nexus = new self(
            NexusActions::fromEditor($modelDescription, $editor)
        );

        $editor->setContext($nexus);

        if (!is_null($createButtonLabel)) {
            $createButtonLabel = Lexicon::translate(self::LEXICON_GROUP, $createButtonLabel);
        }

        $nexus->setTemplateSlot(
            self::SLOT_HEADER,
            new NexusHeader($nexus, $createButtonLabel)
        );

        $nexus->setTemplateSlot(
            self::SLOT_OVERVIEW,
            self::createOverviewFromGridLayout($nexus, $gridFactory)
        );

        return $nexus;
    }

    public static function fromBehavior(
        ModelDescription $modelDescription,
        NexusEditorBehavior $behavior,
        GridLayoutFactory $gridFactory,
        ?string $createButtonLabel = null,
    ): self {
        return self::fromEditor(
            $modelDescription,
            new NexusEditor($modelDescription, $behavior),
            $gridFactory,
            $createButtonLabel
        );
    }



    /**
     * @var array<NexusExtension>
     */
    protected array $extensions = [];
    protected NexusUrlCreator $urlCreator;
    protected ?Dashboard $dashboard = null;

    public function __construct(
        protected ?NexusActions $actions = null,
        ?Renderer $renderer = null
    ) {
        parent::__construct($renderer);

        $this->setLexiconGroup(self::LEXICON_GROUP);

        $this->actions ??= NexusActions::none();
        $this->urlCreator = DefaultNexusUrlCreator::getInstance();
    }



    public function setUrlCreator(NexusUrlCreator $urlCreator): static {
        $this->urlCreator = $urlCreator;
        return $this;
    }

    /**
     * @return array<NexusExtension>
     */
    public function getExtensions(): array {
        return $this->extensions;
    }

    public function addExtension(NexusExtension $extension): static {
        $this->extensions[] = $extension;
        if (!is_null($this->routeNode)) {
            $extension->onBind($this, $this->routeNode->getRouter());
        }

        $this->dispatch(self::EVENT_EXTENSION_ADDED, $extension);
        return $this;
    }

    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();

        if (!is_null($create = $this->actions->getCreate())) {
            $router->use(
                '/create',
                $this->createBlockMiddleware(Privilege::fromName(Privilege::CREATE)),
                $create
            );
        }

        if (!is_null($update = $this->actions->getUpdate())) {
            $router->use(
                Route::from('/update/[id]'),
                $this->createBlockMiddleware(Privilege::fromName(Privilege::UPDATE)),
                $update,
            );
        }

        if (!is_null($delete = $this->actions->getDelete())) {
            $router->use(
                Route::from('/[id]'),
                Http::delete(
                    $this->createBlockMiddleware(Privilege::fromName(Privilege::UPDATE)),
                    $delete
                )
            );
        }

        foreach ($this->extensions as $extension) {
            $extension->onBind($this, $router);
        }
    }

    public function getUrl(): ?Url {
        return $this->createUrl();
    }

    public function getCreateUrl(): ?Url {
        return $this->createUrl('create');
    }

    public function getUpdateUrl(string $id): ?Url {
        return $this->createUrl(Path::join('update', $id));
    }

    public function getDeleteUrl(string $id): ?Url {
        return $this->createUrl(Path::join($id));
    }



    // DashboardContent
    public function setDashboard(Dashboard $dashboard): static {
        $this->dashboard = $dashboard;
        return $this;
    }

    public function getDashboard(): ?Dashboard {
        return $this->dashboard;
    }
}