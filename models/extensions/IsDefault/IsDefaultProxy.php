<?php

namespace models\extensions\IsDefault;

use components\forms\controls\Checkbox;
use components\forms\Form;
use components\nexus\Nexus;
use core\sideloader\importers\Javascript\Javascript;

trait IsDefaultProxy {
    protected ?IsDefaultExtension $isDefaultExtension = null;



    public function setContext(Nexus $context): static {
        parent::setContext($context);

        $context->on($context::EVENT_EXTENSION_ADDED, function ($extension) {
            if ($extension instanceof IsDefaultExtension) {
                $this->isDefaultExtension = $extension;
            }
        });

        return $this;
    }

    public function getValueIsDefault(string $name): string {
        if (is_null($this->isDefaultExtension)) {
            return 'IsDefaultProxy::$isDefaultExtension is null';
        }

        $item = $this->getItem();
        if (!($item instanceof IsDefault)) {
            return parent::getValue($name);
        }

        if (Form::importAssets()) {
            Javascript::import($this->isDefaultExtension->getResource('is-default.js'));
        }

        if ($item->isDefault()) {
            return (new Checkbox('', '', true))
                ->readonly();
        }

        $url = $this->isDefaultExtension->createSetAsDefaultUrl($item);
        return (new Checkbox('', '', false))
            ->addDataAttribute('url', $url)
            ->addJavascriptInit('isDefault_toggle');
    }
}