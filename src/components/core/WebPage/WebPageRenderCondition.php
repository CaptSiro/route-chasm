<?php

namespace components\core\WebPage;

use Closure;
use core\App;
use core\communication\Format;
use core\communication\Request;
use core\communication\Response;

trait WebPageRenderCondition {
    private Closure $condition;



    public static function createHtmlPageCondition(): Closure {
        return fn() => (
            App::getInstance()
                ->getResponse()
                ->getFormat() === Format::IDENT_HTML
        );
    }

    private function initCondition(Closure $condition): void {
        $this->condition = $condition;
    }

    public function perform(Request $request, Response $response): void {
        if (($this->condition)()) {
            parent::perform($request, $response);
            return;
        }

        $response->render($this);
    }
}