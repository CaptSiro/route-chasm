<?php

namespace components\layout\Dashboard;

use components\layout\Menu\Menu;
use components\layout\RoutedMenu\RoutedMenu;
use components\Message\Message;
use components\Message\MessageType;
use components\NotFound;
use core\actions\Barrier;
use core\actions\Procedure;
use core\actions\UserResourceBarrier;
use core\actions\When;
use core\communication\Request;
use core\communication\Response;
use core\InstanceCounter;
use core\locale\LexiconUnit;
use core\mounts\Mount;
use core\mounts\StaticMount;
use core\route\Path;
use core\route\Route;
use core\route\RouteNode;
use core\route\Router;
use core\utils\Objects;
use core\view\Component;
use core\view\Controller;
use core\view\PageView;
use core\view\PageViewFactory;
use core\view\View;
use models\Privilege\Privilege;
use models\User\User;
use models\UserResource;
use RuntimeException;

abstract class Dashboard extends Router implements UserResourceBarrier {
    use InstanceCounter, Barrier, LexiconUnit;



    public const LEXICON_GROUP = 'dashboard';

    public const KEY_DASHBOARD_ID = 'dashboard-id';



    private View $sideBar;
    protected DashboardLogin $dashboardLogin;
    protected Path $path;
    protected Mount $mount;

    public function __construct(
        protected ?View $default = null,
    ) {
        parent::__construct();
        $this->setInstanceId();
        $this->setLexiconGroup(self::LEXICON_GROUP);

        $this->dashboardLogin = new DashboardLogin($this);
    }



    /**
     * Returns the name of the class by default.
     *
     * Provide label for the dashboard. Translation of the label is not necessary
     *
     * @return string
     */
    public function getDashboardLabel(): string {
        return Objects::getBaseClass(static::class);
    }

    public function createPageView(): PageView {
        return PageViewFactory::getDefaultFactory()
            ->create();
    }

    public function getDashboardLogin(): DashboardLogin {
        return $this->dashboardLogin;
    }

    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();
        $greetings = new Message($this->tr('Welcome to ' . $this->getDashboardLabel()), MessageType::INFO);

        $router->use('/',
            Procedure::middleware(function (Request $request) {
                $request->set(self::KEY_DASHBOARD_ID, $this->getInstanceId());
                PageViewFactory::setDefaultFactory(new DashboardPageViewFactory($this));
//                throw new RuntimeException('hey hey test');
            }),

            // Used as middleware
            $this->dashboardLogin,

            // If it is just '/' render default or message 'Dashboard'
            new When(
                fn(Request $request) => $request->getRemainingPath()->getDepth() === 0,
                $this->default
                    ?? $this->createPageView()->setComponent($greetings)
            ),
        );

        $router->use('/**',
            function (Request $request, Response $response) {
                $notFound = new NotFound();
                $notFound->setTitle($request->getRemainingPath());
                $response->render($this->createPageView()->setComponent($notFound));
            }
        );
    }

    public function add(
        Route $route,
        Component $component,
        ?UserResource $resource = null,
        ?PageView $pageView = null
    ): static {
        $pageView ??= new DashboardPageView($this);

        if (!$this->hasRequestAccess(Privilege::fromName(Privilege::READ))) {
            return $this;
        }

        if (is_null($component->getTitle()) && !is_null($last = $route->getLast())) {
            $component->setTitle($this->tr($this->getDashboardLabel() . ' - ' . $last->getLabel(true)));
        }

        if ($component instanceof DashboardContent) {
            $component->setDashboard($this);
        }

        if ($component instanceof Controller) {
            if (!is_null($resource)) {
                $component->setUserResource($resource);
            }

            $this->use($route, $component);
            return $this;
        }

        $this->use($route, $pageView->setComponent($component));
        return $this;
    }

    public function mount(Route|string $route, Mount $mount = new StaticMount()): Route {
        $mount->setMountingPoint($route = Route::resolve($route));
        $this->mount = $mount;
        return $route;
    }

    public function getPath(): Path {
        if (!isset($this->path)) {
            if (!isset($this->mount)) {
                throw new RuntimeException(
                    'Cannot create path to the dashboard because the mounting point is unknown. '
                    . 'Use Dashboard::mount when calling Router::bind(Route,Dashboard)'
                );
            }

            $this->path = $this->mount->transform($this->getRoute());
        }

        return $this->path;
    }

    public function getSideBar(): View {
        if (isset($this->sideBar)) {
            return $this->sideBar;
        }

        return $this->sideBar = $this->createDashboardSideBar(
            RoutedMenu::from($this)
        );
    }



    abstract public function createDashboardSideBar(Menu $menu): View;

    public function authenticate(?User $user): bool {
        if (is_null($this->getUserResource())) {
            throw new RuntimeException('Cannot perform default user authentication because UserResource is not set');
        }

        return $this->hasAccess($user, Privilege::fromName(Privilege::READ));
    }
}