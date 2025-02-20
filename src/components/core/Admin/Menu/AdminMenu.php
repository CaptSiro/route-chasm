<?php

namespace components\core\Admin\Menu;

use Closure;
use components\core\BreadCrumb\BreadCrumb;
use components\core\BreadCrumbs\BreadCrumbs;
use components\core\Terminal\Terminal;
use core\AdminRouter;
use core\App;
use core\endpoints\Endpoint;
use core\Singleton;
use core\translation\UrlPathTranslator;
use core\url\UrlGraph;
use core\url\UrlPath;
use core\view\Renderer;
use core\view\View;

class AdminMenu implements View {
    use Renderer, Singleton;

    public static function load(string $file): void {
        require_once $file;
    }

    public static function getBreadCrumbs(?string $path = null): BreadCrumbs {
        $admin = AdminRouter::getInstance()->getPath();

        if (is_null($path)) {
            $path = substr(
                App::getInstance()
                    ->getRequest()
                    ->getUrl()
                    ->getPath(),
                strlen($admin)
            );
        }

        $app = App::getInstance();
        $crumbs = [new BreadCrumb('home', $app->prependHome($admin))];

        $source = UrlPath::segmented(self::getInstance()
            ->getPathSource($path));
        $target = UrlPath::segmented($path);
        $accumulated = $app->prependHome($admin);

        for ($i = 0; $i < count($source); $i++) {
            $accumulated .= '/' . $target[$i];
            $crumbs[] = new BreadCrumb($source[$i], $accumulated);
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
}