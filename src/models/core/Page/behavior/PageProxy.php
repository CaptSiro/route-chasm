<?php

namespace models\core\Page\behavior;

use components\core\Admin\Nexus\NexusProxy;
use components\core\Html\Html;
use core\App;
use core\RouteChasmEnvironment;

class PageProxy extends NexusProxy {
    public function getValue(string $name): string {
        $value = parent::getValue($name);

        if ($name === "title") {
            $url = App::getInstance()->getRequest()->getUrl()->copy();
            $url->setQueryArgument(RouteChasmEnvironment::QUERY_PAGE_PARENT, $this->item->getId());
            return Html::createLinkUnsafe($url, $value);
        }

        return $value;
    }
}