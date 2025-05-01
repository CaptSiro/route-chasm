<?php

namespace models\core\User;

use components\core\Admin\Nexus\NexusProxy;
use components\core\Html\Html;

class UserProxy extends NexusProxy {
    public const COLUMN_USER = 'tag';

    public function getValue(string $name): string {
        if ($name === self::COLUMN_USER) {
            /**
             * @var User $user
             */
            $user = $this->item;
            return Html::wrap('span', '@'.$user->tag);
        }

        return parent::getValue($name);
    }
}