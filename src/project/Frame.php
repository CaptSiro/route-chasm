<?php

namespace project;

use components\core\HtmlHead\HtmlHead;
use components\core\WebPage\WebPage;
use components\Lumora\Editor\Editor;
use core\forms\Form;
use core\route\Path;
use core\sideloader\importers\Css\Css;
use core\sideloader\importers\Javascript\Javascript;
use core\view\ContainerContent;

class Frame extends ContainerContent {
    public function __construct() {
        parent::__construct(
            new WebPage(head: new HtmlHead("Frame"))
        );

        Css::import(Editor::getStaticResource("editor.css"));
        Javascript::import(Editor::getStaticResource("inspector.js"));
        Form::importAssets();
    }

    public function loadWidgets(string $widgetsDirectory): void {
        foreach (glob(Path::join($widgetsDirectory, '*.js')) as $widget) {
            Javascript::import($widget);
        }

        foreach (glob(Path::join($widgetsDirectory, '*.css')) as $widget) {
            Css::import($widget);
        }
    }
}