<?php

namespace components\core\Terminal;

use core\App;
use core\Singleton;
use core\view\Renderer;
use core\view\View;

class Terminal implements View {
    use Singleton, Renderer;

    public const DEBUG_DEV = 'DEV';

    public static function dump(mixed ...$var): void {
        $instance = self::getInstance();
        foreach ($var as $item) {
            $instance->varDump($item);
        }
    }



    protected array $dumps;

    public function varDump(mixed $var): void {
        $this->dumps[] = $var;
    }

    public function shouldDisplay(): bool {
        $isDev = boolval(App::getInstance()
            ->getEnv()
            ->get(self::DEBUG_DEV) ?? true);

        return $isDev && !empty($this->dumps);
    }
}