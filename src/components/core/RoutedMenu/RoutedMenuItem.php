<?php

namespace components\core\RoutedMenu;

use components\core\Menu\Menu;
use components\core\Menu\MenuItem;
use components\core\Menu\MenuItemContext;
use core\App;
use core\locale\LexiconUnit;
use core\route\Path;
use core\route\Route;
use core\route\RouteNode;
use core\route\Router;
use core\route\RouteSegment;
use core\view\Renderer;
use RuntimeException;

class RoutedMenuItem implements MenuItem {
    use Renderer, MenuItemContext, LexiconUnit;



    public static function from(Router $router, ?Path $binding = null): static {
        return new static(
            $router->getStructure()->getRoot()->get(),
            Path::empty(),
            $binding
        );
    }

    protected static function fromRouteNode(RouteNode $node, ?Path $binding = null): static {
        $segment = Path::empty();
        $label = null;
        $icon = null;
        $edge = $node->getVertex()->getParentEdge()->get();

        if ($edge instanceof RouteSegment) {
            $source = $edge->getSource();
            $label = $edge->getLabel() ?? $source;

            if (Route::isDynamic($source)) {
                $class = self::class;
                throw new RuntimeException("Route for RoutedMenu cannot be dynamic. The violating segment is '$source'");
            }

            $segment = Path::from($source);

            if (!is_null($edge->getIcon())) {
                $icon = $edge->getIcon();
            }
        }

        return new static(
            $node,
            $segment,
            $binding,
            $label,
            $icon
        );
    }



    protected array $children;

    public function __construct(
        protected RouteNode $node,
        protected Path $segment,
        protected ?Path $binding = null,
        protected ?string $label = null,
        protected ?string $icon = null,
    ) {
        $this->setLexiconGroup(Menu::LEXICON_GROUP);
        $this->children = [];

        foreach ($this->node->getVertex()->getEdges() as $edge) {
            $s = $edge->get();
            if (!($s instanceof RouteSegment)) {
                continue;
            }

            if (!$s->getMetadata(Route::MENU_ROUTE)) {
                continue;
            }

            $this->children[] = self::fromRouteNode($edge->getVertex()->get());
        }
    }



    public function hasChildren(): bool {
        return !empty($this->children);
    }

    public function getChildren(): array {
        return $this->children;
    }

    public function hasItem(): bool {
        return !empty($this->node->getActions());
    }

    public function createUrl(): string {
        return App::getInstance()->attach(
            is_null($this->binding)
                ? $this->context->getPath()
                : Path::merge($this->binding, $this->context->getPath())
        );
    }

    public function getLabel(): string {
        return $this->label ?? "";
    }

    public function getMenuSegment(): Path {
        return $this->segment;
    }

    public function getStateClasses(): string {
        return '';
    }
}