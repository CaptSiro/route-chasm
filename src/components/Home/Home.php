<?php

namespace components\Home;

use components\core\Html\Html;
use components\core\HtmlHead\HtmlHead;
use components\core\Menu\Menu;
use components\core\WebPage\ContextAwareWebPage;
use core\App;
use core\locale\Locale;
use core\view\ContainerContent;

class Home extends ContainerContent {
    protected Menu $menu;

    public function __construct() {
        parent::__construct(
            new ContextAwareWebPage(
                head: new HtmlHead("Home")
            )
        );
    }

    public function createLanguageLink(Locale $locale): string {
        $url = App::getInstance()->getRequest()->getUrl()->copy();
        $url->getQuery()->clear();

        return Html::createLinkUnsafe(
            $url->setQueryArgument('language', $locale->getIdentifier()),
            $locale->getName()
        );
    }
}