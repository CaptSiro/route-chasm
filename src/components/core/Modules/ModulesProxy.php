<?php

namespace components\core\Modules;

use components\core\Html\Html;
use components\layout\Grid\Proxy\Proxy;
use core\module\Module;
use core\module\ModuleInfo;

class ModulesProxy implements Proxy {
    public const COLUMN_NAME = 'name';
    public const COLUMN_IDENTIFIER = 'identifier';
    public const COLUMN_VERSION = 'version';



    protected Module $module;
    protected ModuleInfo $info;

    public function setItem(mixed $item): void {
        $this->module = $item;
        $this->info = $this->module->getInfo();
    }

    public function getValue(string $name): string {
        return match ($name) {
            self::COLUMN_NAME => Html::wrap('span', basename(get_class($this->module))),
            self::COLUMN_IDENTIFIER => Html::wrap('code', $this->info->identifier),
            self::COLUMN_VERSION => Html::wrap('span', $this->info->version),
        };
    }
}