<?php

use sptf\Sptf;

require_once __DIR__ ."/src/autoload.php";



if (isset($_GET['_test'])) {
    Sptf::testDirectory(__DIR__ . "/src/tests/cases");
    die();
}
