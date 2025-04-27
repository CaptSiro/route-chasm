<?php

namespace components\core\Admin\Nexus;

use components\core\Admin\Menu\AdminMenu;
use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\Admin\Nexus\Editor\Editor;
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
use core\path\Path;
use core\Router;
use core\utils\Arrays;
use core\view\ContainerContent;
use core\view\View;
use modules\forms\FormSection;

class AdminNexus extends ContainerContent {
    public const COLUMN_EDIT = 'nexus_edit';
    public const COLUMN_DELETE = 'nexus_delete';



    protected AdminWebPage $page;
    protected ?string $urlPath = null;
    protected ?Editor $editor;



    public function __construct(
        protected ModelDescription $modelDescription,
        protected FormSection $formSection,
        protected GridDescription $gridDescription,
        protected ?string $title = null
    ) {
        parent::__construct($this->page = new AdminWebPage());
        $this->setEditor(new AdminNexusEditor());
    }



    public function getModelDescription(): ModelDescription {
        return $this->modelDescription;
    }

    public function getFormSection(): FormSection {
        return $this->formSection;
    }

    public function getGridDescription(): GridDescription {
        return $this->gridDescription;
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
            return new Message("Could not create table, because the description is empty");
        }

        return $grid
            ->addAsFirst(self::COLUMN_EDIT, 'Edit', '64px')
            ->add(self::COLUMN_DELETE, 'Delete', '64px')
            ->load($this->gridDescription->getLoader()->load($grid));
    }

    public function getTitle(): string {
        if (is_null($this->title)) {
            $segments = Arrays::explode('/',  AdminMenu::getRequestPathSource());
            return array_pop($segments);
        }

        return $this->title;
    }

    public function onContextBind(Router $leaf): void {
        $this->urlPath = App::getInstance()->prependHome($leaf->getUrlPath());

        $leaf->use('/create', $this->editor);

        $factory = $this->modelDescription->getFactory();

        $leaf->use(
            Path::from('/update/[id]'),
            fn(Request $request, Response $response) => $this->editor
                ->setModel($factory->fromId(
                    $request->getParam()->get('id')
                ))
        );

        $leaf->use(
            Path::from('/[id]'),
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

    public function getLink(): ?string {
        return $this->urlPath;
    }

    public function getCreateLink(): ?string {
        if (is_null($this->urlPath)) {
            return null;
        }

        return Path::join($this->urlPath, 'create');
    }

    public function getUpdateLink(string $id): ?string {
        if (is_null($this->urlPath)) {
            return null;
        }

        return Path::join($this->urlPath, 'update', $id);
    }

    public function getDeleteLink(string $id): ?string {
        if (is_null($this->urlPath)) {
            return null;
        }

        return Path::join($this->urlPath, $id);
    }

    public function execute(Request $request, Response $response): void {
        $this->page
            ->getHead()
            ->setTitle($this->getTitle());

        switch ($request->httpMethod) {
            case HttpMethod::GET: {
                parent::execute($request, $response);
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