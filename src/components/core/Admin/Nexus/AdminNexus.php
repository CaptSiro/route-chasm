<?php

namespace components\core\Admin\Nexus;

use components\core\BreadCrumbs\BreadCrumbs;
use components\core\Message\Message;
use components\core\WebPage\AdminWebPage;
use components\layout\Grid\GridLayout;
use components\layout\Grid\GridLayoutFactory;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\database\sql\Model;
use core\database\sql\ModelDescription;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\route\Path;
use core\route\Route;
use core\route\RouteNode;
use core\route\Router;
use core\url\Url;
use core\view\ContainerContent;
use core\view\View;
use models\core\Privilege\Privilege;
use models\core\UserResource;
use models\core\User\User;

class AdminNexus extends ContainerContent {
    public const LEXICON_GROUP = 'admin.nexus';
    public const COLUMN_EDIT = 'nexus_edit';
    public const COLUMN_DELETE = 'nexus_delete';



    protected AdminWebPage $webPage;
    protected ?Path $urlPath = null;
    protected ?BreadCrumbs $breadCrumbs = null;
    protected NexusLinkCreator $linkCreator;
    protected bool $showCreateButton = true;
    protected bool $showHeader = true;
    protected bool $doAddGridControls = true;
    protected ?View $headerContent = null;



    public function __construct(
        protected ModelDescription $modelDescription,
        protected Editor $editor,
        protected GridLayoutFactory $gridFactory,
        protected ?UserResource $userResource = null,
        protected ?string $title = null,
        protected ?string $createButtonLabel = null
    ) {
        parent::__construct($this->webPage = new AdminWebPage());
        $this->setLexiconGroup(self::LEXICON_GROUP);

        $this->editor->setContext($this);
        $this->linkCreator = DefaultLinkCreator::getInstance();

        $this->createButtonLabel ??= $this->tr('Create');
    }



    public function hasAccess(string $privilegeName): bool {
        if (is_null($this->userResource)) {
            return true;
        }

        $request = App::getInstance()
            ->getRequest();
        $user = User::fromRequest($request);
        if (is_null($user)) {
            return false;
        }

        return $user->hasAccess($this->userResource, Privilege::fromName($privilegeName));
    }

    public function setRouter(Route $route, Router $router): bool {
        if (!$this->hasAccess(Privilege::READ)) {
            return false;
        }

        $router->use($route, $this);
        return true;
    }

    public function showCreateButton(bool $show): static {
        $this->showCreateButton = $show;
        return $this;
    }

    public function showHeader(bool $show): static {
        $this->showHeader = $show;
        return $this;
    }

    public function doAddGridControls(bool $do): static {
        $this->doAddGridControls = $do;
        return $this;
    }

    public function setHeaderContent(View $headerContent): static {
        $this->headerContent = $headerContent;
        return $this;
    }

    public function setLinkCreator(NexusLinkCreator $creator): static {
        $this->linkCreator = $creator;
        return $this;
    }

    public function getModelDescription(): ModelDescription {
        return $this->modelDescription;
    }

    public function getGridFactory(): GridLayoutFactory {
        return $this->gridFactory;
    }

    public function getBreadCrumbs(): ?BreadCrumbs {
        return $this->breadCrumbs;
    }

    public function setBreadCrumbs(?BreadCrumbs $breadCrumbs): static {
        $this->breadCrumbs = $breadCrumbs;
        return $this;
    }

    public function getEditor(): Editor {
        return $this->editor;
    }

    public function setEditor(Editor $editor): static {
        $this->editor = $editor;
        $this->editor->setContext($this);
        return $this;
    }

    public function createGrid(): ?GridLayout {
        $proxy = $this->gridFactory->getProxy() ?? new NexusProxy();

        if ($proxy instanceof NexusProxy) {
            $proxy->setContext($this);
        }

        return $this->gridFactory->createGrid($proxy);
    }

    public function getGrid(): View {
        $grid = $this->createGrid();

        if (is_null($grid)) {
            return new Message($this->tr("Could not create table, because the description is empty"));
        }

        if ($this->doAddGridControls) {
            $grid
                ->addAsFirst(self::COLUMN_EDIT, $this->tr('Edit'), '64px')
                ->add(self::COLUMN_DELETE, $this->tr('Delete'), '64px');
        }

        return $grid
            ->load($this->gridFactory->getLoader()->load($grid));
    }

    public function getTitle(): string {
        if (is_null($this->title)) {
            $segment = $this->routeNode->getSegment();
            return $this->tr($segment->getLabel() ?? $segment->getSource());
        }

        return $this->title;
    }

    public function setTitle(?string $title): static {
        $this->title = $title;
        return $this;
    }

    public function createModel(mixed $id): ?Model {
        return $this->modelDescription
            ->getFactory()
            ->fromId($id);
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
                if (!$this->hasAccess(Privilege::READ)) {
                    $response->sendStatus(HttpCode::CE_FORBIDDEN);
                }

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