<?php

namespace components\core\Admin\Nexus;

use components\core\Admin\Menu\AdminMenu;
use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\Admin\Nexus\Editor\Editor;
use components\core\Message\Message;
use components\core\WebPage\WebPage;
use components\layout\Table\Table;
use components\layout\Table\TableLayout;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\database\Schema;
use core\http\Http;
use core\http\HttpCode;
use core\http\HttpMethod;
use core\path\Path;
use core\Router;
use core\utils\Arrays;
use core\view\ContainerContent;
use core\view\View;

class AdminNexus extends ContainerContent {
    public const COLUMN_EDIT = 'nexus_edit';
    public const COLUMN_DELETE = 'nexus_delete';



    protected WebPage $page;
    protected ?string $urlPath = null;
    protected ?TableLayout $layout = null;
    protected ?Editor $editor;



    public function __construct(
        protected Schema $schema,
        protected ?string $title = null
    ) {
        parent::__construct($this->page = new WebPage());
        $this->setEditor(new AdminNexusEditor());
    }



    public function setEditor(Editor $editor): static {
        $this->editor = $editor;
        $this->editor->setContext($this);
        return $this;
    }

    public function setTableLayout(?TableLayout $layout): static {
        $this->layout = $layout;
        return $this;
    }

    public function createTable(): ?Table {
        $layout = $this->layout ?? $this->schema->createDefaultTableLayout();
        $proxy = $layout->getProxy() ?? new NexusProxy();

        if ($proxy instanceof NexusProxy) {
            $proxy->setContext($this);
        }

        return $layout->createTable($proxy);
    }

    public function getTable(): View {
        $table = $this->createTable();

        if (is_null($table)) {
            return new Message("Could not create table, because the layout is empty");
        }

        return $table
            ->addAsFirst('Edit', self::COLUMN_EDIT)
            ->add('Delete', self::COLUMN_DELETE)
            ->load($this->schema->getEntityFactory()->fetchAll());
    }

    public function getTitle(): string {
        if (is_null($this->title)) {
            $segments = Arrays::explode('/',  AdminMenu::getRequestPathSource());
            return array_pop($segments);
        }

        return $this->title;
    }

    public function getSchema(): Schema {
        return $this->schema;
    }

    public function onContextBind(Router $leaf): void {
        $this->urlPath = App::getInstance()->prependHome($leaf->getUrlPath());

        $leaf->use('/create', $this->editor);

        $factory = $this->schema->getEntityFactory();

        $leaf->use(
            Path::from('/update/[id]'),
            fn(Request $request, Response $response) => $this->editor
                ->setEntity($factory->fromId(
                    $request->getParam()->get('id')
                ))
        );

        $leaf->use(
            Path::from('/[id]'),
            Http::delete(function (Request $request, Response $response) use ($factory) {
                $entity = $factory->fromId(
                    $request->getParam()->get('id')
                );

                $entity->delete();

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