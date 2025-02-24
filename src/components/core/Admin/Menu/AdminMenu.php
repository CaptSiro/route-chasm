<?php

namespace components\core\Admin\Menu;

use Closure;
use components\core\Admin\SubMenu\SubMenu;
use components\core\BreadCrumb\BreadCrumb;
use components\core\BreadCrumbs\BreadCrumbs;
use core\AdminRouter;
use core\App;
use core\endpoints\Endpoint;
use core\path\Path;
use core\path\SearchPath;
use core\Singleton;
use core\translation\UrlPathTranslator;
use core\url\UrlGraph;
use core\utils\Arrays;
use core\view\Renderer;
use core\view\View;

class AdminMenu implements View {
    use Renderer, Singleton;

    public static function load(string $file): void {
        require_once $file;
    }

    public static function getRequestPath(): string {
        return substr(
            App::getInstance()
                ->getRequest()
                ->getUrl()
                ->getPath(),
            strlen(AdminRouter::getInstance()->getPath())
        );
    }

    public static function getRequestPathSource(): string {
        return self::getInstance()
            ->getPathSource(self::getRequestPath());
    }

    public static function getBreadCrumbs(?string $path = null): BreadCrumbs {
        if (is_null($path)) {
            $path = self::getRequestPath();
        }

        $admin = AdminRouter::getInstance()->getPath();
        $app = App::getInstance();
        $node = self::getInstance()
            ->graph
            ->getRoot();

        $crumbs = [
            new BreadCrumb('home', $app->prependHome($admin))
        ];

        $source = Arrays::explode('/', self::getInstance()
            ->getPathSource($path));;
        $target = Arrays::explode('/', $path);
        $accumulated = $app->prependHome($admin);

        for ($i = 0; $i < count($source); $i++) {
            if (!is_null($node)) {
                $node = $node[$target[$i]] ?? null;
            }

            $accumulated .= '/' . $target[$i];
            $crumbs[] = new BreadCrumb($source[$i], UrlGraph::isLeaf($node) ? $accumulated : null);
        }

        return new BreadCrumbs($crumbs);
    }



    protected ?View $homeLabel;
    protected UrlPathTranslator $paths;
    protected UrlGraph $graph;

    public function __construct() {
        $this->paths = new UrlPathTranslator();
        $this->graph = new UrlGraph(
            $this->paths->getSegments()
        );
    }



    public function setHomeLabel(?View $homeLabel): static {
        $this->homeLabel = $homeLabel;
        return $this;
    }

    public function add(string $path, Closure|Endpoint ...$endpoints): static {
        $this->graph->add($path);

        $urlPath = implode('/', $this->paths->add($path));
        AdminRouter::getInstance()
            ->use($urlPath, ...$endpoints);

        return $this;
    }

    public function getPathSource(string $path): string {
        return $this->graph->getSource($path);
    }

    public function createSubMenu(?bool &$renderHome): SubMenu {
        $menu = new SubMenu(
            $this->paths->getSegments(),
            '',
            $this->graph->getRoot(),
            selected: Path::fromStringArray(Arrays::explode('/', self::getRequestPath()))
        );

        $menu->inset($renderHome = $menu->hasRender() && !is_null($this->homeLabel));
        return $menu;
    }
}