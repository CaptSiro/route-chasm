<?php

namespace models\extensions\Priority;

use components\forms\Form;
use components\Icon;
use components\nexus\Nexus;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
use core\view\Html;

trait PriorityProxy {
    protected ?PriorityExtension $priorityExtension = null;



    public function setContext(Nexus $context): static {
        parent::setContext($context);

        $context->on($context::EVENT_EXTENSION_ADDED, function ($extension) {
            if ($extension instanceof PriorityExtension) {
                $this->priorityExtension = $extension;
            }
        });

        return $this;
    }

    public function getValuePriority(): string {
        if (is_null($this->priorityExtension)) {
            return 'PriorityProxy::$priorityExtension is null';
        }

        $item = $this->getItem();

        if (Form::importAssets()) {
            Javascript::import($this->priorityExtension->getResource('priority.js'));
            Css::import($this->priorityExtension->getResource('priority.css'));
        }

        return Html::wrapUnsafe(
            'div',
            Icon::nf('nf-md-drag', '⋮⋮'),
            [
                'x-init' => 'prio_dragHandle',
                'class' => 'prio-drag-handle',
                'data-priority-query' => PriorityExtension::QUERY_PRIORITY,
                'data-url' => $this->priorityExtension->createSetPriorityUrl($item)
                    ->toString()
            ]
        );
    }
}