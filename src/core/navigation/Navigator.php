<?php

namespace core\navigation;

use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\mounts\MountLocation;
use core\route\Path;
use core\route\RouteNode;
use core\route\Router;
use core\route\RouteTree;
use core\view\Component;
use models\core\Language\Language;
use models\core\Navigation\NavigationContext;
use models\core\Navigation\NavigationFactoryRecord;
use models\core\Navigation\Slug;
use RuntimeException;

class Navigator extends Router {
    use MountLocation;

    /** @var $factories array<NavigationFactory> */
    private static array $factories = [];

    public static function register(NavigationFactory $factory): void {
        $factoryRecord = NavigationFactoryRecord::fromName($factory->getName(), create: true);
        self::$factories[$factoryRecord->id] = $factory;
    }

    public static function build(int $factoryId, string $data): Component {
        if (!isset(self::$factories[$factoryId])) {
            throw new RuntimeException("Factory ($factoryId) is not loaded");
        }

        return self::$factories[$factoryId]->createDestination($data);
    }



    public static function addSlug(Language $language, Path $path, NavigationFactory $factory, string $data, ?string $context = null): void {
        $contextId = NavigationContext::getContextId($context);
        $parentId = null;
        $last = $path->getDepth() - 1;

        foreach ($path as $i => $segment) {
            $s = Slug::fromSlug($language, $contextId, $segment, $parentId);

            if (is_null($s)) {
                $s = new Slug();

                $s->setLanguage($language);
                $s->setParentId($parentId);
                $s->contextId = $contextId;
                $s->slug = $segment;

                if ($i === $last) {
                    $s->setFactory($factory, $data);
                }

                $s->save();
            }

            $parentId = $s->id;
        }
    }

    public static function getDestination(Language $language, Path $path, ?string $context = null): ?Component {
        /** @var Slug|null $slug */
        $slug = null;
        $contextId = NavigationContext::getContextId($context);
        $parentId = null;
        $last = $path->getDepth() - 1;

        foreach ($path as $segment) {
            $slug = Slug::fromSlug($language, $contextId, $segment, $parentId);
            if (is_null($slug)) {
                return null;
            }

            $parentId = $slug->id;
        }

        return $slug?->build();
    }



    public function __construct(
        protected ?string $context = null,
        ?RouteTree $structure = null
    ) {
        parent::__construct($structure);
    }

    protected function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $this->use(
            '/**',
            fn(Request $request, Response $response) => $this->resolve($request, $response)
        );
    }



    public function setContext(?string $context): void {
        $this->context = $context;
    }

    public function resolve(Request $request, Response $response): Component {
        $path = $request->getRemainingPath();
        $language = $request->getLanguage();

        $destination = self::getDestination($language, $path, $this->context);
        if (is_null($destination)) {
            $response->setStatus(HttpCode::CE_NOT_FOUND);
            $response->send('Resource not found');
        }

        return $destination;
    }
}