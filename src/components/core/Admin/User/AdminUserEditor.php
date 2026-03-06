<?php

namespace components\core\Admin\User;

use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\route\RouteNode;
use core\sideloader\importers\Javascript\Javascript;
use core\url\Url;
use models\core\User\User;

class AdminUserEditor extends AdminNexusEditor {
    public const QUERY_USER_ID = 'user-id';



    public function __construct(EditorBehavior $behaviour) {
        parent::__construct($behaviour);
        $this->setTemplate(AdminNexusEditor::getTemplateResourceStatic());
        Javascript::import($this->getResource('admin-user.js'));
    }



    public function createLoginAsUserUrl(?User $user = null): Url {
        $request = App::getInstance()->getRequest();
        $path = $this->routeNode->getRoute()->toStaticPath()
            ->append('login');

        $ret = $request
            ->getDomain()
            ->createUrl($path);

        $ret->loadTransitiveQueries($request->getUrl()->getQuery());
        if (!is_null($user)) {
            $ret->setQueryArgument(self::QUERY_USER_ID, $user->id);
        }

        return $ret;
    }

    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();
        $router->use('/login', function (Request $request, Response $response) {
            $user = User::fromId(
                intval(
                    $request->getUrl()
                        ->getQuery()
                        ->getStrict(self::QUERY_USER_ID)
                )
            );

            if (is_null($user)) {
                $response->sendStatus(HttpCode::CE_NOT_FOUND);
            }

            $user->login();
            $response->sendStatus(HttpCode::S_OK);
        });
    }
}