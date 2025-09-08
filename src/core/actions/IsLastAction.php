<?php

namespace core\actions;

use core\communication\Request;
use core\route\Path;

trait IsLastAction {
    protected function isLastAction(Request $request): bool {
        $isLast = Path::depth($request->getRemainingPath()) === 0;
        return $isLast && !$this->isMiddleware();
    }
}