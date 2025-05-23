<?php

namespace core\actions;

use core\communication\Request;
use core\communication\Response;
use core\route\RouteNode;

interface Action {
    public function getRouteNode(): ?RouteNode;



    public function isMiddleware(): bool;

    public function getActorName(): string;

    public function onBind(RouteNode $bindingPoint): void;

    public function act(Request $request, Response $response): void;



    // todo
    // add something like getUrl(): Url but it is on Request. The Request should be responsible how deep the action is
}