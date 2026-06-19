<?php

// Location locked file

$_lib = __DIR__ . "/bin/lib.php";
require_once $_lib;



if (!is_null($_frameworkBootstrap = project_mounted("<framework>/bin/bootstrap.php"))) {
    require_once $_frameworkBootstrap;
}

if (!is_null($_projectBootstrap = project_mounted("<project-bootstrap>"))) {
    require_once $_projectBootstrap;
}



routechasm_serve();
