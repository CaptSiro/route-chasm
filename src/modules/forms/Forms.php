<?php

namespace modules\forms;

use core\module\DefaultModule;
use core\module\ModuleInfo;
use core\Singleton;
use core\utils\Arrays;

class Forms extends DefaultModule {
    use Singleton;

    public const IDENTIFIER = 'route-chasm-core:forms';
    public const VERSIONS = ['v1'];

    public function getInfo(): ModuleInfo {
        return new ModuleInfo(
            self::IDENTIFIER,
            Arrays::last(self::VERSIONS)
        );
    }

    public function migrate(string $fromVersion): void {
    }
}