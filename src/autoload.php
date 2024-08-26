<?php

require_once __DIR__ ."/core/Init.php";

spl_autoload_register(function ($class) {
    $file = __DIR__ ."/$class.php";
    if (!file_exists($file)) {
        require __DIR__ ."/../lib/$class.php";
        return;
    }

    require $file;

    if (method_exists($class, "init")) {
        try {
            call_user_func("$class::init");
        } catch (TypeError) {} // abstract class extends init method but does not provide override (\core\Table)
    }
});