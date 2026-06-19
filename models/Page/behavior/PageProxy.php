<?php

namespace models\Page\behavior;

use components\Admin\Nexus\NexusProxy;
use core\App;
use core\RouteChasmEnvironment;
use core\view\Html;
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