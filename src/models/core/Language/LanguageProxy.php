<?php

namespace models\core\Language;

use components\core\Admin\Nexus\NexusProxy;
use components\core\Html\Html;
use models\extensions\IsDefault\IsDefaultProxy;
use const models\extensions\IsDefault\PROPERTY_IS_DEFAULT;

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

        if (!is_null($this->isDefaultExtension) && $name === PROPERTY_IS_DEFAULT) {
            return $this->getValueIsDefault($name);
        }

        return parent::getValue($name);
    }
}