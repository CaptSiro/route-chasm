<?php

namespace core\module;

use components\core\HttpError\HttpError;
use core\App;
use core\Flags;
use core\http\HttpCode;

trait AvailableAfterLoad {
    use Flags;



    public const FLAG_LOADED = 1;



    public function markLoaded(): void {
        $this->setFlag(self::FLAG_LOADED);
    }

    public function isLoaded(): bool {
        return $this->hasFlag(self::FLAG_LOADED);
    }

    protected function accessibleAfterLoad(): void {
        if ($this->hasFlag(self::FLAG_LOADED)) {
            return;
        }

        App::getInstance()
            ->getResponse()
            ->render(new HttpError(
                "Module SideLoader is not accessible before module is properly loaded",
                HttpCode::SE_INTERNAL_SERVER_ERROR
            ));
    }
}