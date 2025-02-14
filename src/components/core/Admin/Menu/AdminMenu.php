<?php

namespace components\core\Admin\Menu;

use components\core\Admin\SubMenu\SubMenu;
use core\Singleton;
use core\utils\Strings;
use core\view\StringRenderer;
use core\view\View;
use core\view\Renderer;

class AdminMenu implements View {
    use Renderer, Singleton;



    public const KEY_RENDER = 0;

    public static function load(string $file): void {
        require_once $file;
    }

    public static function item(string $path, View $view): void {
        self::getInstance()
            ->addItem($path, $view);
    }



    protected ?View $homeLabel;
    protected array $map = [];
    protected array $segmentToLabel = [];
    protected array $labelToSegment = [];

    public function __construct() {
        $this->homeLabel = new StringRenderer('Home');
    }



    public function setHomeLabel(?View $view): static {
        $this->homeLabel = $view;
        return $this;
    }

    protected function getTranslation(string $label): string {
        if (isset($this->segmentToLabel[$label])) {
            return $label;
        }

        $segment = Strings::urlPathSegment($label);
        $this->segmentToLabel[$segment] = $label;
        $this->labelToSegment[$label] = $segment;
        return $label;
    }

    public function addItem(string $path, View $view): static {
        $map = &$this->map;

        foreach ($this->createSteps($path) as $step) {
            $segment = $this->getTranslation($step);

            if (!isset($map[$segment])) {
                $map[$segment] = [];
            }

            $map = &$map[$segment];
        }

        $map[self::KEY_RENDER] = $view;
        return $this;
    }

    public function translate(string $path): ?string {
        $translation = [];

        foreach ($this->createSteps($path) as $step) {
            $segment = $this->labelToSegment[$step] ?? null;

            if (is_null($segment)) {
                return null;
            }

            $translation[] = $segment;
        }

        return implode('/', $translation);
    }

    public function getItem(string $path): ?View {
        $map = $this->map;

        foreach ($this->createSteps($path) as $step) {
            $segment = $this->segmentToLabel[$step] ?? null;

            if (!isset($map[$segment])) {
                return null;
            }

            $map = $map[$segment];
        }

        return $map[self::KEY_RENDER] ?? null;
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