<?php

namespace models\core\Domain;

use components\core\Admin\Nexus\NexusProxy;
use components\core\Html\Html;

class DomainProxy extends NexusProxy {
    public const COLUMN_DOMAIN = 'domain';

    public function getValue(string $name): string {
        if ($name === self::COLUMN_DOMAIN) {
            /**
             * @var Domain $domain
             */
            $domain = $this->item;
            return Html::wrap('span', $domain->getLiteral());
        }

        return parent::getValue($name);
    }
}