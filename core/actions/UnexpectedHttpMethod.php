<?php

namespace core\actions;

use core\communication\Request;
use core\communication\Response;
use core\http\HttpCode;
use core\utils\Objects;

trait UnexpectedHttpMethod {
    protected UnexpectedHttpMethodPolicy $policy = UnexpectedHttpMethodPolicy::TERMINATE;



    public function setUnexpectedMethodPolicy(UnexpectedHttpMethodPolicy $policy): void {
        $this->policy = $policy;
    }

    public function handleUnexpectedMethod(Request $request, Response $response): void {
        switch ($this->policy) {
            case UnexpectedHttpMethodPolicy::IGNORE: {
                break;
            }

            case UnexpectedHttpMethodPolicy::TERMINATE: {
                $response->sendMessage(
                    'Invalid HTTP method '. $request->getHttpMethod() .' (Action: '. Objects::getClass($this) .')',
                    HttpCode::CE_BAD_REQUEST
                );

                break;
            }
        }
    }
}