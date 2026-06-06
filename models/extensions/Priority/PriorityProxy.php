<?php

namespace models\extensions\Priority;

use components\Admin\Nexus\AdminNexus;
use components\forms\Form;
use components\html\Html;
use components\Icon;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;

trait PriorityProxy {
    protected ?PriorityExtension $priorityExtension = null;



    public function setContext(AdminNexus $context): static {
        parent::setContext($context);

        foreach ($context->getExtensions() as $extension) {
            if ($extension instanceof PriorityExtension) {
                $this->priorityExtension = $extension;
                break;
            }
        }

        return $this;
    }

    public function getValuePriority(): string {
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