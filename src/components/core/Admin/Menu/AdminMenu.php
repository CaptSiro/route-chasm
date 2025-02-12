<?php

namespace components\core\Admin\Menu;

use components\core\Admin\SubMenu\SubMenu;
use core\Singleton;
use core\view\Render;
use core\view\Renderer;

class AdminMenu implements Render {
    use Renderer, Singleton;



    public const KEY_RENDER = 0;

    public static function load(string $file): void {
        require_once $file;
    }

    public static function item(string $path, Render $render): void {
        self::getInstance()
            ->addItem($path, $render);
    }



    protected array $map = [];

    public function addItem(string $path, Render $render): static {
        $map = &$this->map;

        foreach ($this->createSteps($path) as $step) {
            if (!isset($map[$step])) {
                $map[$step] = [];
            }

            $map = &$map[$step];
        }

        $map[self::KEY_RENDER] = $render;
        return $this;
    }

    protected function createSteps(string $path): array {
        $steps = [];

        foreach (explode('/', $path) as $step) {
            if ($step !== '') {
                $steps[] = $step;
            }
        }

        return $steps;
    }
}