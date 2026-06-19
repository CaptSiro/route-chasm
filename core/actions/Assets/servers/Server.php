<?php

namespace core\actions\Assets\servers;

use core\communication\Request;
use core\communication\Response;

interface Server {
    public function serve(string $path, Request $request, Response $response);
}