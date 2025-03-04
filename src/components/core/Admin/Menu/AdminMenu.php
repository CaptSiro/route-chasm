<?php

namespace components\core\Admin\Menu;

use Closure;
use components\core\Admin\Menu\Item\AdminMenuItem;
use components\core\BreadCrumbs\BreadCrumb;
use components\core\BreadCrumbs\BreadCrumbs;
use components\core\Menu\Menu;
use core\AdminRouter;
use core\App;
use core\endpoints\Endpoint;
use core\path\Path;
use core\Singleton;
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
            ->menu
            ->getGraph()
            ->getPathSource(self::getRequestPath());
    }

    public static function getBreadCrumbs(?string $path = null): BreadCrumbs {
        if (is_null($path)) {
            $path = self::getRequestPath();
        }

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



    protected Menu $menu;
    protected array $icons;

    public function __construct() {
        $this->icons = [];

        $this->menu = new Menu(
            itemTemplate: new AdminMenuItem($this->icons)
        );

        $this->menu->setIsInset(false);
        $this->menu->setIsExpanded(true);
        $this->menu->setSelected(
            Path::fromStringArray(Arrays::explode('/', self::getRequestPath()))
        );
    }

    public function add(string $path, Closure|Endpoint ...$endpoints): static {
        $this->menu->add($path, true);

        $urlPath = implode('/', $this->menu->translatePathToTarget($path));
        AdminRouter::getInstance()
            ->use($urlPath, ...$endpoints);

        return $this;
    }

    /**
     * @param array<AdminMenuLabel> $path
     * @param Closure|Endpoint ...$endpoints
     * @return static
     */
    public function addIcons(array $path, Closure|Endpoint ...$endpoints): static {
        $labels = '';

        foreach ($path as $item) {
            if ($item->hasIcon()) {
                $this->addIcon($item->getLabel(), $item->getIcon());
            }

            $labels .= '/'. $item->getLabel();
        }

        return $this->add($labels, ...$endpoints);
    }

    public function addIcon(string $label, string $icon): static {
        $this->icons[$label] = $icon;
        return $this;
    }
}