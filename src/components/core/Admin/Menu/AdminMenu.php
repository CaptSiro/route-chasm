<?php

namespace components\core\Admin\Menu;

use Closure;
use components\core\Terminal\Terminal;
use core\AdminRouter;
use core\endpoints\Endpoint;
use core\Singleton;
use core\translation\UrlPathTranslator;
use core\url\UrlGraph;
use core\view\Renderer;
use core\view\View;

class AdminMenu implements View {
    use Renderer, Singleton;

    public static function load(string $file): void {
        require_once $file;
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
}