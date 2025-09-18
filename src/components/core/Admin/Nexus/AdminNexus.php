<?php

namespace components\core\Admin\Nexus;

use components\core\Message\Message;
use components\core\WebPage\AdminWebPage;
use components\layout\Grid\description\GridDescription;
use components\layout\Grid\Grid;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\database\sql\ModelDescription;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\route\Path;
use core\route\Route;
use core\route\RouteNode;
use core\url\Url;
use core\view\ContainerContent;
use core\view\View;

class AdminNexus extends ContainerContent {
    public const LEXICON_GROUP = 'admin.nexus';
    public const COLUMN_EDIT = 'nexus_edit';
    public const COLUMN_DELETE = 'nexus_delete';



    protected AdminWebPage $webPage;
    protected ?Path $urlPath = null;
    protected NexusLinkCreator $linkCreator;
    protected bool $showCreateButton = true;



    public function __construct(
        protected ModelDescription $modelDescription,
        protected Editor $editor,
        protected GridDescription $gridDescription,
        protected ?string $title = null,
        protected ?string $createButtonLabel = null
    ) {
        parent::__construct($this->webPage = new AdminWebPage());
        $this->setLexiconGroup(self::LEXICON_GROUP);

        $this->editor->setContext($this);
        $this->linkCreator = DefaultLinkCreator::getInstance();

        $this->createButtonLabel ??= $this->tr('Create');
    }



    public function showCreateButton(bool $show): static {
        $this->showCreateButton = $show;
        return $this;
    }

    public function setLinkCreator(NexusLinkCreator $creator): static {
        $this->linkCreator = $creator;
        return $this;
    }

    public function getModelDescription(): ModelDescription {
        return $this->modelDescription;
    }

    public function getGridDescription(): GridDescription {
        return $this->gridDescription;
    }

    public function getEditor(): Editor {
        return $this->editor;
    }

    public function setEditor(Editor $editor): static {
        $this->editor = $editor;
        $this->editor->setContext($this);
        return $this;
    }

    public function createGrid(): ?Grid {
        $proxy = $this->gridDescription->getProxy() ?? new NexusProxy();

        if ($proxy instanceof NexusProxy) {
            $proxy->setContext($this);
        }

        return $this->gridDescription->createGrid($proxy);
    }

    public function getGrid(): View {
        $grid = $this->createGrid();

        if (is_null($grid)) {
            return new Message($this->tr("Could not create table, because the description is empty"));
        }

        return $grid
            ->addAsFirst(self::COLUMN_EDIT, $this->tr('Edit'), '64px')
            ->add(self::COLUMN_DELETE, $this->tr('Delete'), '64px')
            ->load($this->gridDescription->getLoader()->load($grid));
    }

    public function getTitle(): string {
        if (is_null($this->title)) {
            $segment = $this->routeNode->getSegment();
            return $this->tr($segment->getLabel() ?? $segment->getSource());
        }

        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
    }

    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();

        $this->urlPath = Path::from(App::getInstance()->attach($router->getRoute()->toStaticPath()));
        $router->use('/create', $this->editor);

        $factory = $this->modelDescription->getFactory();

        $router->use(
            Route::from('/update/[id]'),
            function (Request $request) use ($factory) {
                $id = $request->getParam()->get('id');

                return $this->editor
                    ->setModel($factory->fromId($id));
            }
        );

        $router->use(
            Route::from('/[id]'),
            Http::delete(function (Request $request, Response $response) use ($factory) {
                $model = $factory->fromId(
                    $request->getParam()->get('id')
                );

                $model->delete();

                $response->setStatus(HttpCode::S_OK);
                $response->flush();
            })
        );
    }

    public function getLink(): ?Url {
        if (is_null($this->urlPath)) {
            return null;
        }

        return App::getInstance()->getRequest()->getUrl()
            ->copy()
            ->setPath($this->urlPath);
    }

    public function getEditorLink(): ?Url {
        return $this->getCreateLink();
    }

    public function getCreateLink(): ?Url {
        if (is_null($this->urlPath)) {
            return null;
        }

        return $this->linkCreator->getCreateUrl(
            Path::merge($this->urlPath, 'create')
        );
    }

    public function getUpdateLink(mixed $id): ?Url {
        if (is_null($this->urlPath)) {
            return null;
        }

        return $this->linkCreator->getUpdateUrl(
            Path::merge($this->urlPath, 'update', $id),
            $id
        );
    }

    public function getDeleteLink(string $id): ?string {
        if (is_null($this->urlPath)) {
            return null;
        }

        return $this->linkCreator->getDeleteUrl(
            Path::merge($this->urlPath, $id),
            $id
        );
    }

    public function perform(Request $request, Response $response): void {
        $this->webPage
            ->getHead()
            ->setTitle($this->getTitle());

        switch ($request->getHttpMethod()) {
            case HttpMethod::GET: {
                parent::perform($request, $response);
            }

            default: {
                $response->sendMessage(
                    'Invalid HTTP method ' . $request->getHttpMethod(),
                    HttpCode::CE_BAD_REQUEST
                );
            }
        }
    }
}