<?php

namespace models\extensions\IsDefault;

use components\core\Admin\Nexus\AdminNexus;
use core\forms\controls\Checkbox\Checkbox;
use core\forms\Form;
use core\sideloader\importers\Javascript\Javascript;

trait IsDefaultProxy {
    protected ?IsDefaultExtension $isDefaultExtension = null;



    public function setContext(AdminNexus $context): static {
        parent::setContext($context);

        foreach ($context->getExtensions() as $extension) {
            if ($extension instanceof IsDefaultExtension) {
                $this->isDefaultExtension = $extension;
                break;
            }
        }

        return $this;
    }

    public function getValueIsDefault(string $name): string {
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