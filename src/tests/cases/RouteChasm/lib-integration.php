<?php

use dotenv\Env;
use sptf\Sptf;

Sptf::test("Import dotenv library", function () {
    try {
        $env = new Env([]);
        Sptf::pass();
    } catch (Exception $exception) {
        Sptf::fail($exception->getMessage());
    }
});
