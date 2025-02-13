<?php

namespace modules\jsml;

use core\module\DefaultModule;
use core\module\ModuleInfo;
use core\Singleton;
use core\utils\Arrays;
use core\view\View;
use core\view\Renderer;

class Jsml extends DefaultModule implements View {
    use Renderer, Singleton;



    public const VERSIONS = ['v1'];

    public function getInfo(): ModuleInfo {
        return new ModuleInfo(
            'route-chasm-core:jsml',
            Arrays::last(self::VERSIONS)
        );
    }

    public function migrate(string $fromVersion): void {}



    protected function getSourceFiles(): array {
        $this->accessibleAfterLoad();
        $jsml = $this->getSource();

        return [
            "$jsml/jsml.js",
        ];
    }
}