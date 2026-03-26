<?php

namespace models\core\Page\behavior;

use components\core\Admin\Nexus\NexusProxy;
use components\core\Html\Html;
use core\App;
use core\RouteChasmEnvironment;
use models\extensions\Priority\PriorityProxy;
use const models\extensions\Priority\PROPERTY_PRIORITY;

class PageProxy extends NexusProxy {
    use PriorityProxy;



    public function getValue(string $name): string {
        if ($name === PROPERTY_PRIORITY) {
            return $this->getValuePriority();
        }

        $value = parent::getValue($name);

        if ($name === "title") {
            $url = App::getInstance()->getRequest()->getUrl()->copy();
            $url->setQueryArgument(RouteChasmEnvironment::QUERY_PAGE_PARENT, $this->item->getId());
            return Html::createLinkUnsafe($url, $value);
        }

        return $value;
    }
}