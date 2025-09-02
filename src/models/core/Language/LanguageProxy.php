<?php

namespace models\core\Language;

use components\core\Admin\Nexus\NexusProxy;
use components\core\Html\Html;
use models\extensions\IsDefault\IsDefaultProxy;

class LanguageProxy extends NexusProxy {
    use IsDefaultProxy;

    public const COLUMN_LANGUAGE = 'language';



    public function getValue(string $name): string {
        if ($name === self::COLUMN_LANGUAGE) {
            /**
             * @var Language $language
             */
            $language = $this->item;
            return Html::wrap('span', $language->getLocale()->getName());
        }

        return parent::getValue($name);
    }
}