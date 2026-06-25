<?php

namespace components\Dashboard;

use components\layout\Menu\Menu;
use components\layout\RoutedMenu\RoutedMenu;
use components\Message\Message;
use components\Message\MessageType;
use core\actions\Barrier;
use core\actions\Procedure;
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
use core\view\Component;
use core\view\View;
use models\Privilege\Privilege;
use models\User\User;
use models\UserResource;
use RuntimeException;

abstract class Dashboard extends Router {
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



    public function getDashboardLogin(): DashboardLogin {
        return $this->dashboardLogin;
    }

    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();

        $router->use('/',
            Procedure::middleware(function (Request $request) {
                $request->set(self::KEY_DASHBOARD_ID, $this->getInstanceId());
            }),

            // middleware
            $this->dashboardLogin,

            // if it is just '/' render default or message 'Dashboard'
            new When(
                fn(Request $request) => $request->getRemainingPath()->getDepth() === 0,
                $this->default
                    ?? DashboardPage::fromMessage($this, new Message('Welcome to dashboard', MessageType::INFO))
            ),
        );

        $router->use('/**',
            fn(Request $request, Response $response)
                => $response->render(DashboardPage::notFound($this, $request->getRemainingPath()))
        );
    }

    public function add(Route $route, Component $component, ?UserResource $resource = null): static {
        if (!is_null($resource)) {
            $component->setUserResource($resource);
        }

        if (!$this->hasRequestAccess(Privilege::fromName(Privilege::READ))) {
            return $this;
        }

        if ($component instanceof DashboardContent) {
            $component->setDashboard($this);
        }

        $this->use($route, $component);
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