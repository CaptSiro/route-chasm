<?php

namespace components\core\Admin\Menu;

use Closure;
use components\core\Admin\Menu\Item\AdminMenuItem;
use components\core\BreadCrumbs\BreadCrumb;
use components\core\BreadCrumbs\BreadCrumbs;
use components\core\Menu\Menu;
use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\actions\ActorClassName;
use core\actions\Procedure;
use core\AdminRouter;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\route\Path;
use core\route\RouteNode;
use core\Singleton;
use core\url\UrlGraph;
use core\utils\Arrays;
use core\view\Renderer;
use core\view\View;

class AdminMenu implements View, Action {
    use Renderer, Singleton, ActionBindRouteNode, ActorClassName;

    public static function load(string $file): void {
        require_once $file;
    }



    /**
     * @var Menu<array<Action>>
     */
    protected Menu $menu;
    protected array $icons;
    protected Path $requestPath;

    public function __construct() {
        $this->requestPath = Path::from("");
        $this->icons = [];

        $this->menu = new Menu(
            itemTemplate: new AdminMenuItem($this->icons)
        );

        $this->menu->setIsInset(false);
        $this->menu->setIsExpanded(true);
    }



    public function add(string|Path $path, Closure|Action ...$actions): static {
        $this->menu->add(Path::resolve($path), Procedure::resolve($actions));
        return $this;
    }

    /**
     * @param array<AdminMenuLabel> $path
     * @param Closure|Action ...$actions
     * @return static
     */
    public function addIcons(array $path, Closure|Action ...$actions): static {
        $labels = '';

        foreach ($path as $item) {
            if ($item->hasIcon()) {
                $this->addIcon($item->getLabel(), $item->getIcon());
            }

            $labels .= '/'. $item->getLabel();
        }

        return $this->add($labels, ...$actions);
    }

    public function addIcon(string $label, string $icon): static {
        $this->icons[$label] = $icon;
        return $this;
    }

    public function getRequestPathSource(): Path {
        return Path::from($this->menu->getGraph()->getPathSource($this->requestPath));
    }

    public function getBreadCrumbs(?Path $path = null): BreadCrumbs {
        $path ??= $this->requestPath;

        $admin = AdminRouter::getInstance()->getPath();
        $app = App::getInstance();
        $node = self::getInstance()
            ->menu
            ->getGraph()
            ->getRoot();

        $crumbs = [
            new BreadCrumb('home', $app->prependHome($admin))
        ];

        $source = Arrays::explode('/', self::getInstance()
            ->menu
            ->getGraph()
            ->getPathSource($path));
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



    // Action
    public function isMiddleware(): bool {
        return false;
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function perform(Request $request, Response $response): void {
        $this->requestPath = $request->getRemainingPath();

        $this->menu->setSelected($this->requestPath);
        $actions = $this->menu->getGraph()->get($this->requestPath);
        if (is_null($actions)) {
            return;
        }

        foreach ($actions as $action) {
            $action->perform($request, $response);
        }
    }
}