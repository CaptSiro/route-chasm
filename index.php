<?php

// This file loads different bin/lib.php than the one located in bin/index.php
// The <framework>/bin/lib.php points to the <framework>/project.json while the bin/index.php uses <project>/bin/lib.php
// which points to the <project>/project.json
// All the file references are statically assigned which means that all the files located in bin/ are location dependent
// and moving them to other location may easily introduce bugs

$_lib = __DIR__ . "/bin/lib.php";
require_once $_lib;



if (!is_null($_frameworkBootstrap = project_mounted("<framework>/bin/bootstrap.php"))) {
    require_once $_frameworkBootstrap;
}

if (!is_null($_projectBootstrap = project_mounted("<project-bootstrap>"))) {
    require_once $_projectBootstrap;
}



routechasm_serve();