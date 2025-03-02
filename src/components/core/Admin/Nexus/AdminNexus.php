<?php

namespace components\core\Admin\Nexus;

use components\core\Admin\Menu\AdminMenu;
use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\WebPage\WebPage;
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

class AdminNexus extends ContainerContent {
    protected WebPage $page;
    protected ?string $urlPath = null;



    public function __construct(
        protected Schema $schema,
        protected ?string $title = null
    ) {
        parent::__construct($this->page = new WebPage());
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

        $leaf->use('/create', new AdminNexusEditor($this));

        $leaf->use(
            Path::from('/update/[id]'),
            Http::get(fn(Request $request, Response $response) => $response->send('editor - update'))
        );

        $leaf->use(
            Path::from('/[id]'),
            Http::delete(fn(Request $request, Response $response) => $response->send('delete'))
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