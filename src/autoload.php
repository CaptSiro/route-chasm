<?php

require_once __DIR__ ."/core/Init.php";
require_once __DIR__ ."/exception-handler.php";

spl_autoload_register(function ($class) {
    $file = __DIR__ ."/$class.php";
    if (!file_exists($file)) {
        $lib = __DIR__ ."/../lib/$class.php";
        if (file_exists($lib)) {
            require __DIR__ ."/../lib/$class.php";
        } else {
            http_send_status(500);
            echo "Class does not exists";
            var_dump($class);
            exit;
        }

        return;
    }

    require $file;

    if (method_exists($class, "init")) {
        try {
            call_user_func("$class::init");
        } catch (TypeError) {
            // abstract class extends init method but does not provide override (\core\Entity)
        }
    }
});